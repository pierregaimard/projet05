<?php

namespace App\Controller\Security;

use App\Model\Entity\User;
use App\Service\Form\Annotation\Field;
use App\Service\Form\EntityFormDataManager;
use App\Service\Security\FormTokenManager;
use App\Service\Security\SecurityFormDataManager;
use App\Service\Security\UserPasswordChecker;
use App\Service\Security\UserSecurityCodeManager;
use App\Service\Security\UserSignUpManager;
use Climb\Controller\AbstractController;
use Climb\Exception\AppException;
use Climb\Http\Response;
use Climb\Routing\Annotation\Route;

class SignUpController extends AbstractController
{
    /**
     * @var FormTokenManager
     */
    private FormTokenManager $tokenManager;

    /**
     * @var EntityFormDataManager
     */
    private EntityFormDataManager $formDataManager;

    /**
     * @var UserSignUpManager
     */
    private UserSignUpManager $signUpManager;

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

    /**
     * @param FormTokenManager        $tokenManager
     * @param EntityFormDataManager   $formDataManager
     * @param UserSignUpManager       $signUpManager
     * @param UserSecurityCodeManager $codeManager
     * @param SecurityFormDataManager $securityFormData
     * @param UserPasswordChecker     $passwordChecker
     */
    public function __construct(
        FormTokenManager $tokenManager,
        EntityFormDataManager $formDataManager,
        UserSignUpManager $signUpManager,
        UserSecurityCodeManager $codeManager,
        SecurityFormDataManager $securityFormData,
        UserPasswordChecker $passwordChecker
    ) {
        $this->tokenManager     = $tokenManager;
        $this->formDataManager  = $formDataManager;
        $this->signUpManager    = $signUpManager;
        $this->codeManager      = $codeManager;
        $this->securityFormData = $securityFormData;
        $this->passwordChecker  = $passwordChecker;
    }

    /**
     * @Route(path="/signUp/{step}", name="sign_up", regex={"step"="stepOne|stepTwo|stepThree|stepFour"})
     *
     * @param string $step
     *
     * @return Response
     */
    public function signUp(string $step)
    {
        $token = $this->tokenManager->getToken('SignUpForm');
        $data  = $this->getRequestData();

        // Security code checker for step three
        if ($step === 'stepThree' && $data->get('checkCode') === true) {
            if (!$this->codeManager->isCodeValid($this->getRequestData()->get('code'))) {
                $this->codeManager->unsetSessionHash();
                $this->signUpManager->unsetTempUser();

                return $this->redirectToRoute(
                    'sign_up',
                    ['step' => 'stepOne'],
                    ['message' => ['type' => 'danger', 'message' => 'Security alert, please try again']]
                );
            }
        }

        $response = new Response();
        $response->setContent($this->render(
            'security/signup/sign_up.html.twig',
            [
                'step' => $step,
                'token' => $token,
                'message' => $data->get('message'),
                'formCheck' => $data->get('formCheck'),
                'formData' => $data->get('formData'),
                'requires' => $data->get('passwordRequirements'),
                'firstName' => ucfirst(strtolower($data->get('firstName'))),
            ]
        ));

        return $response;
    }

    /**
     * @Route(path="/signUp/Check/StepOne", name="sign_up_check")
     */
    public function signUpCheck()
    {
        $data = $this->getRequest()->getPost();

        // Check form token
        $tokenCheck = $this->tokenManager->isValid('SignUpForm', $data->get('token'));
        if ($tokenCheck !== true) {
            return $this->redirectToRoute('sign_up', ['step' => 'stepOne'], ['message' => $tokenCheck,]);
        }

        // Check form data
        $formCheck = $this->formDataManager->checkFormData(User::class, $data->getAll());
        if ($formCheck !== true) {
            return $this->redirectToRoute(
                'sign_up',
                ['step' => 'stepOne'],
                ['formCheck' => $formCheck, 'formData' => $data->getAll()]
            );
        }

        // Check username duplication
        $usernameDuplication = $this->signUpManager->checkUserDuplication($data->get('email'));
        if ($usernameDuplication !== true) {
            return $this->redirectToRoute(
                'sign_up',
                ['step' => 'stepOne'],
                ['message' => $usernameDuplication, 'formData' => $data->getAll()]
            );
        }

        // Dispatch security code for email address validation
        $this->codeManager->dispatchSecurityCode($data->get('email'));

        // Store temp user informations
        $this->signUpManager->setTempUser([
            'firstName' => $this->formDataManager->filterField(Field::TYPE_NAME, $data->get('firstName')),
            'lastName' => $this->formDataManager->filterField(Field::TYPE_NAME, $data->get('lastName')),
            'email' => $this->formDataManager->filterField(Field::TYPE_EMAIL, $data->get('email'))
        ]);

        return $this->redirectToRoute('sign_up', ['step' => 'stepTwo']);
    }

