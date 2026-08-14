<?php

namespace app\api\forms;

use yii\base\Model;

class LoginForm extends Model
{
    public string $email = '';
    public string $password = '';

    public function rules(): array
    {
        return [
            [['email', 'password'], 'required'],
            [['email'], 'email'],
            [['password'], 'string'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'email' => 'Email',
            'password' => 'Password',
        ];
    }
}
