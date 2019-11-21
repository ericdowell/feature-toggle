<?php

declare(strict_types=1);

namespace FeatureToggle\Rules;

class FeatureToggle
{
    /**
     * The name of the feature toggle.
     *
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $checkActive;

    /**
     * Create a new required validation rule based on a feature toggle status.
     *
     * @param  string  $name
     * @param  string|int|bool  $checkActive
     */
    public function __construct(string $name, $checkActive = true)
    {
        $this->name = $name;
        $this->checkActive = $checkActive;
    }

    /**
     * Convert the rule to a validation string.
     *
     * @return string
     */
    public function __toString()
    {
        return feature_toggle($this->name, $this->checkActive) ? 'required' : '';
    }
}
