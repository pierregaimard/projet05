<?php

namespace App\Service\Form;

use App\Service\Form\Annotation\Field;
use App\Service\Form\DataChecker\DataChecker;
use App\Service\Form\DataFilter\DataFilter;
use Climb\Annotation\ReaderManagerInterface;

class EntityFormDataManager
{
    /**
     * @var ReaderManagerInterface
     */
    private ReaderManagerInterface $readerManager;

    /**
     * @var DataChecker
     */
    private DataChecker $dataChecker;

    /**
     * @var DataFilter
     */
    private DataFilter $dataFilter;

    public function __construct(
        ReaderManagerInterface $readerManager,
        DataChecker $dataChecker,
        DataFilter $dataFilter
    ) {
        $this->readerManager = $readerManager;
        $this->dataChecker   = $dataChecker;
        $this->dataFilter    = $dataFilter;
    }

    /**
     * @param string $entity
     * @param array  $data
     *
     * @return array|bool
     */
    public function checkFormData(string $entity, array $data)
    {
        $annotations = $this->getFieldAnnotations($entity);

        if (empty($annotations)) {
            return true;
        }

        return $this->dataChecker->check($annotations, $data);
    }

    /**
     * @param string      $type
     * @param             $data
     * @param bool        $nullable
     * @param string|null $message
     *
     * @return string|true
     */
    public function checkFormField(string $type, $data, bool $nullable = false, string $message = null)
    {
        return $this->dataChecker->checkField($type, $data, $nullable, $message);
    }

    /**
     * @param object $entity
     * @param array  $data
     */
    public function setEntityFormData(object $entity, array $data): void
    {
        $annotations = $this->getFieldAnnotations(get_class($entity));

        if (empty($data)) {
            return;
        }

        foreach ($data as $attribute => $formData) {
            $setter = 'set' . ucfirst($attribute);

            if ($formData === '') {
                $formData = null;
            }

            if (array_key_exists($attribute, $annotations)) {
                $formData = $this->dataFilter->filter($annotations[$attribute]->getType(), $formData);
            }

            $entity->$setter($formData);
        }
    }

    /**
     * @param string $type
     * @param string $data
     *
     * @return mixed
     */
    public function filterField(string $type, string $data)
    {
        return $this->dataFilter->filter($type, $data);
    }

    /**
     * @param string $entity
     *
     * @return Field[]
     */
    private function getFieldAnnotations(string $entity): array
    {
        $reader = $this->readerManager->getReader($entity);
        return $reader->getPropertiesAnnotation(Field::TAG, true);
    }
}
