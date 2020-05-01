<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets;

/**
 * @method static getRawAttributePresets(): array Shall be implemented typically in the model class itself
 */
trait HasAttributePresets
{
    /** @var \FlorentPoujol\LaravelAttributePresets\Collection */
    protected static $attributePresetCollection;

    /**
     * @return \FlorentPoujol\LaravelAttributePresets\Collection
     */
    public static function getAttributePresetCollection(): Collection
    {
        if (static::$attributePresetCollection !== null) {
            return static::$attributePresetCollection;
        }

        $collectionFqcn = Collection::class;
        if (property_exists(static::class, 'attributePresetsCollectionFqcn')) {
            /** @noinspection PhpUndefinedFieldInspection */
            $collectionFqcn = static::$attributePresetsCollectionFqcn;
        }

        static::$attributePresetCollection = new $collectionFqcn(
            static::class, // model Fqcn
            static::getRawAttributePresets() // attribute preset definitions
        );

        return static::$attributePresetCollection;
    }

    public static function getAttributePreset(string $name): ?BasePreset
    {
        $modelMetadata = static::getAttributePresetCollection();

        return $modelMetadata->get($name);
    }
}
