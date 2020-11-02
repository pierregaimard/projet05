<?php

namespace App\Controller\Security;

use Climb\Controller\AbstractController;
use Climb\Http\Response;

class AuthenticationController extends AbstractController
{
    /**
     * @Route(path="/login", name="login")
     */
    public function login()
    {
        $response = new Response();
        $response->setContent($this->render('security/authentication/login.html.twig'));

        return $response;
    }
}
