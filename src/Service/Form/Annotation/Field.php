<?php

namespace App\Service\Form\Annotation;

use Climb\Annotation\AnnotationInterface;

class Field implements AnnotationInterface
{
    public const TAG = 'Field';

    /**
     * @var string
     */
    private string $type;

    /**
     * @var bool
     */
    private bool $nullable;

    /**
     * @var string|null
     */
    private ?string $message;

    public function __construct()
    {
        $this->nullable = true;
        $this->message  = null;
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
}
