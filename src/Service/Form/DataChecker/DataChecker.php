<?php

namespace App\Service\Form\DataChecker;

use App\Service\Form\Annotation\Field;
use Climb\Bag\Bag;

class DataChecker
{
    /**
     * @var Bag
     */
    private Bag $container;

    private NullDataChecker $nullChecker;

    /**
     * @var MinLengthDataChecker
     */
    private MinLengthDataChecker $minLengthChecker;

    /**
     * @var MaxLengthDataChecker
     */
    private MaxLengthDataChecker $maxLengthChecker;

    public function __construct(
        NullDataChecker $nullChecker,
        MinLengthDataChecker $minLengthChecker,
        MaxLengthDataChecker $maxLengthChecker
    ) {
        $this->nullChecker      = $nullChecker;
        $this->minLengthChecker = $minLengthChecker;
        $this->maxLengthChecker = $maxLengthChecker;
        $this->container        = new Bag();
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
     * @param string $data
     * @param Field  $field
     *
     * @return string|true
     */
    public function checkField(string $data, Field $field)
    {
        // Nullable checker
        if (!$field->isNullable()) {
            $nullCheck = $this->nullChecker->check($data);
            if ($nullCheck !== true) {
                return ($field->hasNullMessage()) ? $field->getNullMessage() : $nullCheck;
            }
        }

        // Min length checker
        $minLengthCheck = $this->minLengthChecker->check($data, $field->getMinLength());
        if ($minLengthCheck !== true) {
            return $minLengthCheck;
        }

        // Max length checker
        $maxLengthCheck = $this->maxLengthChecker->check($data, $field->getMaxLength());
        if ($maxLengthCheck !== true) {
            return $maxLengthCheck;
        }

        $dataChecker = $this->getDataChecker($field->getType());

        if ($dataChecker->check($data) === true) {
            return true;
        }

        return ($field->hasMessage()) ? $field->getMessage() : $dataChecker->getErrorMessage();
    }

    /**
     * @param Field[] $annotations
     * @param array   $data
     *
     * @return array|true
     */
    public function check(array $annotations, array $data)
    {
        $messages = [];

        foreach ($data as $attribute => $fieldData) {
            if (!array_key_exists($attribute, $annotations)) {
                continue;
            }

            $check = $this->checkField($fieldData, $annotations[$attribute]);

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
