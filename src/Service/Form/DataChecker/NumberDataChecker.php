<?php

namespace App\Service\Form\DataChecker;

class NumberDataChecker implements DataCheckerInterface
{
    /**
     * @param string $data
     *
     * @return bool
     */
    public function check(string $data): bool
    {
        return preg_match('#^[0-9]+$#', $data);
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return 'Invalid number';
    }
}
