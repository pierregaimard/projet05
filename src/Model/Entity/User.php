<?php

namespace App\Model\Entity;

use Climb\Orm\EntityBag;
use Climb\Security\UserInterface;
use App\Service\Form\Annotation\Field;

/**
 * @Table(name="user")
 */
class User implements UserInterface
{
    public const STATUS_VALIDATION = 'VALIDATION';
    public const STATUS_ACTIVE     = 'ACTIVE';
    public const STATUS_LOCKED     = 'LOCKED';
    public const ROLE_ADMIN        = 'ADMIN';
    public const ROLE_MEMBER       = 'MEMBER';

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
     * @Field(type="name", nullable=false, minLength=3)
     */
    private string $firstName;

    /**
     * @var string
     *
     * @Column(name="last_name")
     * @Field(type="name", nullable=false, minLength=3)
     */
    private string $lastName;

    /**
     * @var string
     *
     * @Column(name="email")
     * @Field(type="email", nullable=false)
     */
    private string $email;

    /**
     * @var string
     *
     * @Column(name="password")
     * @Field(type="password", nullable=false, minLength=8)
     */
    private string $password;

    /**
     * @var string|null
     *
     * @Column(name="last_security_code")
     */
    private ?string $lastSecurityCode;

    /**
     * @var int
     *
     * @Column(name="bad_credentials")
     */
    private int $badCredentials;

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
        $this->roles            = new EntityBag();
        $this->badCredentials   = 0;
        $this->lastSecurityCode = null;
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
     * @return string|null
     */
    public function getLastSecurityCode(): ?string
    {
        return $this->lastSecurityCode;
    }

    /**
     * @param string|null $lastSecurityCode
     */
    public function setLastSecurityCode(?string $lastSecurityCode): void
    {
        $this->lastSecurityCode = $lastSecurityCode;
    }

    /**
     * @return int
     */
    public function getBadCredentials(): int
    {
        return $this->badCredentials;
    }

    /**
     * @param int $badCredentials
     */
    public function setBadCredentials(int $badCredentials): void
    {
        $this->badCredentials = $badCredentials;
    }

    public function increaseBadCredentials(): void
    {
        $this->badCredentials += 1;
    }

    public function resetBadCredentials(): void
    {
        $this->badCredentials = 0;
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

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->getStatus()->getStatus() === self::STATUS_LOCKED;
    }

    /**
     * @param string|null $type
     *
     * @return string
     */
    public function getFormattedName(string $type = null)
    {
        switch ($type) {
            case 'navbar':
                return ucfirst(substr($this->firstName, 0, 1));
            case 'short':
                return ucfirst(substr($this->firstName, 0, 1)) . ' ' . ucfirst(strtolower($this->lastName));
            case 'long':
            default:
                return $this->firstName . ' ' . $this->lastName;
        }
    }
}
