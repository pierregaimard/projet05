<?php

namespace App\Model\Entity;

use App\Service\Form\Annotation\Field;

class PasswordChange
{
    /**
     * @var string
     *
     * @Field(type="password", minLength=8)
     */
    public string $currentPassword;

    /**
     * @var string
     *
     * @Field(type="password", minLength=8)
     */
    public string $newPassword;

    /**
     * @var string
     *
     * @Field(type="password", minLength=8)
     */
    public string $passwordConfirm;

    /**
     * @return string
     */
    public function getCurrentPassword(): string
    {
        return $this->currentPassword;
    }

    /**
     * @param string $currentPassword
     */
    public function setCurrentPassword(string $currentPassword): void
    {
        $this->currentPassword = $currentPassword;
    }

    /**
     * @return string
     */
    public function getNewPassword(): string
    {
        return $this->newPassword;
    }

    /**
     * @param string $newPassword
     */
    public function setNewPassword(string $newPassword): void
    {
        $this->newPassword = $newPassword;
    }

    /**
     * @return string
     */
    public function getPasswordConfirm(): string
    {
        return $this->passwordConfirm;
    }

    /**
     * @param string $passwordConfirm
     */
    public function setPasswordConfirm(string $passwordConfirm): void
    {
        $this->passwordConfirm = $passwordConfirm;
    }
}
