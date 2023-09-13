<?php

namespace app\controllers;

use app\models\LoginForm;
use yii\filters\AccessControl;

class AuthController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'reset-password'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'profile'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }



    public function actionPasswordDimenticata()
    {
        $this->layout = 'auth';
        return $this->render('password_dimenticata');
    }

    public function actionResetPassword()
    {
        $this->layout = 'auth';
        return $this->render('reset_password');
    }

/*    public function actionSignup()
    {
        $this->layout = 'auth';
        return $this->render('signup');
    }*/

    public function actionLogin()
    {
        $model = new LoginForm();
        $this->layout = 'auth';
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        else if ($this->request->isPost) {
            $model->load($this->request->post());
            if ($model->login()) {
                // go home
                return $this->goHome();
            }
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionForgot()
    {
        $this->layout = 'auth';
        return $this->render('forgot_password');
    }

    public function actionIndex()
    {
        $this->layout = 'auth';
        return $this->render('login');
    }

    public function actionLogout()
    {
        \Yii::$app->user->logout();
        return $this->goHome();
    }

}
