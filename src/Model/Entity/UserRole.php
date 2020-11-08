<?php

namespace App\Model\Entity;

use Climb\Orm\EntityBag;

/**
 * @Table(name="user_role")
 */
class UserRole
{
    /**
     * @var int
     *
     * @Column(name="id")
     */
    private int $key;

    /**
     * @var string
     *
     * @Column(name="role")
     */
    private string $role;

    /**
     * @var EntityBag
     *
     * @Relation(type="association", entity="App\Model\Entity\User", association="as_user_role", invertedBy="roles")
     */
    private EntityBag $users;

    public function __construct()
    {
        $this->users = new EntityBag();
    }

    /**
     * @return int
     */
    public function getKey(): int
    {
        return $this->key;
    }

    /**
     * @param int $key
     */
    public function setKey(int $key): void
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param string $role
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * @return User[]
     */
    public function getUsers()
    {
        return $this->users->getAll();
    }

    /**
     * @param array $users
     */
    public function setUsers(array $users): void
    {
        $this->users->setAll($users);
    }

    /**
     * @param User $user
     */
    public function addUser(User $user): void
    {
        $this->users->add($user->getKey(), $user);
    }

    /**
     * @param User $user
     */
    public function removeUser(User $user): void
    {
        $this->users->remove($user->getKey());
    }
}
