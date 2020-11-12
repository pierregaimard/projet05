<?php

namespace App\Controller\Admin;

use App\Model\Entity\User;
use App\Model\Entity\UserStatus;
use App\Service\Email\EmailManager;
use Climb\Controller\AbstractController;
use Climb\Exception\AppException;
use Climb\Http\RedirectResponse;
use Climb\Http\Response;
use Climb\Routing\Annotation\Route;
use Climb\Security\Annotation\Security;

class AdminUserController extends AbstractController
{
    /**
     * @var EmailManager
     */
    private EmailManager $emailManager;

    public function __construct(EmailManager $emailManager)
    {
        $this->emailManager = $emailManager;
    }

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
        $users          = $userRepository->findValidatedMembers();

        $response = new Response();
        $response->setContent($this->render(
            'admin/user/users_list.html.twig',
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
     * @Security(roles={"ADMIN"})
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
            'admin/user/user_view.html.twig',
            [
                'user' => $user
            ]
        ));

        return $response;
    }

    /**
     * @param int $key
     *
     * @Route(path="/admin/users/lock/{key}", name="admin_user_lock", regex={"key"="[1-9]([0-9]*)"})
     * @Security(roles={"ADMIN"})
     *
     * @return Response
     *
     * @throws AppException
     */
    public function lock(int $key)
    {
        $manager          = $this->getOrm()->getManager('App');
        $userRepository   = $manager->getRepository(User::class);
        $user             = $userRepository->findOne($key);
        $statusRepository = $manager->getRepository(UserStatus::class);
        $response         = new RedirectResponse($this->getRoutePath('admin_user_view', ['key' => $key]));

        switch ($user->getStatus()->getStatus()) {
            case User::STATUS_LOCKED:
                $status  = $statusRepository->findOneBy(['status' => User::STATUS_ACTIVE]);
                $message = ' have been unlocked successfully!';
                $this->emailManager->send(
                    $user->getEmail(),
                    'Account unlocked',
                    'admin/user/_email_account_unlock.html.twig',
                    ['user' => $user]
                );
                break;
            case User::STATUS_ACTIVE:
            default:
                $status  = $statusRepository->findOneBy(['status' => User::STATUS_LOCKED]);
                $message = ' have been locked successfully!';
        }

        $response->getFlashes()->add(
            'message',
            [
                'status' => 'success',
                'message' => '<span uk-icon="check"></span> ' . $user->getFormattedName('firstName') . $message
            ]
        );

        $user->setStatus($status);
        $manager->updateOne($user);

        return $response;
    }

    /**
     * @param int $key
     *
     * @Route(path="/admin/users/delete/{key}", name="admin_user_delete", regex={"key"="[1-9]([0-9]*)"})
     * @Security(roles={"ADMIN"})
     *
     * @return Response
     *
     * @throws AppException
     */
    public function remove(int $key)
    {
        $manager        = $this->getOrm()->getManager('App');
        $userRepository = $manager->getRepository(User::class);
        $user           = $userRepository->findOne($key);

        $manager->deleteOne($user);

        $this->emailManager->send(
            $user->getEmail(),
            'Account removal',
            'admin/user/_email_account_removal.html.twig',
            ['user' => $user]
        );

        $response = new RedirectResponse($this->getRoutePath('admin_user_list'));
        $response->getFlashes()->add(
            'message',
            [
                'status' => 'success',
                'message' =>
                    '<span uk-icon="check"></span> ' .
                    $user->getFormattedName('firstName') .
                    ' have been removed successfully'
            ]
        );

        return $response;
    }
}
