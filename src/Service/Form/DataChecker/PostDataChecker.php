<?php

namespace App\Service\Form\DataChecker;

class PostDataChecker implements DataCheckerInterface
{
    /**
     * @param string $data
     *
     * @return bool
     */
    public function check(string $data): bool
    {
        return $data !== '';
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return 'Invalid character is used';
    }
}
