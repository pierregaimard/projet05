<?php

namespace App\Service\Security;

use App\Model\Entity\UserStatus;
use Climb\Orm\EntityManager;
use Climb\Orm\Orm;
use Climb\Exception\AppException;
use App\Model\Entity\User;
use Climb\Security\UserManager;
use DateTime;

class UserSecurityManager
{
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
     * UserSecurityManager constructor.
     *
     * @param Orm         $orm
     * @param UserManager $userManager
     *
     * @throws AppException
     */
    public function __construct(Orm $orm, UserManager $userManager)
    {
        $this->orm         = $orm;
        $this->manager     = $orm->getManager('App');
        $this->userManager = $userManager;
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
