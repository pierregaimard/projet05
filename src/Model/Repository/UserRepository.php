<?php

namespace App\Model\Repository;

use App\Model\Entity\User;
use Climb\Orm\EntityRepository;
use Climb\Exception\AppException;

class UserRepository extends EntityRepository
{
    /**
     * @return User[]|null
     *
     * @throws AppException
     */
    public function findValidatedMembers()
    {
        $request = "
            SELECT * FROM user
            INNER JOIN as_user_role ON user.id = as_user_role.id_user AND as_user_role.id_user_role = :user_role
            WHERE user.id_status != :user_status
            ORDER BY user.first_name ASC, user.last_name ASC 
        ";

        return $this->findByRequest(
            $request,
            ['user_role' => 2, 'user_status' => 1]
        );
    }

    /**
     * @param string $role
     *
     * @return User[]|null
     *
     * @throws AppException
     */
    public function findByRole(string $role)
    {
        $request = "
            SELECT * FROM user
            INNER JOIN as_user_role ON user.id = as_user_role.id_user 
            INNER JOIN user_role ON as_user_role.id_user_role = user_role.id AND user_role.role = :role
            ORDER BY user.first_name ASC, user.last_name ASC 
        ";

        return $this->findByRequest(
            $request,
            ['role' => $role]
        );
    }
}