    /**
     * @Route(path="/SignUp/Check/StepTwo", name="sign_up_check_two")
     */
    public function signUpCheckTwo()
    {
        $data = $this->getRequest()->getPost();

        // Check form token
        $tokenCheck = $this->tokenManager->isValid('SignUpForm', $data->get('token'));
        if ($tokenCheck !== true) {
            return $this->redirectToRoute('sign_up', ['step' => 'stepTwo'], ['message' => $tokenCheck]);
        }

        // Check form data
        $checkCodeData = $this->securityFormData->checkSecurityCode($data->get('code'));
        if ($checkCodeData !== true) {
            return $this->redirectToRoute(
                'sign_up',
                ['step' => 'stepTwo'],
                ['formCheck' => ['code' => $checkCodeData]]
            );
        }

        // Filter code data
        $code = $this->securityFormData->filterSecurityCode($data->get('code'));

        // Check security code
        $tempEmail = $this->signUpManager->getTempUser()['email'];
        if (!$this->codeManager->isCodeValid($code)) {
            $this->codeManager->dispatchSecurityCode($tempEmail);
            return $this->redirectToRoute(
                'sign_up',
                ['step' => 'stepTwo'],
                ['message' => $this->codeManager->getInvalidMessage($tempEmail)]
            );
        }

        return $this->redirectToRoute('sign_up', ['step' => 'stepThree'], ['code' => $code, 'checkCode' => true]);
    }

    /**
     * @Route(path="/SignUp/Check/StepThree", name="sign_up_check_three")
     *
     * @throws AppException
     */
    public function signUpCheckThree()
    {
        $data = $this->getRequest()->getPost();

        // Check form token
        $tokenCheck = $this->tokenManager->isValid('SignUpForm', $data->get('token'));
        if ($tokenCheck !== true) {
            return $this->redirectToRoute('sign_up', ['step' => 'stepThree'], ['message' => $tokenCheck]);
        }

        // Check form data
        $pass          = $data->get('password');
        $passConfirm   = $data->get('passwordConfirm');
        $passwordCheck = $this->securityFormData->checkPasswordsData($pass, $passConfirm);
        if ($passwordCheck !== true) {
            return $this->redirectToRoute(
                'sign_up',
                ['step' => 'stepThree'],
                ['formCheck' => $passwordCheck, 'checkCode' => false]
            );
        }

        // Check identical
        $identicalCheck = $this->passwordChecker->isIdentical($pass, $passConfirm);
        if ($identicalCheck !== true) {
            return $this->redirectToRoute(
                'sign_up',
                ['step' => 'stepThree'],
                ['message' => $identicalCheck, 'checkCode' => false]
            );
        }

        // Check password requirements
        $requirementsCheck = $this->passwordChecker->checkPassword($pass);
        if ($requirementsCheck !== true) {
            return $this->redirectToRoute(
                'sign_up',
                ['step' => 'stepThree'],
                [
                    'checkCode' => false,
                    'message' => ['type' => 'danger', 'message' => 'Invalid password, please check requirements'],
                    'passwordRequirements' => $requirementsCheck
                ]
            );
        }

        $user = $this->signUpManager->setFinalUser($pass);
        $this->signUpManager->sendNewUserNotificationToAdmin($user->getFormattedName('long'));

        return $this->redirectToRoute('sign_up', ['step' => 'stepFour'], ['firstName' => $user->getFirstName()]);
    }
}
