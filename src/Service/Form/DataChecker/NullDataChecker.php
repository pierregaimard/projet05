<?php

namespace App\Service\Form\DataChecker;

class NullDataChecker
{
    /**
     * @param string $data
     *
     * @return string|true
     */
    public function check(string $data)
    {
        if ($data !== '') {
            return true;
        }

        return 'This field is required';
    }
}
