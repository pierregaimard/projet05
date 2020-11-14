<?php

namespace App\Controller\Account;

use App\Model\Entity\PasswordChange;
use App\Model\Entity\User;
use App\Service\Form\EntityFormDataManager;
use App\Service\Security\FormTokenManager;
use App\Service\Security\UserPasswordChecker;
use App\Service\Security\UserSecurityCodeManager;
use App\Service\Security\UserSecurityManager;
use Climb\Controller\AbstractController;
use Climb\Exception\AppException;
use Climb\Http\RedirectResponse;
use Climb\Security\UserManager;
use Climb\Security\UserPasswordManager;

class UserAccountPasswordController extends AbstractController
{
    /**
     * @var FormTokenManager
     */
    private FormTokenManager $tokenManager;

    /**
     * @var EntityFormDataManager
     */
    private EntityFormDataManager $formManager;

    /**
     * @var UserManager
     */
    private UserManager $userManager;

    /**
     * @var UserSecurityManager
     */
    private UserSecurityManager $userSecurity;

    /**
     * @var UserSecurityCodeManager
     */
    private UserSecurityCodeManager $codeManager;

    /**
     * @var UserPasswordChecker
     */
    private UserPasswordChecker $passwordChecker;

    /**
     * @var UserPasswordManager
     */
    private UserPasswordManager $passwordManager;

    /**
     * @param FormTokenManager        $tokenManager
     * @param EntityFormDataManager   $formManager
     * @param UserManager             $userManager
     * @param UserSecurityManager     $userSecurity
     * @param UserSecurityCodeManager $codeManager
     * @param UserPasswordChecker     $passwordChecker
     * @param UserPasswordManager     $passwordManager
     */
    public function __construct(
        FormTokenManager $tokenManager,
        EntityFormDataManager $formManager,
        UserManager $userManager,
        UserSecurityManager $userSecurity,
        UserSecurityCodeManager $codeManager,
        UserPasswordChecker $passwordChecker,
        UserPasswordManager $passwordManager
    ) {
        $this->tokenManager    = $tokenManager;
        $this->formManager     = $formManager;
        $this->userManager     = $userManager;
        $this->userSecurity    = $userSecurity;
        $this->codeManager     = $codeManager;
        $this->passwordChecker = $passwordChecker;
        $this->passwordManager = $passwordManager;
    }

    /**
     * @Route(path="/account/checkPassword", name="account_password_check")
     *
     * @throws AppException
     */
    public function checkPassword()
    {
        $data = $this->getRequest()->getPost();

        // Checks security token
        $tokenCheck = $this->tokenManager->isValid('accountCheck', $data->get('token'));
        if ($tokenCheck !== true) {
            return $this->redirectToRoute(
                'account_home',
                null,
                ['messagePassword' => $tokenCheck, 'formData' => $data->getAll()]
            );
        }

        // Check form data
        $formCheck = $this->formManager->checkFormData(PasswordChange::class, $data->getAll());
        if (is_array($formCheck)) {
            return $this->redirectToRoute(
                'account_home',
                null,
                ['formCheck' => $formCheck, 'formData' => $data->getAll()]
            );
        }

        $pass = new PasswordChange();
        $data->remove('token');
        $this->formManager->setEntityFormData($pass, $data->getAll());

        // Check current password
        if (!$this->passwordManager->isPasswordValid($pass->getCurrentPassword(), $this->getUser()->getPassword())) {
            return $this->redirectToRoute(
                'account_home',
                null,
                [
                    'formData' => $data->getAll(),
                    'messagePassword' => [
                        'type' => 'danger',
                        'message' => 'Invalid current password, please try again'
                    ]
                ]
            );
        }

        // Check identical
        $identicalCheck = $this->passwordChecker->isIdentical($pass->getNewPassword(), $data->get('passwordConfirm'));
        if ($identicalCheck !== true) {
            return $this->redirectToRoute(
                'account_home',
                null,
                ['messagePassword' => $identicalCheck, 'formData' => $data->getAll()]
            );
        }

        // Check password requirements
        $requirementsCheck = $this->passwordChecker->checkPassword($pass->getNewPassword());
        if ($requirementsCheck !== true) {
            return $this->redirectToRoute(
                'account_home',
                null,
                [
                    'messagePassword' => [
                        'type' => 'danger',
                        'message' => 'Invalid password, please check requirements'
                    ],
                    'formData' => $data->getAll(),
                    'requires' => $requirementsCheck
                ]
            );
        }

        $manager        = $this->getOrm()->getManager('App');
        $userRepository = $manager->getRepository(User::class);
        $user           = $userRepository->findOne($this->getUser()->getKey());
        $user->setPassword($this->passwordManager->getPasswordHash($pass->getNewPassword()));
        $manager->updateOne($user);
        $this->userSecurity->setUser($user);

        $response = new RedirectResponse($this->getRoutePath('account_home'));
        $response->getFlashes()->add(
            'message',
            [
                'status' => 'success',
                'message' => '<span uk-icon="check"></span> your password has been updated!'
            ]
        );

        return $response;
    }
}
