<?php

namespace App\Service\Form\DataChecker;

interface DataCheckerInterface
{
    /**
     * @param string $data
     *
     * @return bool
     */
    public function check(string $data): bool;

    /**
     * @return string
     */
    public function getErrorMessage(): string;
}
