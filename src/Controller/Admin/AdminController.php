<?php

namespace App\Controller\Admin;

use App\Model\Entity\BlogPostComment;
use App\Model\Entity\User;
use Climb\Controller\AbstractController;
use Climb\Exception\AppException;
use Climb\Http\Response;
use Climb\Routing\Annotation\Route;
use Climb\Security\Annotation\Security;

class AdminController extends AbstractController
{
    /**
     * @Route(path="/admin/home", name="admin_home")
     * @Security(roles={"ADMIN"})
     *
     * @throws AppException
     */
    public function home()
    {
        $manager            = $this->getOrm()->getManager('App');
        $userRepository     = $manager->getRepository(User::class);
        $users              = $userRepository->findValidatedMembers();
        $validationUsers    = $userRepository->findBy(['id_status' => 1]);
        $validationComments = $manager->getRepository(BlogPostComment::class)->findByStatus(1);

        $response = new Response();
        $response->setContent($this->render(
            'admin/home.html.twig',
            [
                'users' => $users,
                'validationUsers' => $validationUsers,
                'validationComments' => $validationComments
            ]
        ));

        return $response;
    }
}
