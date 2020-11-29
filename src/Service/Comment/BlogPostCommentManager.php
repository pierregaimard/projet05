<?php

namespace App\Service\Comment;

use Climb\Exception\AppException;
use App\Model\Entity\BlogPostComment;
use App\Model\Entity\BlogPostCommentStatus;
use App\Service\Email\EmailManager;
use Climb\Orm\Orm;

class BlogPostCommentManager
{
    /**
     * @var EmailManager
     */
    private EmailManager $emailManager;

    /**
     * @var Orm
     */
    private Orm $orm;

    public function __construct(EmailManager $emailManager, Orm $orm)
    {
        $this->emailManager = $emailManager;
        $this->orm          = $orm;
    }

    /**
     * @param int $commentId
     *
     * @throws AppException
     */
    public function validate(int $commentId): void
    {
        $manager = $this->orm->getManager('App');
        $comment = $manager->getRepository(BlogPostComment::class)->findOne($commentId);
        $status  = $manager->getRepository(BlogPostCommentStatus::class)->findOne(
            BlogPostCommentStatus::STATUS_APPROVED
        );

        $comment->setStatus($status);
        $manager->updateOne($comment);

        // Email notification
        $this->emailManager->send(
            $comment->getUser()->getEmail(),
            'Comment approved',
            'blog/comment/_comment_action_email.html.twig',
            [
                'title' => $comment->getBlogPost()->getTitle(),
                'comment' => $comment,
                'action' => 'approve'
            ]
        );
    }

    /**
     * @param int $commentId
     *
     * @throws AppException
     */
    public function reject(int $commentId): void
    {
        $manager = $this->orm->getManager('App');
        $comment = $manager->getRepository(BlogPostComment::class)->findOne($commentId);

        $manager->deleteOne($comment);

        // Email notification
        $this->emailManager->send(
            $comment->getUser()->getEmail(),
            'Comment rejected',
            'blog/comment/_comment_action_email.html.twig',
            [
                'title' => $comment->getBlogPost()->getTitle(),
                'comment' => $comment,
                'action' => 'reject'
            ]
        );
    }
}
