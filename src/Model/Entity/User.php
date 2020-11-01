<?php

namespace App\Model\Entity;

use Climb\Orm\EntityBag;
use Climb\Security\UserInterface;

/**
 * @Table(name="user")
 */
class User implements UserInterface
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
     * @Column(name="first_name")
     */
    private string $firstName;

    /**
     * @var string
     *
     * @Column(name="last_name")
     */
    private string $lastName;

    /**
     * @var string
     *
     * @Column(name="email")
     */
    private string $email;

    /**
     * @var string
     *
     * @Column(name="password")
     */
    private string $password;

    /**
     * @var string
     *
     * @Column(name="last_login_date")
     */
    private string $lastLoginDate;

    /**
     * @var UserStatus
     *
     * @Relation(type="entity", entity="App\Model\Entity\UserStatus")
     */
    private UserStatus $status;

    /**
     * @var EntityBag
     *
     * @Relation(type="association", entity="App\Model\Entity\UserRole", association="as_user_role")
     */
    private EntityBag $roles;

    public function __construct()
    {
        $this->roles = new EntityBag();
    }

    public function isGranted(string $role): bool
    {
        $isGranted = false;
        foreach ($this->roles as $userRole) {
            if ($userRole->getRole() === $role) {
                $isGranted = true;
            }
        }

        return $isGranted;
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
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getLastLoginDate(): string
    {
        return $this->lastLoginDate;
    }

    /**
     * @param string $lastLoginDate
     */
    public function setLastLoginDate(string $lastLoginDate): void
    {
        $this->lastLoginDate = $lastLoginDate;
    }

    /**
     * @return UserStatus
     */
    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    /**
     * @param UserStatus $status
     */
    public function setStatus(UserStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * @return UserRole[]
     */
    public function getRoles()
    {
        return $this->roles->getAll();
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles->setAll($roles);
    }

    /**
     * @param UserRole $role
     */
    public function addRole(UserRole $role): void
    {
        $this->roles->add($role->getKey(), $role);
    }

    /**
     * @param UserRole $role
     */
    public function removeRole(UserRole $role): void
    {
        $this->roles->remove($role->getKey());
    }
}
