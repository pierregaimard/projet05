<?php

namespace App\Service\Security;

use App\Model\Entity\User;
use App\Service\Email\EmailManager;
use App\Service\Templating\TemplatingManager;
use Climb\Http\Session\SessionInterface;
use Climb\Security\TokenManager;
use DateTime;
use Exception;

class UserAuthenticationCodeManager
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
     * @var TemplatingManager
     */
    private TemplatingManager $templating;

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
     * @param TemplatingManager   $templating
     * @param TokenManager        $tokenManager
     * @param SessionInterface    $session
     */
    public function __construct(
        EmailManager $emailManager,
        SecurityCodeManager $codeManager,
        UserSecurityManager $userManager,
        TemplatingManager $templating,
        TokenManager $tokenManager,
        SessionInterface $session
    ) {
        $this->emailManager = $emailManager;
        $this->codeManager  = $codeManager;
        $this->userManager  = $userManager;
        $this->templating   = $templating;
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
     * @param User $user
     */
    public function dispatchSecurityCode(User $user): void
    {
        $code    = $this->codeManager->generateCode();
        $message = $this->templating->render(
            'security/authentication/_email_security_code.html.twig',
            ['code' => $code]
        );

        $this->emailManager->send($user->getEmail(), 'Authentication code', $message);
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
     * @param User $user
     *
     * @return string[]
     */
    public function getMessage(User $user): array
    {
        return [
            'type' => 'info',
            'message' => 'A security code have been sent to ' . $user->getEmail() . '. Please enter this code here.'
        ];
    }

    /**
     * @return string[]
     */
    public function getInvalidMessage(): array
    {
        return [
            'type' => 'danger',
            'message' => 'invalid security code. Please try again.'
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
