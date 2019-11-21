<?php

declare(strict_types=1);

namespace FeatureToggle\Validation;

use Illuminate\Validation\Concerns\ValidatesAttributes;
use InvalidArgumentException;
use Throwable;

class FeatureToggle
{
    use ValidatesAttributes;

    /**
     * @var string
     */
    const ERROR_MESSAGE = 'Feature toggle name was not provided as the first validation parameter.';

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public function validate($attribute, $value, array $parameters): bool
    {
        try {
            [$name] = $parameters;
        } catch (Throwable $exception) {
            throw new InvalidArgumentException(self::ERROR_MESSAGE, 0, $exception);
        }

        if (feature_toggle($name, $parameters[1] ?? true)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }
}
