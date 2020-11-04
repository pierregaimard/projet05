<?php

namespace App\Service\Form\DataChecker;

use App\Service\Form\Annotation\Field;
use Climb\Bag\Bag;

class DataChecker
{
    private const TYPE_NULL    = 'null';
    public const TYPE_EMAIL    = 'email';
    public const TYPE_PASSWORD = 'password';
    public const TYPE_NAME     = 'name';
    public const TYPE_TITLE    = 'title';
    public const TYPE_COMMENT  = 'comment';

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
     * @return DataCheckerInterface
     */
    private function getDataChecker(string $type): DataCheckerInterface
    {
        if (!$this->container->has($type)) {
            $this->setDataChecker($type);
        }

        return $this->container->get($type);
    }

    /**
     * @param string $type
     */
    private function setDataChecker(string $type): void
    {
        $dataChecker = 'App\\Service\\Form\\DataChecker\\' . ucfirst($type) . 'DataChecker';
        $this->container->add($type, new $dataChecker());
    }

    /**
     * @param string      $type
     * @param string      $data
     * @param bool        $nullable
     * @param string|null $message
     *
     * @return string|true
     */
    public function checkField(string $type, string $data, bool $nullable, string $message = null)
    {
        if ($nullable === false) {
            $nullChecker = $this->getDataChecker(self::TYPE_NULL);
            if ($nullChecker->check($data) !== true) {
                return $nullChecker->getErrorMessage();
            }
        }

        $dataChecker = $this->getDataChecker($type);

        if ($dataChecker->check($data) === true) {
            return true;
        }

        return ($message !== null) ? $message : $dataChecker->getErrorMessage();
    }

    /**
     * @param Field[] $annotations
     * @param array   $data
     *
     * @return array|bool
     */
    public function check(array $annotations, array $data)
    {
        $messages = [];

        foreach ($annotations as $attribute => $field) {
            $check = $this->checkField(
                $field->getType(),
                $data[$attribute],
                $field->isNullable(),
                $field->getMessage()
            );

            if ($check !== true) {
                $messages[$attribute] = $check;
            }
        }

        if (empty($messages)) {
            return true;
        }

        return $messages;
    }
}
