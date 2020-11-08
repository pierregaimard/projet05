<?php

namespace App\Service\Security;

use App\Service\Form\Annotation\Field;
use App\Service\Form\EntityFormDataManager;

class SecurityFormDataManager
{
    /**
     * @var EntityFormDataManager
     */
    private EntityFormDataManager $formDataManager;

    public function __construct(EntityFormDataManager $formDataManager)
    {
        $this->formDataManager = $formDataManager;
    }

    /**
     * @param string $code
     *
     * @return array|true
     */
    public function checkSecurityCode(string $code)
    {
        $field = new Field();
        $field->setType(Field::TYPE_NUMBER);
        $field->setNullable(false);
        $checkCode = $this->formDataManager->checkFormField($code, $field);

        if ($checkCode === true) {
            return true;
        }

        return $checkCode;
    }

    /**
     * @param string $code
     *
     * @return int
     */
    public function filterSecurityCode(string $code)
    {
        return $this->formDataManager->filterField(Field::TYPE_NUMBER, $code);
    }

    /**
     * @param string $password
     * @param string $passwordConfirm
     *
     * @return array|bool
     */
    public function checkPasswordsData(string $password, string $passwordConfirm)
    {
        $field = new Field();
        $field->setType(Field::TYPE_PASSWORD);
        $field->setNullable(false);
        $field->setMinLength(8);
        $checkPw        = $this->formDataManager->checkFormField($password, $field);
        $checkPwConfirm = $this->formDataManager->checkFormField($passwordConfirm, $field);
        $message = [];

        if ($checkPw !== true) {
            $message['password'] = $checkPw;
        }

        if ($checkPwConfirm !== true) {
            $message['passwordConfirm'] = $checkPwConfirm;
        }

        if (!empty($message)) {
            return $message;
        }

        return true;
    }
}
