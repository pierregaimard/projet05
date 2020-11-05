<?php

namespace App\Service\Email;

use Climb\Exception\AppException;
use Climb\Templating\Twig\TemplatingManager;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Swift_Mailer;
use Swift_Message;

class EmailManager
{
    /**
     * @var Swift_Mailer
     */
    private Swift_Mailer $mailer;

    /**
     * @var Environment
     */
    private Environment $templating;

    /**
     * @var string
     */
    private string $email;

    /**
     * EmailManager constructor.
     *
     * @param MailerManager     $manager
     * @param TemplatingManager $templating
     * @param string            $email
     *
     * @throws AppException
     */
    public function __construct(MailerManager $manager, TemplatingManager $templating, string $email)
    {
        $this->mailer     = $manager->getMailer();
        $this->templating = $templating->getEnvironment([]);
        $this->email      = $email;
    }

    /**
     * @param string      $email
     * @param string      $subject
     * @param string      $content
     *
     * @return int
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function send(string $email, string $subject, string $content): int
    {
        $message = (new Swift_Message())
            ->setSubject($subject)
            ->setFrom($this->email)
            ->setTo($email)
            ->setContentType('text/html')
            ->setBody(
                $this->templating->render('email/blog_email_template.html.twig', ['content' => $content])
            );

        return $this->mailer->send($message);
    }
}
