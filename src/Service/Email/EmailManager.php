<?php

namespace App\Service\Email;

use App\Service\Templating\TemplatingManager;
use Swift_Mailer;
use Swift_Message;

class EmailManager
{
    /**
     * @var Swift_Mailer
     */
    private Swift_Mailer $mailer;

    /**
     * @var TemplatingManager
     */
    private TemplatingManager $templating;

    /**
     * @var string
     */
    private string $email;

    /**
     * @param MailerManager     $manager
     * @param TemplatingManager $templating
     * @param string            $email
     */
    public function __construct(MailerManager $manager, TemplatingManager $templating, string $email)
    {
        $this->mailer     = $manager->getMailer();
        $this->templating = $templating;
        $this->email      = $email;
    }

    /**
     * @param string      $email
     * @param string      $subject
     * @param string      $content
     *
     * @return int
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
