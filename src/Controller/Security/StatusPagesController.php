<?php

namespace App\Controller\Security;

use Climb\Controller\AbstractController;
use Climb\Http\Response;

class StatusPagesController extends AbstractController
{
    public function notFound()
    {
        $response = new Response();
        $response->setContent($this->render('status/404_not_found.html.twig'));

        return $response;
    }

    public function unauthorized()
    {
        $response = new Response();
        $response->setContent($this->render('status/401_unauthorized.html.twig'));

        return $response;
    }

    public function forbidden()
    {
        $response = new Response();
        $response->setContent($this->render('status/403_forbidden.html.twig'));

        return $response;
    }
}
