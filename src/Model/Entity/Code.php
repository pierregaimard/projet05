<?php

namespace App\Model\Entity;

class Code
{
    /**
     * @var int
     *
     * @Field(type="number", nullable=false, minLength=6, maxLength=6)
     */
    private int $code;

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }
}
