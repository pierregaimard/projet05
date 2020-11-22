<?php

namespace App\Service\Security;

use App\Model\Entity\UserRole;
use App\Model\Entity\UserStatus;
use App\Service\Email\EmailManager;
use Climb\Http\Session\SessionInterface;
use Climb\Orm\EntityManager;
use Climb\Orm\Orm;
use Climb\Exception\AppException;
use App\Model\Entity\User;
use Climb\Security\UserManager;
use Climb\Security\UserPasswordManager;
use DateTime;

class UserSecurityManager
{
    private const SESSION_LOGIN_KEY = 'login';

    /**
     * @var Orm
     */
    private Orm $orm;

    /**
     * @var EntityManager
     */
    private EntityManager $manager;

    /**
     * @var UserManager
     */
    private UserManager $userManager;

    /**
     * @var SessionInterface
     */
    private SessionInterface $session;

    /**
     * @var EmailManager
     */
    private EmailManager $emailManager;

    /**
     * @var UserPasswordManager
     */
    private UserPasswordManager $passwordManager;

    /**
     * UserSecurityManager constructor.
     *
     * @param Orm                 $orm
     * @param UserManager         $userManager
     * @param SessionInterface    $session
     * @param EmailManager        $emailManager
     * @param UserPasswordManager $passwordManager
     *
     * @throws AppException
     */
    public function __construct(
        Orm $orm,
        UserManager $userManager,
        SessionInterface $session,
        EmailManager $emailManager,
        UserPasswordManager $passwordManager
    ) {
        $this->orm             = $orm;
        $this->manager         = $orm->getManager('App');
        $this->userManager     = $userManager;
        $this->session         = $session;
        $this->emailManager    = $emailManager;
        $this->passwordManager = $passwordManager;
    }

    /**
     * @param User $user
     *
     * @throws AppException
     */
    public function increaseBadCredentials(User $user): void
    {
        $user->increaseBadCredentials();
        $this->manager->updateOne($user);

        if ($user->getBadCredentials() > 2) {
            $this->lockUser($user);
            $this->sendUserLockNotificationToAdmin($user->getFormattedName('long'));
        }
    }

    /**
     * @param User $user
     *
     * @throws AppException
     */
    public function resetBadCredentials(User $user): void
    {
        $user->resetBadCredentials();
        $this->manager->updateOne($user);
    }

    /**
     * @param User $user
     *
     * @throws AppException
     */
    public function lockUser(User $user): void
    {
        $lockedStatus = $this->getUserStatus(User::STATUS_LOCKED);
        $user->setStatus($lockedStatus);
        $this->manager->updateOne($user);
    }

    /**
     * @param string $name
     *
     * @throws AppException
     */
    private function sendUserLockNotificationToAdmin(string $name): void
    {
        $admin = $this->getAdminUser();

        $this->emailManager->send(
            $admin->getEmail(),
            'User lock notification',
            'security/authentication/_email_admin_lock_notification.html.twig',
            ['name' => $name]
        );
    }

    /**
     * @return User|null
     *
     * @throws AppException
     */
    public function getAdminUser(): ?User
    {
        $roleRepository = $this->manager->getRepository(UserRole::class);
        $role           = $roleRepository->findOneBy(['role' => User::ROLE_ADMIN]);

        return $role->getUsers()[array_key_first($role->getUsers())];
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->userManager->setUser($user);
    }

    public function unsetUser(): void
    {
        $this->userManager->unsetUser();
    }

    /**
     * @return bool
     */
    public function hasUser(): bool
    {
        return $this->userManager->hasUser();
    }

    /**
     * @param User $user
     *
     * @throws AppException
     */
    public function updateLastSecurityCode(User $user)
    {
        $date = new DateTime('NOW');
        $user->setLastSecurityCode($date->format('Y-m-d'));
        $this->manager->updateOne($user);
    }

    /**
     * @param string $email
     */
    public function setSessionLogin(string $email): void
    {
        $this->session->add(self::SESSION_LOGIN_KEY, $email);
    }

    /**
     * @return string|null
     */
    public function getSessionLogin(): ?string
    {
        return $this->session->get(self::SESSION_LOGIN_KEY);
    }

    public function unsetSessionLogin(): void
    {
        $this->session->remove(self::SESSION_LOGIN_KEY);
    }

    /**
     * @return bool
     */
    public function hasSessionLogin(): bool
    {
        return $this->session->has(self::SESSION_LOGIN_KEY);
    }

    /**
     * @param string $password
     *
     * @throws AppException
     */
    public function saveUserPassword(string $password): void
    {
        $userRepository = $this->manager->getRepository(User::class);
        $user           = $userRepository->findOneBy(['email' => $this->getSessionLogin()]);
        $user->setPassword($this->passwordManager->getPasswordHash($password));

        $this->manager->updateOne($user);
    }

    /**
     * @param string $password
     *
     * @return string|null
     */
    public function getPasswordHash(string $password)
    {
        return $this->passwordManager->getPasswordHash($password);
    }

    /**
     * @param string $status
     *
     * @return object|null
     *
     * @throws AppException
     */
    private function getUserStatus(string $status)
    {
        $statusRepository = $this->manager->getRepository(UserStatus::class);

        return $statusRepository->findOneBy(['status' => $status]);
    }
}
