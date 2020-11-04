<?php

namespace App\Controller\Security;

use App\Model\Entity\User;
use App\Service\Form\EntityFormDataManager;
use Climb\Controller\AbstractController;
use Climb\Exception\AppException;
use Climb\Http\Response;
use Climb\Security\TokenManager;

class AuthenticationController extends AbstractController
{
    /**
     * @var TokenManager
     */
    private TokenManager $tokenManager;

    /**
     * @var EntityFormDataManager
     */
    private EntityFormDataManager $formManager;

    public function __construct(TokenManager $tokenManager, EntityFormDataManager $formManager)
    {
        $this->tokenManager = $tokenManager;
        $this->formManager  = $formManager;
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
                [
                    'formCheck' => $formCheck,
                    'formData' => $data->getAll(),
                ]
            );
        }
    }
}
