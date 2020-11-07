<?php

namespace App\Controller\Security;

use App\Model\Entity\User;
use App\Service\Form\EntityFormDataManager;
use App\Service\Security\FormTokenManager;
use App\Service\Security\UserAuthenticationChecker;
use App\Service\Security\UserAuthenticationCodeManager;
use App\Service\Security\UserSecurityManager;
use Climb\Controller\AbstractController;
use Climb\Exception\AppException;
use Climb\Http\Response;
use Climb\Routing\Annotation\Route;

class AuthenticationController extends AbstractController
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
     * @var UserAuthenticationChecker
     */
    private UserAuthenticationChecker $authenticator;

    /**
     * @var UserSecurityManager
     */
    private UserSecurityManager $userManager;

    /**
     * @var UserAuthenticationCodeManager
     */
    private UserAuthenticationCodeManager $codeManager;

    /**
     * @param FormTokenManager              $tokenManager
     * @param EntityFormDataManager         $formManager
     * @param UserAuthenticationChecker     $authenticator
     * @param UserAuthenticationCodeManager $codeManager
     * @param UserSecurityManager           $userManager
     */
    public function __construct(
        FormTokenManager $tokenManager,
        EntityFormDataManager $formManager,
        UserAuthenticationChecker $authenticator,
        UserAuthenticationCodeManager $codeManager,
        UserSecurityManager $userManager
    ) {
        $this->tokenManager  = $tokenManager;
        $this->formManager   = $formManager;
        $this->authenticator = $authenticator;
        $this->codeManager   = $codeManager;
        $this->userManager   = $userManager;
    }

    /**
     * @Route(path="/login", name="login")
     */
    public function login()
    {
        $token = $this->tokenManager->getToken('authentication');

        $response = new Response();
        $response->setContent($this->render(
            'security/authentication/login.html.twig',
            [
                'token' => $token,
                'formCheck' => $this->getRequestData()->get('formCheck'),
                'formData' => $this->getRequestData()->get('formData'),
                'message' => $this->getRequestData()->get('message'),
                'securityCode' => $this->getRequestData()->get('securityCode'),
            ]
        ));

        return $response;
    }

    /**
     * @Route(path="/loginCheck", name="login_check")
     *
     * @throws AppException
     */
    public function loginCheck()
    {
        $data      = $this->getRequest()->getPost();
        $formCheck = $this->formManager->checkFormData(User::class, $data->getAll());

        if (is_array($formCheck)) {
            return $this->redirectToRoute(
                'login',
                null,
                ['formCheck' => $formCheck, 'formData' => $data->getAll()]
            );
        }

        $userAuthCheck = $this->authenticator->check($data);
        if (is_array($userAuthCheck)) {
            return $this->redirectToRoute(
                'login',
                null,
                ['message' => $userAuthCheck, 'formData' => $data->getAll()]
            );
        }

        if ($this->codeManager->needSecurityCode($userAuthCheck)) {
            $this->codeManager->dispatchSecurityCode($userAuthCheck);
            $this->userManager->setSessionLogin($userAuthCheck->getEmail());

            return $this->redirectToRoute(
                'login',
                null,
                ['securityCode' => true, 'message' => $this->codeManager->getMessage($userAuthCheck)]
            );
        }

        $this->userManager->setUser($userAuthCheck);

        return $this->redirectToRoute('home');
    }

    /**
     * @Route(path="/logout", name="logout")
     */
    public function logout()
    {
        $this->userManager->unsetUser();

        return $this->redirectToRoute('home');
    }
}
