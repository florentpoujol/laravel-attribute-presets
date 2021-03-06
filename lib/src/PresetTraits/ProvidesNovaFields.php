<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\PresetTraits;

use FlorentPoujol\LaravelAttributePresets\Definitions\NovaField;
use Laravel\Nova\Fields\Field;

trait ProvidesNovaFields
{
    /** @var null|\Laravel\Nova\Fields\Field */
    protected $novaField;

    /**
     * @return null|\Laravel\Nova\Fields\Field
     */
    public function getNovaField(): ?Field
    {
        if ($this->novaField === null && $this->novaFieldDefinitions !== null) {
            $this->novaField = $this->novaFieldDefinitions
                ->attribute($this->getName())
                ->getInstance();
        }

        return $this->novaField;
    }

    public function hasNovaField(): bool
    {
        return
            $this->novaField !== null ||
            (
                $this->novaFieldDefinitions !== null &&
                $this->novaFieldDefinitions->has('type')
            );
    }

    // --------------------------------------------------

    /**
     * @param null|\Laravel\Nova\Fields\Field $field
     */
    public function setNovaField($field): self
    {
        $this->novaField = $field;

        return $this;
    }

    /** @var \FlorentPoujol\LaravelAttributePresets\Definitions\NovaField */
    protected $novaFieldDefinitions;

    /**
     * @return \FlorentPoujol\LaravelAttributePresets\Definitions\NovaField
     */
    public function getNovaFieldDefinitions(): NovaField
    {
        if ($this->novaFieldDefinitions === null) {
            $this->novaFieldDefinitions = new NovaField();
        }

        return $this->novaFieldDefinitions;
    }

    /**
     * @param \FlorentPoujol\LaravelAttributePresets\Definitions\NovaField $definitions
     */
    public function setNovaFieldDefinitions(NovaField $definitions): self
    {
        $this->novaFieldDefinitions = $definitions;

        return $this;
    }

    /**
     * Catch the call for the 'nova' key when the base preset itself is filled
     *
     * @param array|callable $attributesOrCallback Will fill or tap into the underlying definition instance
     *
     * @return static
     */
    public function novaField($attributesOrCallback)
    {
        $method = \is_callable($attributesOrCallback) ? 'tap' : 'fill';

        $this->getNovaFieldDefinitions()->$method($attributesOrCallback);

        return $this;
    }
}
