<?php

namespace App\Service\Form\DataFilter;

class PostDataFilter implements DataFilterInterface
{
    /**
     * @param $data
     *
     * @return mixed|void
     */
    public function filter($data)
    {
        return htmlspecialchars($data);
    }
}
