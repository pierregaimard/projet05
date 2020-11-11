<?php

namespace App\Controller\Admin;

use App\Model\Entity\User;
use Climb\Controller\AbstractController;
use Climb\Exception\AppException;
use Climb\Http\Response;
use Climb\Orm\EntityRepository;
use Climb\Routing\Annotation\Route;
use Climb\Security\Annotation\Security;

class AdminUserController extends AbstractController
{
    /**
     * @Route(path="/admin/users/list", name="admin_user_list")
     * @Security(roles={"ADMIN"})
     *
     * @throws AppException
     */
    public function list()
    {
        $manager        = $this->getOrm()->getManager('App');
        $userRepository = $manager->getRepository(User::class);
        $users          = $userRepository->findAll(
            [EntityRepository::OPT_ORDER_BY =>
                [
                    'id_status' => 'ASC',
                    'first_name' => 'ASC',
                    'last_name' => 'ASC'
                ]
            ]
        );

        $response = new Response();
        $response->setContent($this->render(
            'admin/users_list.html.twig',
            [
                'users' => $users
            ]
        ));

        return $response;
    }

    /**
     * @param int $key
     *
     * @Route(path="/admin/users/view/{key}", name="admin_user_view", regex={"key"="[1-9]([0-9]*)"})
     *
     * @return Response
     *
     * @throws AppException
     */
    public function view(int $key)
    {
        $manager        = $this->getOrm()->getManager('App');
        $userRepository = $manager->getRepository(User::class);
        $user           = $userRepository->findOne($key);

        $response = new Response();
        $response->setContent($this->render(
            'admin/user_view.html.twig',
            [
                'user' => $user
            ]
        ));

        return $response;
    }
}