<?php

namespace App\Controller;

use Climb\Controller\AbstractController;
use Climb\Http\Response;

class HomeController extends AbstractController
{
    /**
     * @return Response
     *
     * @Route(path="/", name="home")
     */
    public function home()
    {
        $response = new Response();
        $response->setContent($this->render('home/home.html.twig'));

        return $response;
    }
}
