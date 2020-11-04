<?php

namespace App\Service\Form\DataFilter;

class EmailDataFilter implements DataFilterInterface
{
    /**
     * @param $data
     *
     * @return mixed
     */
    public function filter($data)
    {
        return filter_var(trim($data), FILTER_SANITIZE_EMAIL);
    }
}
