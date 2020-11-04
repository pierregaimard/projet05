<?php

namespace App\Service\Form\DataFilter;

interface DataFilterInterface
{
    /**
     * @param $data
     *
     * @return mixed
     */
    public function filter($data);
}
