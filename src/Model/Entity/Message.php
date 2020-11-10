<?php

namespace App\Model\Entity;

class Message
{
    /**
     * @var string
     *
     * @Field(type="name", nullable=false, minLength=3)
     */
    private string $firstName;

    /**
     * @var string
     *
     * @Field(type="name", nullable=false, minLength=3)
     */
    private string $lastName;

    /**
     * @var string
     *
     * @Field(type="email", nullable=false)
     */
    private string $email;

    /**
     * @var string
     *
     * @Field(type="comment", nullable=false)
     */
    private string $message;

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
