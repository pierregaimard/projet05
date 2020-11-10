<?php

namespace App\Service\Security;

use App\Model\Entity\User;
use App\Service\Email\EmailManager;
use Climb\Http\Session\SessionInterface;
use Climb\Security\TokenManager;
use DateTime;
use Exception;

class UserSecurityCodeManager
{
    private const HASH_KEYWORD     = 'tSYDgd548s5$dks';
    private const SESSION_CODE_KEY = 'HYsgdycHSGtdg54$GHF';

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
     * @var TokenManager
     */
    private TokenManager $tokenManager;

    /**
     * @var SessionInterface
     */
    private SessionInterface $session;

    /**
     * @param EmailManager        $emailManager
     * @param SecurityCodeManager $codeManager
     * @param UserSecurityManager $userManager
     * @param TokenManager        $tokenManager
     * @param SessionInterface    $session
     */
    public function __construct(
        EmailManager $emailManager,
        SecurityCodeManager $codeManager,
        UserSecurityManager $userManager,
        TokenManager $tokenManager,
        SessionInterface $session
    ) {
        $this->emailManager = $emailManager;
        $this->codeManager  = $codeManager;
        $this->userManager  = $userManager;
        $this->tokenManager = $tokenManager;
        $this->session      = $session;
    }

    /**
     * @param User $user
     *
     * @return bool|int
     */
    public function needSecurityCode(User $user)
    {
        if ($user->getLastSecurityCode() === null) {
            return true;
        }

        $now = new DateTime('NOW');

        try {
            $lastCodeDate = new DateTime($user->getLastSecurityCode());
        } catch (Exception $exception) {
            return (int)$exception->getCode();
        }

        $newCodeDate = $lastCodeDate->modify('+15 day');

        if ($now >= $newCodeDate) {
            return true;
        }
        return false;
    }

    /**
     * @param string $email
     */
    public function dispatchSecurityCode(string $email): void
    {
        $code    = $this->codeManager->generateCode();
        $this->emailManager->send(
            $email,
            'Authentication code',
            'security/authentication/_email_security_code.html.twig',
            ['code' => $code]
        );
        $this->setSessionHash($this->getHashSecurityCode($code));
    }

    /**
     * @param int $code
     *
     * @return bool
     */
    public function isCodeValid(int $code): bool
    {
        return $this->tokenManager->isTokenValid(
            $this->getSecurityHashKeyword($code),
            $this->getSessionHash()
        );
    }

    /**
     * @param string $email
     *
     * @return string[]
     */
    public function getMessage(string $email): array
    {
        return [
            'type' => 'info',
            'message' => 'A security code have been sent to ' . $email . '. Please enter this code here.'
        ];
    }

    /**
     * @param string $email
     *
     * @return string[]
     */
    public function getNewCodeMessage(string $email): array
    {
        return [
            'type' => 'info',
            'message' => 'A new code have been sent to ' . $email . '. Please enter this code here.'
        ];
    }

    /**
     * @param string $email
     *
     * @return string[]
     */
    public function getInvalidMessage(string $email): array
    {
        return [
            'type' => 'danger',
            'message' =>
                'invalid security code. A new code have been sent to ' . $email . '. Please enter this code here.'
        ];
    }

    /**
     * @param int $code
     *
     * @return string|null
     */
    private function getHashSecurityCode(int $code): ?string
    {
        return $this->tokenManager->getToken($this->getSecurityHashKeyword($code));
    }

    /**
     * @param int $code
     *
     * @return string
     */
    private function getSecurityHashKeyword(int $code): string
    {
        return self::HASH_KEYWORD . $code;
    }

    /**
     * @param string $hash
     */
    private function setSessionHash(string $hash): void
    {
        $this->session->add(self::SESSION_CODE_KEY, $hash);
    }

    /**
     * @return string|null
     */
    private function getSessionHash(): ?string
    {
        return $this->session->get(self::SESSION_CODE_KEY);
    }

    public function unsetSessionHash(): void
    {
        $this->session->remove(self::SESSION_CODE_KEY);
    }
}
