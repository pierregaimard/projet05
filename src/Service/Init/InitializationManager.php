<?php

namespace App\Service\Init;

use App\Model\Entity\User;
use App\Model\Entity\UserRole;
use App\Model\Entity\UserStatus;
use App\Service\Security\UserSecurityManager;
use Climb\Filesystem\FileReader;
use Climb\Orm\DbConnectionManager;
use Climb\Exception\AppException;
use Climb\Orm\Orm;
use DateTime;

class InitializationManager
{
    private const INIT_ADMIN_FIRSTNAME = 'admin';
    private const INIT_ADMIN_LASTNAME  = 'admin';
    private const INIT_ADMIN_EMAIL     = 'admin@blog.fr';
    private const INIT_ADMIN_PASSWORD  = 'admin$Pass';

    /**
     * @var DbConnectionManager
     */
    private DbConnectionManager $connector;

    /**
     * @var FileReader
     */
    private FileReader $fileReader;

    private UserSecurityManager $securityManager;

    /**
     * @var Orm
     */
    private Orm $orm;

    /**
     * @var string
     */
    private string $baseDir;

    /**
     * @var string
     */
    private string $dbName;

    /**
     * @param DbConnectionManager $connector
     * @param FileReader          $fileReader
     * @param UserSecurityManager $securityManager
     * @param Orm                 $orm
     * @param string              $baseDir
     * @param string              $dbName
     */
    public function __construct(
        DbConnectionManager $connector,
        FileReader $fileReader,
        UserSecurityManager $securityManager,
        Orm $orm,
        string $baseDir,
        string $dbName
    ) {
        $this->connector       = $connector;
        $this->fileReader      = $fileReader;
        $this->securityManager = $securityManager;
        $this->orm             = $orm;
        $this->baseDir         = $baseDir;
        $this->dbName          = $dbName;
    }

    /**
     * @return bool
     *
     * @throws AppException
     */
    public function hasProjectInit()
    {
        return $this->securityManager->hasAdminUser();
    }

    /**
     * @throws AppException
     */
    public function initializeDatabase()
    {
        $request =
            'USE ' . $this->dbName . '; ' .
            $this->fileReader->getContent(
                $this->baseDir . '/src/Database/database.sql',
                FileReader::TYPE_STRING
            );
        
        $pdo     = $this->connector->getPdo('App');
        $request = $pdo->prepare($request);
        $request->execute();
    }

    /**
     * @throws AppException
     */
    public function initAdminUser()
    {
        $manager          = $this->orm->getManager('App');
        $roleRepository   = $manager->getRepository(UserRole::class);
        $role             = $roleRepository->findOneBy(['role' => User::ROLE_ADMIN]);
        $statusRepository = $manager->getRepository(UserStatus::class);
        $status           = $statusRepository->findOneBy(['status' => User::STATUS_ACTIVE]);

        $admin = new User();
        $admin->setFirstName('admin');
        $admin->setLastName('admin');
        $admin->setEmail('admin@blog.fr');
        $admin->setPassword($this->securityManager->getPasswordHash('root$Pass'));
        $admin->addRole($role);
        $admin->setStatus($status);
        $admin->setLastSecurityCode((new DateTime('NOW'))->format('Y-m-d'));
        $admin->setBadCredentials(0);

        $manager->insertOne($admin);
    }

    /**
     * @throws AppException
     */
    public function setUser()
    {
        $manager = $this->orm->getManager('App');
        $user    = $manager->getRepository(User::class)->findOneBy(['email' => self::INIT_ADMIN_EMAIL]);
        $this->securityManager->setUser($user);
    }

    public function getData()
    {
        return [
            'messageName' => [
                'type' => 'danger',
                'message' => 'Please change generic admin account informations by yours!'
            ],
            'messageEmail' => [
                'type' => 'danger',
                'message' => 'Please set your admin email.'
            ],
            'messagePassword' => [
                'type' => 'danger',
                'message' => 'Please set a new strong password!'
            ]
        ];
    }
}
