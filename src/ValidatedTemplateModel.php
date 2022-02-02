<?php

declare(strict_types=1);

namespace Craftzing\Laravel\NotificationChannels\Postmark;

use Craftzing\Laravel\NotificationChannels\Postmark\Resources\TemplateModel;
use Illuminate\Support\Arr;
use Iterator;
use Postmark\Models\DynamicResponseModel;

use function array_pad;
use function count;
use function gettype;
use function head;
use function is_array;

final class ValidatedTemplateModel
{
    /**
     * @readonly
     * @var array<mixed>
     */
    public array $missing = [];

    /**
     * @readonly
     * @var array<mixed>
     */
    public array $invalid = [];

    private function __construct(DynamicResponseModel $model, DynamicResponseModel $suggestedModel)
    {
        foreach ($suggestedModel as $key => $suggestedValue) {
            $suggestedValue = $this->resolveValue($suggestedValue);

            if ($this->isMarkedAsMissing($key, $model, $suggestedValue)) {
                continue;
            }

            $providedValue = $this->resolveValue($model[$key]);

            if ($this->isMarkedAsInvalid($key, $providedValue, $suggestedValue)) {
                continue;
            }

            $this->validateNestedAttributes($key, $providedValue, $suggestedValue);
        }
    }

    public static function validate(TemplateModel $model, DynamicResponseModel $suggestedModel): self
    {
        // Because of the intricacies of the DynamicResponseModel implementation, we should
        // ensure to compare the actual model to the suggested model after converting it
        // to a DynamicResponseModel. This way, both result sets work identically.
        return new self(new DynamicResponseModel($model->attributes()), $suggestedModel);
    }

    /**
     * @param string|int $key
     * @param string|array $suggestedValue
     */
    private function isMarkedAsMissing($key, DynamicResponseModel $model, $suggestedValue): bool
    {
        if (isset($model[$key])) {
            return false;
        }

        $this->missing[$key] = $suggestedValue;

        return true;
    }

    /**
     * @param string|int $key
     * @param string|array $providedValue
     * @param string|array $suggestedValue
     */
    private function isMarkedAsInvalid($key, $providedValue, $suggestedValue): bool
    {
        if (gettype($providedValue) !== gettype($suggestedValue)) {
            $this->invalid[$key] = $suggestedValue;

            return true;
        }

        // When the suggested value is not an array, there is nothing more to validate...
        if (! is_array($suggestedValue)) {
            return false;
        }

        // When the provided value is an empty array, we should not mark it as invalid is we should
        // rather mark it's nested attributes as missing for clearer error reporting...
        if ($providedValue === []) {
            return false;
        }

        // When the suggested value is an array, we must ensure that the provided and
        // suggested values are either both a list or both an associative array...
        if (Arr::isAssoc($providedValue) === Arr::isAssoc($suggestedValue)) {
            return false;
        }

        $this->invalid[$key] = $suggestedValue;

        return true;
    }

    /**
     * @param string|int $key
     * @param string|array $suggestedValue
     * @param string|array $suggestedValue
     */
    private function validateNestedAttributes($key, $providedValue, $suggestedValue): void
    {
        // Only suggested values that are an array should be validated for nested attributes...
        if (! is_array($suggestedValue)) {
            return;
        }

        if (Arr::isAssoc($suggestedValue)) {
            $this->nestValidationErrorsUnderAttribute($key, new self(
                new DynamicResponseModel($providedValue),
                new DynamicResponseModel($suggestedValue),
            ));

            return;
        }

        // We should allow a provided value for a list to be empty...
        if ($providedValue === []) {
            return;
        }

        // Because the suggested value typically only provides a single example for list items,
        // we should pad the suggested value array to have the same length as the provided
        // value. That way we can validate each item of the provided value list...
        $suggestedValue = array_pad($suggestedValue, count($providedValue), head($suggestedValue));

        foreach ($suggestedValue as $index => $suggestedItemValue) {
            $this->nestValidationErrorsUnderAttribute("$key.$index", new self(
                new DynamicResponseModel($providedValue[$index]),
                new DynamicResponseModel($suggestedItemValue),
            ));
        }
    }

    private function nestValidationErrorsUnderAttribute(string $key, self $nestedInstance): void
    {
        if ($nestedInstance->missing) {
            Arr::set($this->missing, $key, $nestedInstance->missing);
        }

        if ($nestedInstance->invalid) {
            Arr::set($this->invalid, $key, $nestedInstance->invalid);
        }
    }

    /**
     * @param mixed $potentiallyIterableValue
     * @return mixed
     */
    private function resolveValue($potentiallyIterableValue)
    {
        if (! $potentiallyIterableValue instanceof Iterator) {
            return $potentiallyIterableValue;
        }

        // DynamicResponseModels are pretty cumbersome to work with, so we
        // should recursively resolve them to plain arrays instead...
        $arrayValues = [];

        foreach ($potentiallyIterableValue as $key => $value) {
            if ($value instanceof DynamicResponseModel) {
                $value = $this->resolveValue($value);
            }

            $arrayValues[$key] = $value;
        }

        return $arrayValues;
    }

    public function isIncompleteOrInvalid(): bool
    {
        if ($this->invalid !== []) {
            return true;
        }

        if ($this->missing !== []) {
            return true;
        }

        return false;
    }
}
