<?php

namespace App\Service\Form\DataChecker;

class MaxLengthDataChecker
{
    /**
     * @param string   $data
     * @param int|null $length
     *
     * @return string|true
     */
    public function check(string $data, int $length = null)
    {
        if ($length === null || strlen($data) < $length) {
            return true;
        }

        return 'This field requires maximum ' . $length . ' characters long';
    }
}
