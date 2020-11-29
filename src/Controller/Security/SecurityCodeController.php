<?php

namespace App\Controller\Security;

use App\Service\Security\FormTokenManager;
use App\Service\Security\SecurityFormDataManager;
use App\Service\Security\UserAuthenticationChecker;
use App\Service\Security\UserSecurityCodeManager;
use App\Service\Security\UserSecurityManager;
use App\Service\Security\UserSignUpManager;
use Climb\Controller\AbstractController;
use Climb\Exception\AppException;

class SecurityCodeController extends AbstractController
{
    /**
     * @var FormTokenManager
     */
    private FormTokenManager $tokenManager;

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
     * @var SecurityFormDataManager
     */
    private SecurityFormDataManager $securityFormData;

    /**
     * @var UserSignUpManager
     */
    private UserSignUpManager $signUpManager;

    /**
     * @param FormTokenManager          $tokenManager
     * @param UserAuthenticationChecker $authenticator
     * @param UserSecurityCodeManager   $codeManager
     * @param UserSecurityManager       $userManager
     * @param SecurityFormDataManager   $securityFormData
     * @param UserSignUpManager         $signUpManager
     */
    public function __construct(
        FormTokenManager $tokenManager,
        UserAuthenticationChecker $authenticator,
        UserSecurityCodeManager $codeManager,
        UserSecurityManager $userManager,
        SecurityFormDataManager $securityFormData,
        UserSignUpManager $signUpManager
    ) {
        $this->tokenManager     = $tokenManager;
        $this->authenticator    = $authenticator;
        $this->codeManager      = $codeManager;
        $this->userManager      = $userManager;
        $this->securityFormData = $securityFormData;
        $this->signUpManager    = $signUpManager;
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
        $checkCode = $this->securityFormData->checkSecurityCode($data->get('code'));

        if ($checkCode !== true) {
            return $this->redirectToRoute(
                'login',
                null,
                ['securityCode' => true, 'formCheck' => ['code' => $checkCode]]
            );
        }

        // Filter form data
        $code = $this->securityFormData->filterSecurityCode($data->get('code'));

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
            $this->codeManager->dispatchSecurityCode($user->getEmail());
            return $this->redirectToRoute(
                'login',
                null,
                ['securityCode' => true, 'message' => $this->codeManager->getInvalidMessage($user->getEmail())]
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
    public function loginNewCode()
    {
        // Get user entity
        $user = $this->authenticator->checkUser($this->userManager->getSessionLogin());

        // Dispatch new code
        $this->codeManager->dispatchSecurityCode($user);

        return $this->redirectToRoute(
            'login',
            null,
            ['securityCode' => true, 'message' => $this->codeManager->getNewCodeMessage($user->getEmail())]
        );
    }

    /**
     * @Route(path="/signUp/newCode", name="signup_code_new")
     */
    public function signUpNewCode()
    {
        $email = $this->signUpManager->getTempUser()['email'];

        // Dispatch new code
        $this->codeManager->dispatchSecurityCode($email);

        return $this->redirectToRoute(
            'sign_up',
            ['step' => 'stepTwo'],
            ['message' => $this->codeManager->getNewCodeMessage($email)]
        );
    }
}
