<?php

namespace App\Model\Entity;

/**
 * @Table(name="blog_post_comment_status")
 */
class BlogPostCommentStatus
{
    public const STATUS_VALIDATION = 'VALIDATION';
    public const STATUS_APPROVED   = 'APPROVED';

    /**
     * @var int
     *
     * @Column(name="id")
     */
    private int $key;

    /**
     * @var string
     *
     * @Column(name="status")
     */
    private string $status;

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
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
