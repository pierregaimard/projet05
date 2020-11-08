<?php

namespace App\Service\Form\DataChecker;

class NameDataChecker implements DataCheckerInterface
{
    /**
     * @param string $data
     *
     * @return bool
     */
    public function check(string $data): bool
    {
        return preg_match('#^[a-zA-Zâäçéèêëîïôùûüÿ]+([ -]?[a-zA-Zâäçéèêëîïôùûüÿ]*)$#', $data) !== 0;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return 'Invalid name declaration';
    }
}
