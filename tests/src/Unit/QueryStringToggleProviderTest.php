<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Unit;

use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;
use FeatureToggle\QueryStringToggleProvider;
use FeatureToggle\Tests\Concerns\TestToggleProvider;
use FeatureToggle\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class QueryStringToggleProviderTest extends TestCase
{
    use TestToggleProvider;

    /**
     * @param  \Illuminate\Http\Request|null  $request
     * @param  string  $activeKey
     * @param  string  $inactiveKey
     * @return \FeatureToggle\QueryStringToggleProvider
     */
    protected function getToggleProvider(
        Request $request = null,
        string $activeKey = 'feature',
        string $inactiveKey = 'feature_off'
    ): QueryStringToggleProvider {
        $request = $request ?? request();

        return (new QueryStringToggleProvider($request, $activeKey, $inactiveKey))->refreshToggles();
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $toggles
     * @param  string  $activeKey
     * @param  string  $inactiveKey
     * @return void
     */
    public static function setupQueryToggles(
        Request &$request,
        array $toggles,
        string $activeKey = 'feature',
        string $inactiveKey = 'feature_off'
    ): void {
        if (! empty($toggles)) {
            $queryStrings = [
                $activeKey => [],
                $inactiveKey => [],
            ];
            foreach ($toggles as $name => $value) {
                $isActive = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                $queryStrings[$isActive ? $activeKey : $inactiveKey][] = $name;
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
     * @param  string  $activeKey
     * @param  string  $inactiveKey
     * @return \FeatureToggle\QueryStringToggleProvider|ToggleProviderContract
     */
    protected function setToggles(
        array $toggles = null,
        string $activeKey = 'feature',
        string $inactiveKey = 'feature_off'
    ): ToggleProviderContract {
        /** @var Request $request */
        $request = $this->app['request'];
        if (! empty($toggles)) {
            $this->setupQueryToggles($request, $toggles, $activeKey, $inactiveKey);
        }

        return $this->getToggleProvider($request, $activeKey, $inactiveKey);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @returns void
     */
    protected function setupCommonToggles(Request $request): void
    {
        $toggles = [
            'foo' => $this->getIsActiveAttribute(true),
            'bar' => $this->getIsActiveAttribute('off'),
            'baz' => $this->getIsActiveAttribute('on'),
        ];
        $this->setupQueryToggles($request, $toggles);
    }

    /**
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testPassingActiveKeyAndInactiveKeyParameterToAppMake(): void
    {
        $activeKey = 'active';
        $inactiveKey = 'inactive';

        /** @var QueryStringToggleProvider $toggleProvider */
        $toggleProvider = app()->make('feature-toggle.querystring', compact('activeKey', 'inactiveKey'));

        $this->assertSame($activeKey, $toggleProvider->activeKey());
        $this->assertSame($inactiveKey, $toggleProvider->inactiveKey());
    }

    /**
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testPassingApiKeyParameterToAppMakeWithInputKeyOnRequestReturnsToggles(): void
    {
        $apiKey = Str::random();
        $apiInputKey = 'feature_token';

        /** @var Request $request */
        $request = $this->app['request'];
        $request->request->set($apiInputKey, $apiKey);

        $this->setupCommonToggles($request);

        /** @var QueryStringToggleProvider $toggleProvider */
        $toggleProvider = app()->make('feature-toggle.querystring', compact('request', 'apiKey', 'apiInputKey'));
        $toggleProvider->refreshToggles();

        $this->assertTrue($toggleProvider->isAuthorized(), '"isAuthorized" should return true.');
        $this->assertCommonToggles($toggleProvider);
    }

    /**
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testPassingApiKeyParameterToAppMakeWithInputKeyNotOnRequestReturnsNoToggles(): void
    {
        $apiKey = Str::random();
        $apiInputKey = 'feature_token';

        /** @var Request $request */
        $request = $this->app['request'];

        $this->setupCommonToggles($request);

        /** @var QueryStringToggleProvider $toggleProvider */
        $toggleProvider = app()->make('feature-toggle.querystring', compact('request', 'apiKey', 'apiInputKey'));
        $toggleProvider->refreshToggles();

        $this->assertFalse($toggleProvider->isAuthorized(), '"isAuthorized" should return false.');
        $this->assertCount(0, $toggleProvider->getToggles(), 'Checking "getToggles" count.');
        $this->assertCount(0, $toggleProvider->getActiveToggles(), 'Checking "getActiveToggles" count.');
    }

    /**
     * @return void
     */
    public function testManyActiveAndInactiveToggles(): void
    {
        $toggleProvider = $this->setToggles([
            'foo' => $this->getIsActiveAttribute(true),
            'bar' => $this->getIsActiveAttribute('off'),
            'baz' => $this->getIsActiveAttribute('on'),
        ], 'active', 'inactive');

        $this->assertCommonToggles($toggleProvider);
    }
}
