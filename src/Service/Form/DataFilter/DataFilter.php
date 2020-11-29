<?php

namespace App\Service\Form\DataFilter;

use Climb\Bag\Bag;

class DataFilter
{
    public const TYPE_COMMENT  = 'comment';
    public const TYPE_PASSWORD = 'password';
    public const TYPE_NAME     = 'string';
    public const TYPE_EMAIL    = 'email';
    public const TYPE_TITLE    = 'title';

    /**
     * @var Bag
     */
    private Bag $container;

    public function __construct()
    {
        $this->container = new Bag();
    }

    /**
     * @param string $type
     *
     * @return DataFilterInterface
     */
    private function getDataFilter(string $type)
    {
        if (!$this->container->has($type)) {
            $this->setDataFilter($type);
        }

        return $this->container->get($type);
    }

    /**
     * @param string $type
     */
    private function setDataFilter(string $type): void
    {
        $dataFilter = 'App\\Service\\Form\\DataFilter\\' . ucfirst($type) . 'DataFilter';
        $this->container->add($type, new $dataFilter());
    }

    /**
     * @param string $type
     * @param mixed  $data
     *
     * @return mixed
     */
    public function filter(string $type, $data)
    {
        if ($data === null) {
            return null;
        }

        $dataFilter = $this->getDataFilter($type);

        return $dataFilter->filter($data);
    }
}
