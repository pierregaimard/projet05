<?php

namespace App\Service\Form\DataChecker;

class CommentDataChecker implements DataCheckerInterface
{
    /**
     * @param string $data
     *
     * @return bool
     */
    public function check(string $data): bool
    {
        return preg_match('#^[ a-zA-Z0-9âäçéèêëîïôùûüÿ.!?,\-]+$#', $data) !== 0;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return 'Invalid character is used';
    }
}
