<?php

namespace App\Service\Security;

class UserPasswordChecker
{
    /**
     * @param string $password
     * @param string $passwordConfirm
     *
     * @return array|true
     */
    public function isIdentical(string $password, string $passwordConfirm)
    {
        if ($password === $passwordConfirm) {
            return true;
        };

        return ['type' => 'danger', 'message' => 'Password confirm is different from password. Please try again'];
    }

    /**
     * @param string $password
     *
     * @return array|true
     */
    public function checkPassword(string $password)
    {
        $message = [];

        $checks = ['lowerCase', 'upperCase', 'number', 'specialChar'];

        foreach ($checks as $check) {
            $checkerName = 'check' . ucfirst($check);
            $result = $this->$checkerName($password);

            if ($result !== true) {
                $message[$check] = $result;
            }
        }

        if (!empty($message)) {
            return $message;
        }

        return true;
    }

    /**
     * @param string $password
     *
     * @return string|true
     */
    public function checkLowerCase(string $password)
    {
        if (preg_match('#[a-z]+#', $password) !== 0) {
            return true;
        }

        return 'lower case is missing';
    }

    /**
     * @param string $password
     *
     * @return string|true
     */
    public function checkUpperCase(string $password)
    {
        if (preg_match('#[A-Z]+#', $password) !== 0) {
            return true;
        }

        return 'upper case is missing';
    }

    /**
     * @param string $password
     *
     * @return string|true
     */
    public function checkNumber(string $password)
    {
        if (preg_match('#[1-9]+#', $password) !== 0) {
            return true;
        }

        return 'number is missing';
    }

    /**
     * @param string $password
     *
     * @return string|true
     */
    public function checkSpecialChar(string $password)
    {
        if (preg_match('#[.!$%*`{|}~\[\]?@&_\'\-€/\\\+=\#\^]+#', $password) !== 0) {
            return true;
        }

        return 'Special character is missing';
    }
}