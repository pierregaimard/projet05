<?php

namespace App\Controller\Security;

use App\Service\Form\EntityFormDataManager;
use Climb\Controller\AbstractController;
use Climb\Http\Response;
use Climb\Security\TokenManager;

class AuthenticationController extends AbstractController
{
    /**
     * @var TokenManager
     */
    private TokenManager $tokenManager;

    /**
     * @var EntityFormDataManager
     */
    private EntityFormDataManager $formManager;

    public function __construct(TokenManager $tokenManager, EntityFormDataManager $formManager)
    {
        $this->tokenManager = $tokenManager;
        $this->formManager  = $formManager;
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
