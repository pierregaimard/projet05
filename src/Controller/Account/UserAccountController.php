<?php

namespace App\Controller\Account;

use App\Model\Entity\User;
use Climb\Controller\AbstractController;
use Climb\Exception\AppException;
use Climb\Http\Response;
use Climb\Routing\Annotation\Route;
use Climb\Security\Annotation\Security;

class UserAccountController extends AbstractController
{
    /**
     * @Route(path="/account", name="account_home")
     * @Security(roles={"ADMIN", "MEMBER"})
     *
     * @return Response
     */
    public function home()
    {
        if ($this->getUser() === null) {
            return $this->redirectToRoute('home');
        }

        $response = new Response();
        $response->setContent($this->render('account/account.html.twig'));

        return $response;
    }
}
