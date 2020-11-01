<?php

namespace App\Model\Entity;

/**
 * @Table(name="blog_post")
 */
class BlogPost
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
     * @Column(name="title")
     */
    private string $title;

    /**
     * @var string
     *
     * @Column(name="chapo")
     */
    private string $chapo;

    /**
     * @var string
     *
     * @Column(name="content")
     */
    private string $content;

    /**
     * @var string
     *
     * @Column(name="creation_time")
     */
    private string $creationTime;

    /**
     * @var string
     * @Column(name="last_update_time")
     */
    private string $lastUpdateTime;

    /**
     * @var User
     *
     * @Relation(type="entity", entity="App\Model\Entity\User")
     */
    private User $user;

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
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getChapo(): string
    {
        return $this->chapo;
    }

    /**
     * @param string $chapo
     */
    public function setChapo(string $chapo): void
    {
        $this->chapo = $chapo;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getCreationTime(): string
    {
        return $this->creationTime;
    }

    /**
     * @param string $creationTime
     */
    public function setCreationTime(string $creationTime): void
    {
        $this->creationTime = $creationTime;
    }

    /**
     * @return string
     */
    public function getLastUpdateTime(): string
    {
        return $this->lastUpdateTime;
    }

    /**
     * @param string $lastUpdateTime
     */
    public function setLastUpdateTime(string $lastUpdateTime): void
    {
        $this->lastUpdateTime = $lastUpdateTime;
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
}
