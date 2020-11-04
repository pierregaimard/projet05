<?php

namespace App\Service\Form\DataFilter;

class TitleDataFilter implements DataFilterInterface
{
    /**
     * @param $data
     *
     * @return mixed
     */
    public function filter($data)
    {
        return filter_var(trim($data), FILTER_SANITIZE_STRING);
    }
}
