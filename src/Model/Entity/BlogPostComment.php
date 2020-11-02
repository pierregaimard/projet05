<?php

namespace App\Model\Entity;

/**
 * @Table(name="blog_post_comment")
 */
class BlogPostComment
{
    /**
     * @var int
     *
     * @Column(name="id")
     */
    private int $key;

    /**
     * @var string
     *
     * @Column(name="comment")
     */
    private string $comment;

    /**
     * @var string
     *
     * @Column(name="time")
     */
    private string $time;

    /**
     * @var BlogPost
     *
     * @Relation(type="entity", entity="App\Model\Entity\BlogPost", invertedBy="comments")
     */
    private BlogPost $blogPost;

    /**
     * @var User
     *
     * @Relation(type="entity", entity="App\Model\Entity\User")
     */
    private User $user;

    /**
     * @var BlogPostCommentStatus
     *
     * @Relation(type="entity", entity="App\Model\Entity\BlogPostCommentStatus")
     */
    private BlogPostCommentStatus $status;

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
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->time;
    }

    /**
     * @param string $time
     */
    public function setTime(string $time): void
    {
        $this->time = $time;
    }

    /**
     * @return BlogPost
     */
    public function getBlogPost(): BlogPost
    {
        return $this->blogPost;
    }

    /**
     * @param BlogPost $blogPost
     */
    public function setBlogPost(BlogPost $blogPost): void
    {
        $this->blogPost = $blogPost;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return BlogPostCommentStatus
     */
    public function getStatus(): BlogPostCommentStatus
    {
        return $this->status;
    }

    /**
     * @param BlogPostCommentStatus $status
     */
    public function setStatus(BlogPostCommentStatus $status): void
    {
        $this->status = $status;
    }
}
