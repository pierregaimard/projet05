<?php

namespace App\Service\Security;

use App\Model\Entity\User;
use App\Service\Email\EmailManager;
use Climb\Templating\Twig\TemplatingManager;
use DateTime;
use Exception;
use Twig\Environment;
use Climb\Exception\AppException;

class UserAuthenticationCodeManager
{
    /**
     * @var EmailManager
     */
    private EmailManager $emailManager;

    /**
     * @var SecurityCodeManager
     */
    private SecurityCodeManager $codeManager;

    /**
     * @var UserSecurityManager
     */
    private UserSecurityManager $userManager;

    /**
     * @var Environment
     */
    private Environment $templating;

    /**
     * UserAuthenticationCodeManager constructor.
     *
     * @param EmailManager        $emailManager
     * @param SecurityCodeManager $codeManager
     * @param UserSecurityManager $userManager
     * @param TemplatingManager   $templatingManager
     *
     * @throws AppException
     */
    public function __construct(
        EmailManager $emailManager,
        SecurityCodeManager $codeManager,
        UserSecurityManager $userManager,
        TemplatingManager $templatingManager
    ) {
        $this->emailManager = $emailManager;
        $this->codeManager  = $codeManager;
        $this->userManager  = $userManager;
        $this->templating   = $templatingManager->getEnvironment([]);
    }

    /**
     * @param User $user
     *
     * @return bool|int
     */
    public function needSecurityCode(User $user)
    {
        $now          = new DateTime('NOW');

        try {
            $lastCodeDate = new DateTime($user->getLastSecurityCode());
        } catch (Exception $exception) {
            return (int)$exception->getCode();
        }

        $newCodeDate  = $lastCodeDate->modify('+15 day');

        if ($now >= $newCodeDate) {
            return true;
        }
        return false;
    }

    /**
     * @param User $user
     *
     * @return int
     */
    public function sendSecurityCode(User $user)
    {
        $code = $this->codeManager->generateCode();

        $message = $this->templating->render(
            'security/authentication/_email_security_code.html.twig',
            ['code' => $code]
        );

        $this->emailManager->send($user->getEmail(), 'Authentication code', $message);
        $this->userManager->updateLastSecurityCode($user);

        return $code;
    }
}
