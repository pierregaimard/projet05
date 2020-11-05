<?php

namespace App\Service\Email;

use Swift_Mailer;
use Swift_SmtpTransport;

class MailerManager
{
    /**
     * @var string
     */
    private string $server;

    /**
     * @var int
     */
    private int $port;

    /**
     * @var string
     */
    private string $protocole;

    /**
     * @var string
     */
    private string $username;

    /**
     * @var string
     */
    private string $password;

    /**
     * @var Swift_Mailer
     */
    private Swift_Mailer $mailer;

    /**
     * @param string $server
     * @param string $port
     * @param string $protocole
     * @param string $username
     * @param string $password
     */
    public function __construct(
        string $server,
        string $port,
        string $protocole,
        string $username,
        string $password
    ) {
        $this->server    = $server;
        $this->port      = (int)$port;
        $this->protocole = $protocole;
        $this->username  = $username;
        $this->password  = $password;

        $this->setMailer();
    }

    /**
     * @return Swift_Mailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    private function setMailer(): void
    {
        $this->mailer = new Swift_Mailer($this->getTransport());
    }

    /**
     * @return Swift_SmtpTransport
     */
    private function getTransport(): Swift_SmtpTransport
    {
        return (new Swift_SmtpTransport($this->server, $this->port, $this->protocole))
            ->setUsername($this->username)
            ->setPassword($this->password);
    }
}
