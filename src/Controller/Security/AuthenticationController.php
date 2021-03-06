<?php

namespace App\Controller\Security;

use App\Model\Entity\User;
use App\Service\Form\EntityFormDataManager;
use App\Service\Security\FormTokenManager;
use App\Service\Security\UserAuthenticationChecker;
use App\Service\Security\UserSecurityCodeManager;
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
     * @var UserSecurityCodeManager
     */
    private UserSecurityCodeManager $codeManager;

    /**
     * @param FormTokenManager          $tokenManager
     * @param EntityFormDataManager     $formManager
     * @param UserAuthenticationChecker $authenticator
     * @param UserSecurityCodeManager   $codeManager
     * @param UserSecurityManager       $userManager
     */
    public function __construct(
        FormTokenManager $tokenManager,
        EntityFormDataManager $formManager,
        UserAuthenticationChecker $authenticator,
        UserSecurityCodeManager $codeManager,
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
        $data = $this->getRequest()->getPost();

        // Check security token
        $tokenCheck = $this->tokenManager->isValid('authentication', $data->get('token'));
        if ($tokenCheck !== true) {
            return $this->redirectToRoute(
                'login',
                null,
                ['message' => $tokenCheck, 'formData' => $data->getAll()]
            );
        }

        // Check form data
        $formCheck = $this->formManager->checkFormData(User::class, $data->getAll());
        if (is_array($formCheck)) {
            return $this->redirectToRoute(
                'login',
                null,
                ['formCheck' => $formCheck, 'formData' => $data->getAll()]
            );
        }

        // Check credentials
        $userAuthCheck = $this->authenticator->check($data);
        if (is_array($userAuthCheck)) {
            return $this->redirectToRoute(
                'login',
                null,
                ['message' => $userAuthCheck, 'formData' => $data->getAll()]
            );
        }

        // Check security code needs
        if ($this->codeManager->needSecurityCode($userAuthCheck)) {
            $this->codeManager->dispatchSecurityCode($userAuthCheck->getEmail());
            $this->userManager->setSessionLogin($userAuthCheck->getEmail());

            return $this->redirectToRoute(
                'login',
                null,
                ['securityCode' => true, 'message' => $this->codeManager->getMessage($userAuthCheck->getEmail())]
            );
        }

        // Set user session
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
