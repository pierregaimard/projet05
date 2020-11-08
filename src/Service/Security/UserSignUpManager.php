<?php

namespace App\Service\Security;

use App\Model\Entity\User;
use Climb\Orm\EntityManager;
use Climb\Orm\Orm;
use Climb\Exception\AppException;

class UserSignUpManager
{
    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

    /**
     * @param Orm $orm
     *
     * @throws AppException
     */
    public function __construct(Orm $orm)
    {
        $this->entityManager = $orm->getManager('App');
    }

    /**
     * @param string $email
     *
     * @return string[]|true
     * @throws AppException
     */
    public function checkUserDuplication(string $email)
    {
        $repository = $this->entityManager->getRepository(User::class);
        $user       = $repository->findOneBy(['email' => $email]);

        if ($user === null) {
            return true;
        }

        return [
            'type' => 'danger',
            'message' => 'An account width this email address already exists. Please try with an other one or ' .
                'uses "forgot password" functionality.'
        ];
    }
}
