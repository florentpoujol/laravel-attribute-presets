<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

use Illuminate\Support\Collection;

class ModelMetadata
{
    /** @var string */
    protected $modelFqcn;

    public function __construct(string $modelFqcn, array $rawAttributesMetadata)
    {
        $this->modelFqcn = $modelFqcn;
        $this->rawAttrsMetadata = $rawAttributesMetadata;

        $this->attrNames = array_keys($this->rawAttrsMetadata);
        $this->attrCollection = new Collection();
    }

    /** @var array<string, string|callable|\FlorentPoujol\LaravelModelMetadata\AttributeMetadata> */
    protected $rawAttrsMetadata;

    public function hasAttributeMetadata(string $name): bool
    {
        return isset($this->rawAttrsMetadata[$name]);
    }

    /** @var string[] List of the model's attributes (that have metadata) */
    protected $attrNames;

    /**
     * @return array<string>
     */
    public function getAttributeNames(): array
    {
        return $this->attrNames;
    }

    /** @var \Illuminate\Support\Collection&array<string, \FlorentPoujol\LaravelModelMetadata\AttributeMetadata> */
    protected $attrCollection;

    /**
     * @return \FlorentPoujol\LaravelModelMetadata\AttributeMetadata
     *
     * @throws \LogicException When the attribute has no metadata
     */
    public function getAttributeMetadata(string $name): AttributeMetadata
    {
        if ($this->attrCollection->has($name)) {
            return $this->attrCollection->get($name);
        }

        if (! in_array($name, $this->attrNames)) {
            throw new \LogicException(
                "Attribute '{$this->modelFqcn}->$name' doesn't have metadata"
            );
        }

        $object = $this->rawAttrsMetadata[$name];

        if (is_callable($object)) {
            $object = $object();
        } elseif (is_string($object)) {
            $object = new $object();
        }

        $this->attrCollection->put($name, $object);

        return $object;
    }

    /**
     * Return all or a subset of the attributes metadata collection
     *
     * @param null|array<string> $names
     *
     * @return \Illuminate\Support\Collection&array<string, \FlorentPoujol\LaravelModelMetadata\AttributeMetadata>
     */
    public function getAttrCollection(array $names = null)
    {
        $names = empty($names) ? $this->attrNames : $names;

        $collection = new Collection();
        foreach ($names as $name) {
            $collection->put($name, $this->getAttributeMetadata($name));
            // done like that instead of using the collection's only() method
            // so that metadata classes are created if they don't exists yet
        }

        return $collection;
    }

    /**
     * @param null|array<string> $attributes The optional list of attribute names to restrict the results to
     *
     * @return array<string, array<string|object>> Validation rules per attribute name
     */
    public function getValidationRules(array $attributes = null): array
    {
         return $this
            ->getAttrCollection($attributes)
            ->mapWithKeys(function (AttributeMetadata $meta, string $name) {
                return [$name => $meta->getValidationRules()];
            })
            ->toArray();
    }

    /**
     * @param null|array<string> $attributes The optional list of attribute names to restrict the results to
     *
     * @return array<string, string> Validation messages per attribute name
     */
    public function getValidationMessages(array $attributes = null): array
    {
        return $this
            ->getAttrCollection($attributes)
            ->mapWithKeys(function (AttributeMetadata $meta, string $name) {
                return [$name => $meta->getValidationMessage()];
            })
            ->toArray();
    }

    /**
     * @param null|array<string> $attributes The optional list of attribute names to restrict the results to
     *
     * @return array<string, string>
     */
    public function getNovaFields(array $attributes = null): array
    {
        return $this
            ->getAttrCollection($attributes)
            ->flatMap(function (AttributeMetadata $meta) {
                return $meta->getNovaFields();
            })
            ->toArray();
    }
}
