<?php

namespace App\Service\Security;

use Exception;

class SecurityCodeManager
{
    /**
     * @return int
     */
    public function generateCode(): int
    {
        try {
            return random_int(100000, 999999);
        } catch (Exception $exception) {
            return (int)$exception->getCode();
        }
    }
}
