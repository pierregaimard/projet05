<?php

namespace App\Controller\Security;

use App\Service\Form\Annotation\Field;
use App\Service\Form\DataChecker\DataChecker;
use App\Service\Form\EntityFormDataManager;
use App\Service\Security\FormTokenManager;
use App\Service\Security\UserAuthenticationChecker;
use App\Service\Security\UserAuthenticationCodeManager;
use App\Service\Security\UserSecurityManager;
use Climb\Controller\AbstractController;
use Climb\Exception\AppException;

class SecurityCodeController extends AbstractController
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
     * @Route(path="/login/securityCode", name="login_code_check")
     *
     * @throws AppException
     */
    public function codeCheck()
    {
        $data = $this->getRequest()->getPost();

        // Check form token
        $tokenCheck = $this->tokenManager->isValid('authentication', $data->get('token'));
        if ($tokenCheck !== true) {
            return $this->redirectToRoute(
                'login',
                null,
                ['securityCode' => true, 'message' => $tokenCheck]
            );
        }

        // Check form data
        $code  = $data->get('code');
        $field = new Field();
        $field->setType(Field::TYPE_NUMBER);
        $field->setNullable(false);
        $checkCode = $this->formManager->checkFormField($code, $field);

        if ($checkCode !== true) {
            return $this->redirectToRoute(
                'login',
                null,
                ['securityCode' => true, 'message' => ['type' => 'danger', 'message' => $checkCode]]
            );
        }

        // Filter form data
        $code = $this->formManager->filterField('number', $code);

        // Get user entity
        $user = $this->authenticator->checkUser($this->userManager->getSessionLogin());

        // Check user
        if (!is_object($user)) {
            return $this->redirectToRoute(
                'login',
                null,
                ['message' => ['type' => 'danger', 'message' => 'Sorry, a problem occurred. Please try again']]
            );
        }

        // Check security code
        if (!$this->codeManager->isCodeValid($code)) {
            $this->codeManager->dispatchSecurityCode($user);
            return $this->redirectToRoute(
                'login',
                null,
                ['securityCode' => true, 'message' => $this->codeManager->getInvalidMessage()]
            );
        }

        // Unset security code data in session
        $this->userManager->unsetSessionLogin();
        $this->codeManager->unsetSessionHash();

        // Set user session
        $this->userManager->setUser($user);
        $this->userManager->updateLastSecurityCode($user);

        return $this->redirectToRoute('home');
    }

    /**
     * @Route(path="/newSecurityCode", name="login_code_new")
     *
     * @throws AppException
     */
    public function sendNewCode()
    {
        // Get user entity
        $user = $this->authenticator->checkUser($this->userManager->getSessionLogin());

        // Dispatch new code
        $this->codeManager->dispatchSecurityCode($user);

        return $this->redirectToRoute(
            'login',
            null,
            ['securityCode' => true, 'message' => $this->codeManager->getMessage($user)]
        );
    }
}
