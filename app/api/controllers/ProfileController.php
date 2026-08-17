<?php

namespace app\api\controllers;

use app\services\polar\PolarConnectionService;
use app\services\profile\ProfileService;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class ProfileController extends Controller
{
    public function __construct(
        $id,
        $module,
        private ProfileService $profileService,
        private PolarConnectionService $polarConnectionService,
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
        ];
    }

    public function actionIndex(): string
    {
        $user = $this->profileService->getCurrentUser();
        if ($user === null) {
            throw new NotFoundHttpException('User not found.');
        }

        return $this->render('index', [
            'user' => $user,
            'polarConnection' => $this->polarConnectionService->getActualPolarConnection($user),
        ]);
    }
}
