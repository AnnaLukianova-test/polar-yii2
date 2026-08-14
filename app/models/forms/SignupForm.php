<?php

namespace app\models\forms;

use app\models\User;
use yii\base\Model;

class SignupForm extends Model
{
    public string $email = '';
    public string $first_name = '';
    public string $last_name = '';
    public string $password = '';
    public string $password_repeat = '';

    public function rules(): array
    {
        return [
            [['email', 'first_name', 'last_name', 'password', 'password_repeat'], 'required'],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 255],
            [['first_name', 'last_name'], 'string', 'max' => 100],
            [['password'], 'string', 'min' => 6],
            [['password_repeat'], 'compare', 'compareAttribute' => 'password', 'message' => 'Passwords do not match.'],
            [['email'], 'unique', 'targetClass' => User::class, 'message' => 'This email is already registered.'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'email' => 'Email',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'password' => 'Password',
            'password_repeat' => 'Repeat Password',
        ];
    }
}
