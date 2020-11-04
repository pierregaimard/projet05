<?php

namespace App\Controller\Security;

use App\Model\Entity\User;
use App\Service\Form\EntityFormDataManager;
use App\Service\Security\FormTokenManager;
use App\Service\Security\UserAuthenticationChecker;
use Climb\Controller\AbstractController;
use Climb\Http\Response;

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

    public function __construct(
        FormTokenManager $tokenManager,
        EntityFormDataManager $formManager,
        UserAuthenticationChecker $authenticator
    ) {
        $this->tokenManager  = $tokenManager;
        $this->formManager   = $formManager;
        $this->authenticator = $authenticator;
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
            ]
        ));

        return $response;
    }

    /**
     * @Route(path="/loginCheck", name="login_check")
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
    }
}
