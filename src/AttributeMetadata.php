<?php

namespace FlorentPoujol\LaravelModelMetadata;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Laravel\Nova\Fields\Field;

class AttributeMetadata
{
    /** @var array<string, mixed> */
    protected static $makeDefinitions = [];

    public static function make(array $definitions): string
    {
        static::$makeDefinitions = [];

        return static::class;
    }

    public function setupFromMakeDefinitions(): void
    {
        foreach (self::$makeDefinitions as $methodName => $arguments) {
            if (is_int($methodName)) {
                $methodName = $arguments;
                $arguments = [];
            }

            if ($arguments === null) {
                $arguments = [];
            } elseif (!is_array($arguments)) {
                $arguments = [$arguments];
            }

            $this->$methodName(...$arguments);
        }
    }

    public function __construct()
    {
        //
    }


    // --------------------------------------------------
    // Database column definitions

    /** @var array<string|int, mixed> */
    protected $columnDefinitions = [];

    /**
     * @param string $type Must match one of the public methods of the Blueprint class
     * @param null|mixed $value one or several (as array) arguments for the type's method
     */
    public function setColumnType(string $type, $value = null)
    {
        $this->columnDefinitions['type'] = ['method' => $type, 'args' => $value];

        return $this;
    }

    public function getColumnType(): ?string
    {
        return $this->columnDefinitions['type']['method'] ?? null;
    }

