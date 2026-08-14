<?php

namespace app\api\controllers;

use app\api\forms\CallbackForm;
use app\models\User;
use app\exceptions\polar\PolarApiException;
use app\services\polar\PolarOAuthService;
use app\services\polar\PolarSyncService;
use app\services\profile\ProfileService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PolarController extends Controller
{
    public function __construct(
        $id,
        $module,
        private PolarOAuthService $oauthService,
        private PolarSyncService $syncService,
        private ProfileService $profileService,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'connect' => ['GET'],
                    'callback' => ['GET'],
                    'sync' => ['GET', 'POST'],
                ],
            ],
        ];
    }

    /**
     * Redirects to Polar OAuth authorization page.
     */
    public function actionConnect(): Response
    {
        $user = $this->requireUser();

        return $this->redirect($this->oauthService->getAuthorizationUrl($user->id));
    }

    /**
     * Handles Polar OAuth authorization callback.
     */
    public function actionCallback(): Response
    {
        $user = $this->requireUser();
        $form = new CallbackForm($user->id);

        if (!$form->load(Yii::$app->request->get(), '') || !$form->validate()) {
            $errors = $form->getFirstErrors();
            Yii::$app->session->setFlash(                                                                               
                'error',
                $errors !== [] ? reset($errors) : 'Invalid Polar authorization response. Please try again.',
            );

            return $this->redirect(['profile/index']);
        }

        try {
            $token = $this->oauthService->exchangeAuthorizationCode($form->code);
            $this->syncService->connect($user, $token);
            Yii::$app->session->setFlash('success', 'Polar account connected.');
        } catch (PolarApiException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', $exception->getMessage());
        }

        return $this->redirect(['profile/index']);
    }

    /**
     * Syncs Polar exercises.
     */
    public function actionSync(): Response
    {
        $user = $this->requireUser();

        try {
            $result = $this->syncService->syncExercises($user);
        } catch (PolarApiException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', $exception->getMessage());

            return $this->redirect(['profile/index']);
        }

        if ($result->errors !== []) {
            Yii::$app->session->setFlash(
                'error',
                implode(' ', $result->errors) . ($result->syncedCount > 0 ? ' Synced ' . $result->syncedCount . ' exercise(s).' : ''),
            );
        } elseif ($result->noNewData) {
            Yii::$app->session->setFlash('info', 'No new Polar exercises to sync.');
        } else {
            Yii::$app->session->setFlash('success', 'Synced ' . $result->syncedCount . ' Polar exercise(s).');
        }

        return $this->redirect(['profile/index']);
    }

    private function requireUser(): User
    {
        $user = $this->profileService->getCurrentUser();
        if ($user === null) {
            throw new NotFoundHttpException('User not found.');
        }

        return $user;
    }
}
