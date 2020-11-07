<?php

namespace App\Controller\Security;

use App\Service\Security\FormTokenManager;
use Climb\Controller\AbstractController;
use Climb\Http\Response;

class SignUpController extends AbstractController
{
    /**
     * @var FormTokenManager
     */
    private FormTokenManager $tokenManager;

    /**
     * @param FormTokenManager $tokenManager
     */
    public function __construct(FormTokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * @Route(path="/signUp", name="sign_up")
     */
    public function signUp()
    {
        $token = $this->tokenManager->getToken('SignUpForm');

        $response = new Response();
        $response->setContent($this->render(
            'security/signup/sign_up.html.twig',
            [
                'token' => $token,
            ]
        ));

        return $response;
    }
}
