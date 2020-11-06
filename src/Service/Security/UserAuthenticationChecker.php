<?php

namespace App\Service\Security;

use App\Model\Entity\User;
use Climb\Orm\Orm;
use Climb\Security\UserPasswordManager;
use Climb\Bag\Bag;
use Climb\Exception\AppException;

class UserAuthenticationChecker
{
    /**
     * @var Orm
     */
    private Orm $orm;

    /**
     * @var UserPasswordManager
     */
    private UserPasswordManager $passwordManager;

    /**
     * @var FormTokenManager
     */
    private FormTokenManager $tokenManager;

    /**
     * @var UserSecurityManager
     */
    private UserSecurityManager $userManager;

    /**
     * UserAuthenticationChecker constructor.
     *
     * @param Orm                 $orm
     * @param UserPasswordManager $passwordManager
     * @param FormTokenManager    $tokenManager
     * @param UserSecurityManager $userManager
     */
    public function __construct(
        Orm $orm,
        UserPasswordManager $passwordManager,
        FormTokenManager $tokenManager,
        UserSecurityManager $userManager
    ) {
        $this->orm             = $orm;
        $this->passwordManager = $passwordManager;
        $this->tokenManager    = $tokenManager;
        $this->userManager     = $userManager;
    }

    /**
     * @param Bag $loginData
     *
     * @return array|true
     *
     * @throws AppException
     */
    public function check(Bag $loginData)
    {
        // Step1: checks security token
        $tokenCheck = $this->tokenManager->isValid('authentication', $loginData->get('token'));
        if ($tokenCheck !== true) {
            return $tokenCheck;
        }

        // Step2: search user
        $userCheck = $this->checkUser($loginData->get('email'));
        if (is_array($userCheck)) {
            return $userCheck;
        }

        // Step3: checks locked user.
        $userActiveCheck = $this->checkUserActive($userCheck);
        if ($userActiveCheck !== true) {
            return $userActiveCheck;
        }

        // Step4: checks user password
        $userPasswordCheck = $this->checkUserPassword($userCheck, $loginData->get('password'));
        if ($userPasswordCheck !== true) {
            return $userPasswordCheck;
        }

        return $userCheck;
    }

    /**
     * @param string $email
     *
     * @return array|User
     *
     * @throws AppException
     */
    public function checkUser(string $email)
    {
        $entityManager = $this->orm->getManager('App');
        $user          = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user === null) {
            return [
                'type' => 'danger',
                'message' => "Account not found, please try again"
            ];
        }

        return $user;
    }

    /**
     * @param User $user
     *
     * @return array|true
     */
    private function checkUserActive(User $user)
    {
        if ($user->isLocked()) {
            return [
                'type' => 'danger',
                'message' => "Your account is locked. Please contact the administrator"
            ];
        }

        return true;
    }

    /**
     * @param User   $user
     * @param string $password
     *
     * @return array|true
     *
     * @throws AppException
     */
    private function checkUserPassword(User $user, string $password)
    {
        if (!$this->passwordManager->isPasswordValid($password, $user->getPassword())) {
            $this->userManager->increaseBadCredentials($user);
            $triesMessage = $this->getTriesMessage($user->getBadCredentials());

            $message1 = [
                'type' => 'danger',
                'message' => "Invalid password, Please try again. " . $triesMessage
            ];

            $message2 = [
                'type' => 'danger',
                'message' => "Your account have been locked. Please contact the administrator"
            ];

            return (!$user->isLocked()) ? $message1 : $message2;
        }

        if ($user->getBadCredentials() > 0) {
            $this->userManager->resetBadCredentials($user);
        }

        return true;
    }

    /**
     * @param int $badCredentials
     *
     * @return string
     */
    private function getTriesMessage(int $badCredentials): string
    {
        switch (3 - $badCredentials) {
            case 2:
                return "You have two tries";

            case 1:
            default:
                return "This is your last try. if you forgot your password, uses 'forgot password' functionality";
        }
    }
}
