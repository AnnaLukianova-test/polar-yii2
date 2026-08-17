<?php

namespace app\api\forms;

use app\api\services\SessionStateValidator;
use yii\base\Model;

class CallbackForm extends Model
{
    public string $error = '';
    public string $state = '';
    public string $code = '';

    public function __construct(
        private int $userId,
        $config = [],
    ) {
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['error', 'state', 'code'], 'string'],
            [['error', 'state', 'code'], 'default', 'value' => ''],
            ['error', 'validatePolarError'],
            [['state', 'code'], 'required', 'when' => fn (self $model) => $model->error === ''],
            ['state', 'validateOauthState'],
        ];
    }

    public function validatePolarError(string $attribute): void
    {
        if ($this->error !== '') {
            $this->addError($attribute, "Polar authorization was cancelled or failed:{$this->error}");
        }
    }

    public function validateOauthState(string $attribute): void
    {
        if ($this->hasErrors('error') || $this->state === '') {
            return;
        }

        if (!SessionStateValidator::validate($this->state, $this->userId)) {
            $this->addError($attribute, 'Invalid Polar authorization response. Please try again.');
        }
    }
}
