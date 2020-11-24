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

class AdminMemberValidationController extends AbstractController
{
    /**
     * @var EmailManager
     */
    private EmailManager $emailManager;

    /**
     * @param EmailManager $emailManager
     */
    public function __construct(EmailManager $emailManager)
    {
        $this->emailManager = $emailManager;
    }

    /**
     * @Route(path="/admin/members/validationList", name="admin_user_validation_list")
     * @Security(roles={"ADMIN"})
     *
     * @throws AppException
     */
    public function list()
    {
        $manager        = $this->getOrm()->getManager('App');
        $userRepository = $manager->getRepository(User::class);
        $users          = $userRepository->findBy(['id_status' => 1]);

        $response = new Response();
        $response->setContent($this->render(
            'admin/member_validation/members_validation_list.html.twig',
            [
                'users' => $users
            ]
        ));

        return $response;
    }

    /**
     * @Route(
     *     path="/admin/members/validation/{key}/{action}",
     *     name="admin_user_validation_action",
     *     regex={"key"="[1-9]([0-9]*)", "action"="accept|reject"}
     * )
     *
     * @param int    $key
     * @param string $action
     *
     * @return Response
     *
     * @throws AppException
     */
    public function validate(int $key, string $action)
    {
        $manager        = $this->getOrm()->getManager('App');
        $userRepository = $manager->getRepository(User::class);
        $user           = $userRepository->findOne($key);

        switch ($action) {
            case 'accept':
                $statusRepository = $manager->getRepository(UserStatus::class);
                $status           = $statusRepository->findOneBy(['status' => User::STATUS_ACTIVE]);
                $user->setStatus($status);
                $manager->updateOne($user);

                $template     = 'admin/member_validation/_email_member_validation.html.twig';
                $notification = $user->getFormattedName('firstName') . ' account have been validated';
                break;
            case 'reject':
                $manager->deleteOne($user);

                $template     = 'admin/member_validation/_email_member_rejection.html.twig';
                $notification = $user->getFormattedName('firstName') . ' account have been rejected';
                break;
            default:
                $notification = 'Oups, something went wrong, please try again';
        }

        if (isset($template)) {
            $this->emailManager->send($user->getEmail(), 'Account validation', $template, ['user' => $user]);
        }

        $response = new RedirectResponse($this->getRoutePath('admin_user_validation_list'));
        $response->getFlashes()->add('message', [
            'status' => 'success',
            'message' => '<span uk-icon="check"></span> ' . $notification
        ]);

        return $response;
    }
}
