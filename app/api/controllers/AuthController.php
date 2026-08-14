<?php

namespace app\api\controllers;

use app\filters\RateLimitFilter;
use app\api\forms\LoginForm;
use app\api\forms\SignupForm;
use app\services\reg\AuthService;
use app\services\reg\SignupService;
use app\services\security\RateLimiterService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

class AuthController extends Controller
{
    public function __construct(
        $id,
        $module,
        private AuthService $authService,
        private SignupService $signupService,
        private RateLimiterService $rateLimiter,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['login', 'signup', 'logout'],
                'rules' => [
                    [
                        'actions' => ['login', 'signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'rateLimitLogin' => [
                'class' => RateLimitFilter::class,
                'only' => ['login'],
                'scope' => 'auth',
            ],
            'rateLimitSignup' => [
                'class' => RateLimitFilter::class,
                'only' => ['signup'],
                'scope' => 'auth',
            ],
        ];
    }

    public function actionLogin(): string|Response
    {
        $form = new LoginForm();
        $rateLimitKey = $this->rateLimitKey('login');

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            if ($this->authService->login($form->email, $form->password)) {
                $this->rateLimiter->reset($rateLimitKey);

                return $this->redirect(['profile/index']);
            }

            $this->rateLimiter->hit($rateLimitKey);
            $form->addError('password', 'Incorrect email or password.');
        }

        return $this->render('login', [
            'model' => $form,
        ]);
    }

    public function actionSignup(): string|Response
    {
        $form = new SignupForm();
        $rateLimitKey = $this->rateLimitKey('signup');

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            if ($this->signupService->register($form) !== null) {
                $this->rateLimiter->reset($rateLimitKey);

                return $this->redirect(['profile/index']);
            }

            $this->rateLimiter->hit($rateLimitKey);
            $form->addError('email', 'Unable to create account. Please try again.');
        }

        return $this->render('signup', [
            'model' => $form,
        ]);
    }

    public function actionLogout(): Response
    {
        $this->authService->logout();

        return $this->redirect(['auth/login']);
    }

    private function rateLimitKey(string $action): string
    {
        return 'auth:' . $action . ':' . (Yii::$app->request->userIP ?? 'unknown');
    }
}
