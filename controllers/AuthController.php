<?php

namespace app\controllers;

class AuthController extends \yii\web\Controller
{
    public function actionForgot_password()
    {
        $this->layout = 'auth';
        return $this->render('forgot_password');
    }

    public function actionReset()
    {
        $this->layout = 'auth';
        return $this->render('reset_password');
    }

    public function actionSignup()
    {
        $this->layout = 'auth';
        return $this->render('signup');
    }

    public function actionLogin()
    {
        $this->layout = 'auth';
        return $this->render('login');
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

}
