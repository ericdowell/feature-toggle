<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit;

use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;
use FeatureToggle\QueryStringToggleProvider;
use FeatureToggle\Tests\TestCase;
use FeatureToggle\Tests\Traits\TestToggleProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class QueryStringToggleProviderTest extends TestCase
{
    use TestToggleProvider;

    /**
     * @param  \Illuminate\Http\Request|null  $request
     * @return \FeatureToggle\QueryStringToggleProvider
     */
    protected function getToggleProvider(Request $request = null): QueryStringToggleProvider
    {
        $request = $request ?? request();

        return (new QueryStringToggleProvider($request))->refreshToggles();
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $toggles
     * @returns void
     */
    public static function setupQueryToggles(Request &$request, array $toggles): void
    {
        if (! empty($toggles)) {
            $queryStrings = [
                'feature' => [],
                'featureOff' => [],
            ];
            foreach ($toggles as $name => $value) {
                $isActive = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                $queryStrings[$isActive ? 'feature' : 'featureOff'][] = $name;
            }
            foreach (array_keys($queryStrings) as $type) {
                if (count($queryStrings[$type]) === 1) {
                    $queryStrings[$type] = Arr::first($queryStrings[$type]);
                }
            }
            $request->request->add($queryStrings);
        }
    }

    /**
     * @param  array|null  $toggles
     * @return \FeatureToggle\QueryStringToggleProvider|ToggleProviderContract
     */
    protected function setToggles(array $toggles = null): ToggleProviderContract
    {
        /** @var Request $request */
        $request = $this->app['request'];
        if (! empty($toggles)) {
            $this->setupQueryToggles($request, $toggles);
        }

        return $this->getToggleProvider($request);
    }
}
