<?php

namespace App\Service\Security;

use App\Model\Entity\User;
use App\Model\Entity\UserRole;
use App\Model\Entity\UserStatus;
use App\Service\Email\EmailManager;
use App\Service\Templating\TemplatingManager;
use Climb\Http\Session\SessionInterface;
use Climb\Orm\EntityManager;
use Climb\Orm\Orm;
use Climb\Exception\AppException;
use Climb\Security\UserPasswordManager;

class UserSignUpManager
{
    private const TEMP_USER_KEY = 'tempUser';

    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

    /**
     * @var SessionInterface
     */
    private SessionInterface $session;

    /**
     * @var UserPasswordManager
     */
    private UserPasswordManager $passwordManager;

    /**
     * @var EmailManager
     */
    private EmailManager $emailManager;

    private TemplatingManager $templating;

    /**
     * @param Orm                 $orm
     * @param SessionInterface    $session
     * @param UserPasswordManager $passwordManager
     * @param EmailManager        $emailManager
     * @param TemplatingManager   $templating
     *
     * @throws AppException
     */
    public function __construct(
        Orm $orm,
        SessionInterface $session,
        UserPasswordManager $passwordManager,
        EmailManager $emailManager,
        TemplatingManager $templating
    ) {
        $this->entityManager   = $orm->getManager('App');
        $this->session         = $session;
        $this->passwordManager = $passwordManager;
        $this->emailManager    = $emailManager;
        $this->templating      = $templating;
    }

    /**
     * @param string $email
     *
     * @return string[]|true
     *
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

    /**
     * @param array $tempUserData
     */
    public function setTempUser(array $tempUserData): void
    {
        $this->session->add(self::TEMP_USER_KEY, $tempUserData);
    }

    /**
     * @return array
     */
    public function getTempUser(): array
    {
        return $this->session->get(self::TEMP_USER_KEY);
    }

    public function unsetTempUser(): void
    {
        $this->session->remove(self::TEMP_USER_KEY);
    }

    /**
     * @param string $password
     *
     * @return User
     *
     * @throws AppException
     */
    public function setFinalUser(string $password): User
    {
        $statusRepository = $this->entityManager->getRepository(UserStatus::class);
        $status           = $statusRepository->findOneBy(['status' => User::STATUS_VALIDATION]);
        $roleRepository   = $this->entityManager->getRepository(UserRole::class);
        $role             = $roleRepository->findOneBy(['role' => User::ROLE_MEMBER]);

        $tempUserData = $this->getTempUser();
        $user         = new User();
        $user->setFirstName($tempUserData['firstName']);
        $user->setLastName($tempUserData['lastName']);
        $user->setEmail($tempUserData['email']);
        $user->setPassword($this->passwordManager->getPasswordHash($password));
        $user->setStatus($status);
        $user->addRole($role);

        $this->entityManager->insertOne($user);

        return $user;
    }

    /**
     * @param string $name
     *
     * @throws AppException
     */
    public function sendNewUserNotificationToAdmin(string $name): void
    {
        $roleRepository = $this->entityManager->getRepository(UserRole::class);
        $role           = $roleRepository->findOneBy(['role' => User::ROLE_ADMIN]);
        $admin          = $role->getUsers()[array_key_first($role->getUsers())];
        
        $this->emailManager->send(
            $admin->getEmail(),
            'New member subscription',
            $this->templating->render('security/signup/_email_admin_notification.html.twig', ['name' => $name])
        );
    }
}
