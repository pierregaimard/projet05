<?php

namespace App\Controller\Account;

use App\Model\Entity\Code;
use App\Model\Entity\User;
use App\Service\Form\EntityFormDataManager;
use App\Service\Security\FormTokenManager;
use App\Service\Security\UserSecurityCodeManager;
use App\Service\Security\UserSecurityManager;
use Climb\Controller\AbstractController;
use Climb\Exception\AppException;
use Climb\Http\RedirectResponse;
use Climb\Http\Response;
use Climb\Security\UserManager;

class UserAccountController extends AbstractController
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
     * @param FormTokenManager        $tokenManager
     * @param EntityFormDataManager   $formManager
     * @param UserManager             $userManager
     * @param UserSecurityManager     $userSecurity
     * @param UserSecurityCodeManager $codeManager
     */
    public function __construct(
        FormTokenManager $tokenManager,
        EntityFormDataManager $formManager,
        UserManager $userManager,
        UserSecurityManager $userSecurity,
        UserSecurityCodeManager $codeManager
    ) {
        $this->tokenManager = $tokenManager;
        $this->formManager  = $formManager;
        $this->userManager  = $userManager;
        $this->userSecurity = $userSecurity;
        $this->codeManager  = $codeManager;
    }

    /**
     * @Route(path="/account", name="account_home")
     * @Security(roles={"ADMIN", "MEMBER"})
     *
     * @return Response
     */
    public function home()
    {
        $token       = $this->tokenManager->getToken('accountCheck');
        $requestData = $this->getRequestData();

        if ($this->getUser() === null) {
            return $this->redirectToRoute('home');
        }

        $response = new Response();
        $response->setContent($this->render(
            'account/account.html.twig',
            [
                'token' => $token,
                'messageName' => $requestData->get('messageName'),
                'messageEmail' => $requestData->get('messageEmail'),
                'messagePassword' => $requestData->get('messagePassword'),
                'formData' => $requestData->get('formData'),
                'formCheck' => $requestData->get('formCheck'),
                'emailCode' => $requestData->get('emailCode'),
                'requires' => $requestData->get('requires')
            ]
        ));

        return $response;
    }

    /**
     * @Route(path="/account/check/{type}", name="account_change", regex={"type"="name|email|password"})
     * @Security(roles={"ADMIN", "MEMBER"})
     *
     * @param string $type
     *
     * @return Response
     *
     * @throws AppException
     */
    public function checkForm(string $type)
    {
        $data = $this->getRequest()->getPost();
        $user = $this->getUser();

        // Checks security token
        $tokenCheck = $this->tokenManager->isValid('accountCheck', $data->get('token'));
        if ($tokenCheck !== true) {
            return $this->redirectToRoute(
                'account_home',
                null,
                ['messageName' => $tokenCheck, 'formData' => $data->getAll()]
            );
        }

        // Check form data
        $formCheck = $this->formManager->checkFormData(User::class, $data->getAll());
        if (is_array($formCheck)) {
            return $this->redirectToRoute(
                'account_home',
                null,
                ['formCheck' => $formCheck, 'formData' => $data->getAll()]
            );
        }

        $manager        = $this->getOrm()->getManager('App');
        $userRepository = $manager->getRepository(User::class);

        if ($type === 'email') {
            $userCheck = $userRepository->has(['email' => $data->get('email')]);
            if ($userCheck === true) {
                return $this->redirectToRoute(
                    'account_home',
                    null,
                    [
                        'messageEmail' => [
                            'type' => 'danger',
                            'message' => 'An account width this email already exists, please chose another one.'
                        ],
                        'formData' => $data->getAll()
                    ]
                );
            }

            $this->userSecurity->setSessionLogin($data->get('email'));
            $this->codeManager->dispatchSecurityCode($data->get('email'));

            return $this->redirectToRoute(
                'account_home',
                null,
                [
                    'emailCode' => true,
                    'messageEmail' => $this->codeManager->getMessage($data->get('email'))
                ]
            );
        }

        $data->remove('token');
        $this->formManager->setEntityFormData($user, $data->getAll());
        $manager->updateOne($user);
        $this->userManager->setUser($user);

        $response = new RedirectResponse($this->getRoutePath('account_home'));
        $response->getFlashes()->add(
            'message',
            [
                'status' => 'success',
                'message' => '<span uk-icon="check"></span> your name have been updated!'
            ]
        );

        return $response;
    }

    /**
     * @Route(path="/account/emailCodeCheck", name="account_email_code_check")
     * @Security(roles={"ADMIN", "MEMBER"})
     *
     * @throws AppException
     */
    public function checkCode()
    {
        $data  = $this->getRequest()->getPost();
        $user  = $this->getUser();
        $email = $this->userSecurity->getSessionLogin();

        // Checks security token
        $tokenCheck = $this->tokenManager->isValid('accountCheck', $data->get('token'));
        if ($tokenCheck !== true) {
            return $this->redirectToRoute(
                'account_home',
                null,
                ['messageName' => $tokenCheck, 'formData' => $data->getAll(), 'emailCode' => true]
            );
        }

        // Check form data
        $formCheck = $this->formManager->checkFormData(Code::class, $data->getAll());
        if (is_array($formCheck)) {
            return $this->redirectToRoute(
                'account_home',
                null,
                ['formCheck' => $formCheck, 'formData' => $data->getAll(), 'emailCode' => true]
            );
        }

        // Check security code
        $codeCheck = $this->codeManager->isCodeValid($data->get('code'));
        $this->userSecurity->unsetSessionLogin();
        $this->codeManager->unsetSessionHash();
        if (!$codeCheck) {
            return $this->redirectToRoute(
                'account_home',
                null,
                [
                    'messageEmail' => [
                        'type' => 'danger',
                        'message' => 'Sorry but your code is not valid. Please try again'
                    ]
                ]
            );
        }

        $manager        = $this->getOrm()->getManager('App');
        $userRepository = $manager->getRepository(User::class);
        $user           = $userRepository->findOne($user->getKey());
        $user->setEmail($email);
        $manager->updateOne($user);
        $this->userManager->setUser($user);

        $response = new RedirectResponse($this->getRoutePath('account_home'));
        $response->getFlashes()->add(
            'message',
            [
                'status' => 'success',
                'message' => '<span uk-icon="check"></span> your email address have been updated!'
            ]
        );

        return $response;
    }

    /**
     * @Route(path="/account/delete", name="account_delete")
     * @Security(roles={"ADMIN", "MEMBER"})
     *
     * @throws AppException
     */
    public function deleteAccount()
    {
        $manager = $this->getOrm()->getManager('App');
        $manager->deleteOne($this->getUser());
        $this->userSecurity->unsetUser();

        $response = new RedirectResponse($this->getRoutePath('home'));
        $response->getFlashes()->add(
            'message',
            [
                'status' => 'success',
                'message' => '<span uk-icon="check"></span> your account have been deleted!'
            ]
        );

        return $response;
    }
}
