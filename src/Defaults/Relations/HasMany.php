<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata\Relations;

class HasMany extends Relation
{
    /**
     * @param string|array $relationParams The FQCN of the related model, or a array with all the arguments that you would pass to the model's belongsTo() method
     */
    public function __construct($relationParams)
    {
        parent::__construct('hasMany', $relationParams);

        $this->columnDefinitions = [];
    }
}