<?php

namespace app\commands;

use app\models\User;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class UserController extends Controller
{
    public function actionCreate($username, $email, $password)
    {
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->setPassword($password);
        $user->generateAuthKey();
        $user->created_at = date('Y-m-d H:i:s');
        $user->updated_at = date('Y-m-d H:i:s');

        if ($user->save()) {
            $this->stdout("User $username created successfully.\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        $this->stderr("Failed to create user!\n", Console::FG_RED);
        foreach ($user->errors as $errors) {
            foreach ($errors as $error) {
                $this->stderr("- $error\n", Console::FG_RED);
            }
        }

        return ExitCode::UNSPECIFIED_ERROR;
    }
}
