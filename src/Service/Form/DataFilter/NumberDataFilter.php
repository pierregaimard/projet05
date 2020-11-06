<?php

namespace App\Service\Form\DataFilter;

use App\Service\Form\DataChecker\DataCheckerInterface;

class NumberDataFilter implements DataFilterInterface
{
    /**
     * @param $data
     *
     * @return int|mixed
     */
    public function filter($data)
    {
        return (int)$data;
    }
}
