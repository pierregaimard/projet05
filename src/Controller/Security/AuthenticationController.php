<?php

namespace App\Controller\Security;

use Climb\Controller\AbstractController;
use Climb\Http\Response;
use Climb\Security\TokenManager;

class AuthenticationController extends AbstractController
{
    /**
     * @var TokenManager
     */
    private TokenManager $tokenManager;

    public function __construct(TokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * @Route(path="/login", name="login")
     */
    public function login()
    {
        $token = $this->tokenManager->getToken('authentication');

        $response = new Response();
        $response->setContent($this->render('security/authentication/login.html.twig', ['token' => $token]));

        return $response;
    }

    /**
     * @Route(path="/loginCheck", name="login_check")
     */
    public function loginCheck()
    {
        
    }
}
