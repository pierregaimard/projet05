<?php

namespace App\Controller\Admin;

use App\Model\Entity\User;
use App\Model\Entity\UserRole;
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
        $manager         = $this->getOrm()->getManager('App');
        $roleRepository  = $manager->getRepository(UserRole::class);
        $role            = $roleRepository->findOneBy(['role' => User::ROLE_MEMBER]);
        $users           = $role->getUsers();
        $userRepository  = $manager->getRepository(User::class);
        $validationUsers = $userRepository->findBy(['id_status' => 1]);

        $response = new Response();
        $response->setContent($this->render(
            'admin/home.html.twig',
            [
                'users' => $users,
                'validationUsers' => $validationUsers,
            ]
        ));

        return $response;
    }
}