    /**
     * @param string $key
     * @param null|mixed $value
     *
     * @return $this
     */
    public function addColumnDefinition($key, $value = null): self
    {
        $this->columnDefinitions[$key] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeColumnDefinition(string $key): self
    {
        unset($this->columnDefinitions[$key]);

        return $this;
    }

    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     *
     * @return null|\Illuminate\Database\Schema\ColumnDefinition
     */
    public function addColumnToTable(Blueprint $table): ?ColumnDefinition
    {
        if (empty($this->columnDefinition)) {
            return null;
        }

        $type = $this->columnDefinitions['type'];
        $arguments = $type['args'] ?? [];
        if (!is_array($arguments)) {
            $arguments = [$arguments];
        }

        /** @var \Illuminate\Database\Schema\ColumnDefinition $columnDefinition */
        $columnDefinition = $table->$type['method'](...$arguments);

        foreach ($this->columnDefinitions as $methodName => $arguments) {
            if ($methodName === 'type') {
                continue;
            }

            if (is_int($methodName)) {
                $methodName = $arguments;
                $arguments = [];
            }

            if ($arguments === null) {
                $arguments = [];
            } elseif (!is_array($arguments)) {
                $arguments = [$arguments];
            }

            $columnDefinition->$methodName(...$arguments);
        }

        return $columnDefinition;
    }

    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     *
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function updateColumnToTable(Blueprint $table): ColumnDefinition
    {
        $columnDefinition = $this->addColumnToTable($table);

        return $columnDefinition->change();
    }

    public function hasColumnInDB(): bool
    {
        return empty($this->columnDefinitions);
    }

    // --------------------------------------------------
    // Validation

    /** @var array<string, mixed> */
    protected $validationRules = [];

    /**
     * @return array<string|object>
     */
    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    /**
     * @param array<string|object> $rules
     *
     * @return $this
     */
    public function setValidationRules(array $rules): self
    {
        $this->validationRules = $rules;

        return $this;
    }

    /**
     * @param string|object $rule
     * @param mixed $value
     *
     * @return $this
     */
    public function setValidationRule($rule, $value = null): self
    {
        if ($value === null && is_string($rule) && strpos($rule, ':') !== false) {
            [$rule, $value] = explode(':', $rule, 2);
        }

        $this->validationRules[$rule] = $value;

        return $this;
    }

    /**
     * @param string|object $rule
     *
     * @return $this
     */
    public function removeValidationRule($rule): self
    {
        unset($this->validationRules[$rule]);

        return $this;
    }

    protected $validationMessage = '';

    public function getValidationMessage(): string
    {
        return $this->validationMessage ?: '';
    }

    /**
     * @return $this
     */
    public function setValidationMessage(string $message): self
    {
        $this->validationMessage = $message;

        return $this;
    }

    // --------------------------------------------------
    // Nova Fields

    /** @var array<string, null|\Laravel\Nova\Fields\Field> */
    protected $novaFields = [
        'index' => null,
        'details' => null,
        'create' => null,
        'update' => null,
    ];

    /** @var string */
    protected $novaFieldFqcn;

    /** @var array<string, array>  */
    protected $novaFieldDefinitions = ['sortable'];

    /**
     * @param string $typeOrFqcn
     *
     * @return $this
     */
    public function setNovaFieldType(string $typeOrFqcn): self
    {
        $prefix = '\\Laravel\\Nova\\Fields\\';

        $typeOrFqcn = ucfirst($typeOrFqcn);
        switch ($typeOrFqcn) {
            case 'Id':
                $typeOrFqcn = $prefix . 'ID';
                break;
            case 'String':
                $typeOrFqcn = $prefix . 'Text';
                break;
            case 'Text':
                $typeOrFqcn = $prefix . 'Textarea';
                break;
            case 'Json':
                $typeOrFqcn = $prefix . 'Code';
                break;
            case 'Datetime':
            case 'Timestamp':
                $typeOrFqcn = $prefix . 'DateTime';
                break;
        }

        $this->novaFieldFqcn = $typeOrFqcn;

        return $this;
    }

    /**
     * @param null|mixed $value
     *
     * @return $this
     */
    public function setNovaFieldDefinition(string $key, $value = null): self
    {
        $this->novaFieldDefinitions[$key] = $value;

        return $this;
    }

    /**
     * @return $this;
     */
    public function removeNovaFieldDefinition(string $key): self
    {
        unset($this->novaFieldDefinitions[$key]);

        return $this;
    }

    /**
     * @param null|string $page 'index', 'details', 'create', 'update'
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function getNovaFields(string $page = null): array
    {
        return
            $this->novaFields[$page ?: 'index'] ??
            $this->novaFields['index'] ?? null;
    }

    /**
     * @param mixed ...$args
     *
     * @return \Laravel\Nova\Fields\Field
     */
    public function setupNovaField(...$args): Field
    {

    }

    /**
     * @param null|\Laravel\Nova\Fields\Field $field
     * @param null|string $page 'index', 'details', 'create', 'update'
     *
     * @return $this
     */
    public function setNovaField($field, string $page = null): self
    {
        if ($page !== null) {
            $this->novaFields[$page] = $field;

            return $this;
        }

        if ($field === null) {
            $this->novaFields = [
                'index' => null,
                'details' => null,
                'create' => null,
                'update' => null,
            ];

            return $this;
        }

        // $field is an instance of Field and $page is null
        if ($field->showOnIndex) {
            $this->novaFields['index'] = $field;
        }
        if ($field->showOnDetail) {
            $this->novaFields['details'] = $field;
        }
        if ($field->showOnCreation) {
            $this->novaFields['create'] = $field;
        }
        if ($field->showOnUpdate) {
            $this->novaFields['update'] = $field;
        }

        return $this;
    }

    // --------------------------------------------------
    // cast and mutators

    /** @var null|string|object */
    protected $cast;

    /**
     * @param null|string|object $cast
     * @param string $value For casts that value values, like decimal or datetime
     *
     * @return $this
     */
    public function setCast($cast, string $value = null): self
    {
        if ($value !== null) {
            $cast .= ":$value";
        }

        $this->cast = $cast;

        return $this;
    }

    public function hasCast(): bool
    {
        return $this->cast !== null;
    }

    /**
     * @return null|object|string
     */
    public function getCast()
    {
        return $this->cast;
    }

    /** @var null|string */
    protected $castTarget;

    public function setCastTarget(string $castTarget)
    {
        $this->castTarget = $castTarget;

        return $this;
    }

    public function hasCastTarget(): bool
    {
        return $this->castTarget !== null;
    }

    public function getCastTarget(): ?string
    {
        return $this->castTarget;
    }

    protected $hasSetter = false;

    public function markHasSetter(bool $hasSetter = true): self
    {
        $this->hasSetter = $hasSetter;

        return $this;
    }

    public function hasSetter(): bool
    {
        return $this->hasSetter;
    }

    protected $hasGetter = false;

    public function markHasGetter(bool $hasGetter = true): self
    {
        $this->hasGetter = $hasGetter;

        return $this;
    }

    public function hasGetter(): bool
    {
        return $this->hasGetter;
    }

    // --------------------------------------------------
    // Relation

    /** @var null|string The name of the relation, on the base Eloquent model that return the relation instance */
    protected $relationMethod;

    /** @var array<string> The arguments for the relation method factory */
    protected $relationParams = [];

    /**
     * @param null|string $method
     * @param array<string> $params
     *
     * @return $this
     */
    public function setRelation(string $method = null, array $params = []): self
    {
        $this->relationMethod = $method;
        $this->relationParams = $params;

        $this
            ->setNovaFieldType($method)
            ->setNovaFieldDefinition('searchable')
            ->removeNovaFieldDefinition('sortable');

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getRelation(): array
    {
        return [$this->relationMethod, $this->relationParams];
    }

    public function isRelation(): bool
    {
        return $this->relationMethod !== null;
    }

    // --------------------------------------------------
    // Hidden

    protected $isHidden = false;

    /**
     * @return $this
     */
    public function markHidden(bool $isHidden = true): self
    {
        $this->isHidden = $isHidden;

        if ($isHidden) {
            $this
                ->setNovaFieldDefinition('hideFromIndex')
                ->setNovaFieldDefinition('hideFromDetails');
        }

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->isHidden;
    }

    // --------------------------------------------------
    // Guarded

    protected $isGuarded = false;

    /**
     * @return $this
     */
    public function markGuarded(bool $isGuarded = true): self
    {
        $this->isGuarded = $isGuarded;

        return $this;
    }

    public function isGuarded(): bool
    {
        return $this->isGuarded;
    }

    // --------------------------------------------------
    // Fillable

    protected $isFillable = true;

    /**
     * @return $this
     */
    public function markFillable(bool $isFillable = true): self
    {
        $this->isFillable = $isFillable;

        return $this;
    }

    public function isFillable(): bool
    {
        return $this->isFillable;
    }

    // --------------------------------------------------
    // Date

    protected $isDate = false;

    /**
     * @return $this
     */
    public function markDate(bool $isDate = true): self
    {
        $this->isDate = $isDate;

        return $this;
    }

    public function isDate(): bool
    {
        return $this->isDate;
    }

    // --------------------------------------------------
    // Nullable

    protected $isNullable = false;

    /**
     * @return $this
     */
    public function markNullable(bool $isNullable = true, bool $affectDbColumn = false): self
    {
        $this->isNullable = $isNullable;

        if ($isNullable) {
            $this
                ->addColumnDefinition('nullable')
                ->setValidationRule('nullable')
                ->setNovaFieldDefinition('nullable');
        } else {
            $this
                ->removeColumnDefinition('nullable')
                ->removeValidationRule('nullable')
                ->removeNovaFieldDefinition('nullable');
        }

        return $this;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    // --------------------------------------------------
    // Required

    protected $isRequired = false;

    /**
     * @return $this
     */
    public function markRequired(bool $isRequired = true): self
    {
        $this->isRequired = $isRequired;

        if ($isRequired) {
            $this
                ->setValidationRule('required')
                ->setNovaFieldDefinition('required');
        } else {
            $this
                ->removeValidationRule('required')
                ->removeNovaFieldDefinition('required');
        }

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    // --------------------------------------------------
    // Unsigned

    protected $isUnsigned = false;

    /**
     * @return $this
     */
    public function markUnsigned(bool $isUnsigned = true): self
    {
        $this->isUnsigned = $isUnsigned;

        $this
            ->addColumnDefinition('unsigned')
            ->setValidationRule('min', 0);

        return $this;
    }

    public function isUnsigned(): bool
    {
        return $this->isUnsigned;
    }

    // --------------------------------------------------
    // Default value

    /** @var null|mixed */
    protected $defaultValue;

    /**
     * @param null|mixed $value
     *
     * @return $this
     */
    public function setDefaultValue($value, bool $affectDbColumn = false): self
    {
        $this->defaultValue = $value;

        if ($affectDbColumn) {
            $this->addColumnDefinition('default', $value);
        }

        return $this;
    }

    /**
     * @return null|mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function hasDefaultValue(): bool
    {
        return $this->defaultValue !== null;
    }

    // --------------------------------------------------
    // Primary key

    protected $isPrimaryKey = false;
    protected $primaryKeyType = 'int';
    protected $isIncrementingPrimaryKey = true;

    /**
     * @return $this
     */
    public function markPrimaryKey(bool $isPrimaryKey = true, string $keyType = 'int', bool $isIncrementing = true): self
    {
        $this->isPrimaryKey = $isPrimaryKey;
        $this->primaryKeyType = $keyType;
        $this->isIncrementingPrimaryKey = $this->primaryKeyType === 'int' ? $isIncrementing : false;

        if ($this->isPrimaryKey) {
            $this
                ->addColumnDefinition('primary')
                ->setNovaFieldType('id');
        }

        if ($this->isIncrementingPrimaryKey) {
            $this->addColumnDefinition('autoIncrement');
        }

        return $this;
    }

    public function isPrimaryKey(): bool
    {
        return $this->isPrimaryKey;
    }

    public function getPrimaryKeyType(): string
    {
        return $this->primaryKeyType;
    }

    public function isIncrementingPrimaryKey(): bool
    {
        return $this->isIncrementingPrimaryKey;
    }

    // TODO set min, set max which sets it on the validation rule and nova field
}
