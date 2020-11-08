<?php

namespace App\Service\Form\Annotation;

use Climb\Annotation\AnnotationInterface;

class Field implements AnnotationInterface
{
    public const TAG              = 'Field';
    public const TYPE_EMAIL       = 'email';
    public const TYPE_PASSWORD    = 'password';
    public const TYPE_NAME        = 'name';
    public const TYPE_TITLE       = 'title';
    public const TYPE_COMMENT     = 'comment';
    public const TYPE_NUMBER      = 'number';

    /**
     * @var string
     */
    private string $type;

    /**
     * @var bool
     */
    private bool $nullable;

    /**
     * @var int|null
     */
    private ?int $minLength;

    /**
     * @var int|null
     */
    private ?int $maxLength;

    /**
     * @var string|null
     */
    private ?string $message;

    /**
     * @var string|null
     */
    private ?string $nullMessage;

    public function __construct()
    {
        $this->nullable    = true;
        $this->minLength   = null;
        $this->maxLength   = null;
        $this->message     = null;
        $this->nullMessage = null;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @param bool $nullable
     */
    public function setNullable(bool $nullable): void
    {
        $this->nullable = $nullable;
    }

    /**
     * @return int|null
     */
    public function getMinLength(): ?int
    {
        return $this->minLength;
    }

    /**
     * @param int|null $minLength
     */
    public function setMinLength(?int $minLength): void
    {
        $this->minLength = $minLength;
    }

    /**
     * @return int|null
     */
    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    /**
     * @param int|null $maxLength
     */
    public function setMaxLength(?int $maxLength): void
    {
        $this->maxLength = $maxLength;
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string|null $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return bool
     */
    public function hasMessage(): bool
    {
        return $this->message !== null;
    }

    /**
     * @return string|null
     */
    public function getNullMessage(): ?string
    {
        return $this->nullMessage;
    }

    /**
     * @param string|null $nullMessage
     */
    public function setNullMessage(?string $nullMessage): void
    {
        $this->nullMessage = $nullMessage;
    }

    /**
     * @return bool
     */
    public function hasNullMessage(): bool
    {
        return $this->nullMessage !== null;
    }
}
