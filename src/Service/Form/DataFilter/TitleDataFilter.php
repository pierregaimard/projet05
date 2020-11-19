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
        return htmlspecialchars(trim($data));
    }
}
