<?php

namespace App\Controller\Security;

use App\Model\Entity\User;
use App\Service\Form\Annotation\Field;
use App\Service\Form\EntityFormDataManager;
use App\Service\Security\FormTokenManager;
use App\Service\Security\SecurityFormDataManager;
use App\Service\Security\UserAuthenticationChecker;
use App\Service\Security\UserPasswordChecker;
use App\Service\Security\UserSecurityCodeManager;
use App\Service\Security\UserSecurityManager;
use Climb\Controller\AbstractController;
use Climb\Exception\AppException;
use Climb\Http\Response;

class ForgotPasswordController extends AbstractController
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
    private UserAuthenticationChecker $userChecker;

    /**
     * @var UserSecurityManager $userSecurity
     */
    private UserSecurityManager $userSecurity;

    /**
     * @var UserSecurityCodeManager
     */
    private UserSecurityCodeManager $codeManager;

    /**
     * @var SecurityFormDataManager
     */
    private SecurityFormDataManager $securityFormData;

    /**
     * @var UserPasswordChecker
     */
    private UserPasswordChecker $passwordChecker;

    public function __construct(
        FormTokenManager $tokenManager,
        EntityFormDataManager $formManager,
        UserAuthenticationChecker $userChecker,
        UserSecurityManager $userSecurity,
        UserSecurityCodeManager $codeManager,
        SecurityFormDataManager $securityFormData,
        UserPasswordChecker $passwordChecker
    ) {
        $this->tokenManager     = $tokenManager;
        $this->formManager      = $formManager;
        $this->userChecker      = $userChecker;
        $this->userSecurity     = $userSecurity;
        $this->codeManager      = $codeManager;
        $this->securityFormData = $securityFormData;
        $this->passwordChecker  = $passwordChecker;
    }

    /**
     * @Route(
     *     path="/login/forgotPassword/{step}",
     *     name="forgot_password",
     *     regex={"step"="stepOne|stepTwo|stepThree|stepFour"}
     * )
     *
     * @param string $step
     *
     * @return Response
     */
    public function forgotPassword(string $step)
    {
        if ($step === 'stepThree') {
            if (
                !$this->getRequestData()->has('stepToken') ||
                !$this->tokenManager->isValid('stepThreeToken', $this->getRequestData()->get('stepToken'))
            ) {
                $this->userSecurity->unsetSessionLogin();

                return $this->redirectToRoute('forgot_password', ['step' => 'stepOne']);
            }
        }

        $token = $this->tokenManager->getToken('forgotPassword');

        $response = new Response();
        $response->setContent($this->render(
            'security/forgot_password/forgot_password.html.twig',
            [
                'step' => $step,
                'token' => $token,
                'formCheck' => $this->getRequestData()->get('formCheck'),
                'formData' => $this->getRequestData()->get('formData'),
                'message' => $this->getRequestData()->get('message'),
                'requires' => $this->getRequestData()->get('passwordRequirements')
            ]
        ));

        return $response;
    }

    /**
     * @Route(path="/login/forgotPassword/checkEmail", name="forgot_password_check_email")
     *
     * @throws AppException
     */
    public function emailCheck()
    {
        $data = $this->getRequest()->getPost();

        // Checks security token
        $tokenCheck = $this->tokenManager->isValid('forgotPassword', $data->get('token'));
        if ($tokenCheck !== true) {
            return $this->redirectToRoute(
                'forgot_password',
                ['step' => 'stepOne'],
                ['message' => $tokenCheck, 'formData' => $data->getAll()]
            );
        }

        // Check form data
        $formCheck = $this->formManager->checkFormData(User::class, $data->getAll());
        if (is_array($formCheck)) {
            return $this->redirectToRoute(
                'forgot_password',
                ['step' => 'stepOne'],
                ['formCheck' => $formCheck, 'formData' => $data->getAll()]
            );
        }

        $email = $this->formManager->filterField(Field::TYPE_EMAIL, $data->get('email'));

        // Check account
        $userCheck = $this->userChecker->checkUser($email);
        if (is_array($userCheck)) {
            return $this->redirectToRoute(
                'forgot_password',
                ['step' => 'stepOne'],
                ['formData' => $data->getAll(), 'message' => $userCheck]
            );
        }

        $this->userSecurity->setSessionLogin($email);
        $this->codeManager->dispatchSecurityCode($email);

        return $this->redirectToRoute('forgot_password', ['step' => 'stepTwo']);
    }

    /**
     * @Route(path="/login/forgotPassword/checkCode", name="forgot_password_check_code")
     */
    public function codeCheck()
    {
        $data = $this->getRequest()->getPost();

        // Checks security token
        $tokenCheck = $this->tokenManager->isValid('forgotPassword', $data->get('token'));
        if ($tokenCheck !== true) {
            $this->userSecurity->unsetSessionLogin();
            return $this->redirectToRoute(
                'forgot_password',
                ['step' => 'stepOne'],
                ['message' => $tokenCheck, 'formData' => $data->getAll()]
            );
        }

        // Check form data
        $checkCode = $this->securityFormData->checkSecurityCode($data->get('code'));
        if ($checkCode !== true) {
            return $this->redirectToRoute(
                'forgot_password',
                ['step' => 'stepTwo'],
                ['formCheck' => ['code' => $checkCode]]
            );
        }

        // Filter form data
        $code  = $this->securityFormData->filterSecurityCode($data->get('code'));
        $email = $this->userSecurity->getSessionLogin();

        // Check security code
        if (!$this->codeManager->isCodeValid($code)) {
            $this->codeManager->dispatchSecurityCode($email);
            return $this->redirectToRoute(
                'forgot_password',
                ['step' => 'stepTwo'],
                ['message' => $this->codeManager->getInvalidMessage($email)]
            );
        }

        return $this->redirectToRoute(
            'forgot_password',
            ['step' => 'stepThree'],
            ['stepToken' => $this->tokenManager->getToken('stepThreeToken')]
        );
    }

    /**
     * @Route(path="/login/forgotPassword/newCode", name="forgot_password_new_code")
     */
    public function forgotPasswordNewCode()
    {
        $email = $this->userSecurity->getSessionLogin();

        // Dispatch new code
        $this->codeManager->dispatchSecurityCode($email);

        return $this->redirectToRoute(
            'forgot_password',
            ['step' => 'stepTwo'],
            ['message' => $this->codeManager->getNewCodeMessage($email)]
        );
    }

    /**
     * @Route(path="/login/forgotPassword/checkPassword", name="forgot_password_check_password")
     *
     * @throws AppException
     */
    public function checkPassword()
    {
        if (!$this->userSecurity->hasSessionLogin()) {
            return $this->redirectToRoute('forgot_password', ['step' => 'stepOne']);
        }

        $data = $this->getRequest()->getPost();

        // Check form token
        $tokenCheck = $this->tokenManager->isValid('forgotPassword', $data->get('token'));
        if ($tokenCheck !== true) {
            $this->userSecurity->unsetSessionLogin();
            return $this->redirectToRoute('forgot_password', ['step' => 'stepOne'], ['message' => $tokenCheck]);
        }

        $stepToken = $this->tokenManager->getToken('stepThreeToken');

        // Check form data
        $pass          = $data->get('password');
        $passConfirm   = $data->get('passwordConfirm');
        $passwordCheck = $this->securityFormData->checkPasswordsData($pass, $passConfirm);
        if ($passwordCheck !== true) {
            return $this->redirectToRoute(
                'forgot_password',
                ['step' => 'stepThree'],
                ['formCheck' => $passwordCheck, 'stepToken' => $stepToken]
            );
        }

        // Check identical
        $identicalCheck = $this->passwordChecker->isIdentical($pass, $passConfirm);
        if ($identicalCheck !== true) {
            return $this->redirectToRoute(
                'forgot_password',
                ['step' => 'stepThree'],
                ['message' => $identicalCheck, 'stepToken' => $stepToken]
            );
        }

        // Check password requirements
        $requirementsCheck = $this->passwordChecker->checkPassword($pass);
        if ($requirementsCheck !== true) {
            return $this->redirectToRoute(
                'forgot_password',
                ['step' => 'stepThree'],
                [
                    'message' => ['type' => 'danger', 'message' => 'Invalid password, please check requirements'],
                    'passwordRequirements' => $requirementsCheck,
                    'stepToken' => $stepToken
                ]
            );
        }

        $this->userSecurity->saveUserPassword($pass);

        return $this->redirectToRoute('forgot_password', ['step' => 'stepFour']);
    }
}
