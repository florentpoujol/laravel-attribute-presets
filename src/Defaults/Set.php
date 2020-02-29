<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

class Set extends AttributeMetadata
{
    /**
     * @param array<string> $allowedValues
     */
    public function __construct(array $allowedValues = null)
    {
        parent::__construct();

        if (!empty($allowedValues)) {
            $this->setAllowedValues($allowedValues);
        }

        $this
            ->addColumnDefinition('set', $this->allowedValues)
            ->setValidationRule('in', $this->allowedValues);
        // TODO nova field multiselect
    }

    /** @var array<string>  */
    protected $allowedValues = [];

    /**
     * @param array<string> $values
     *
     * @return $this
     */
    public function setAllowedValues(array $values): self
    {
        $this->allowedValues = $values;

        $this->setValidationRule('in', $values);

        return $this;
    }
}