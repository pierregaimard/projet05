<?php

namespace App\Service\Security;

use Climb\Security\TokenManager;

class FormTokenManager
{
    /**
     * @var TokenManager
     */
    private TokenManager $tokenManager;

    /**
     * FormTokenManager constructor.
     *
     * @param TokenManager $tokenManager
     */
    public function __construct(TokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * @param string $keyword
     *
     * @return string|null
     */
    public function getToken(string $keyword)
    {
        return $this->tokenManager->getToken($keyword);
    }

    /**
     * @param string $keyword
     * @param string $hash
     *
     * @return array|true
     */
    public function isValid(string $keyword, string $hash)
    {
        if ($this->tokenManager->isTokenValid($keyword, $hash)) {
            return true;
        }

        return ['type' => 'danger', 'message' => "Invalid security token, please try again"];
    }
}
