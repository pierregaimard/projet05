<?php

namespace App\Service\Form\DataChecker;

class EmailDataChecker implements DataCheckerInterface
{
    /**
     * @param string $data
     *
     * @return bool
     */
    public function check(string $data): bool
    {
        return filter_var($data, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return 'Invalid e-mail address';
    }
}
