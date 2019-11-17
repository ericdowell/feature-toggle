<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Feature\Middleware;

use FeatureToggle\Tests\TestCase;
use Illuminate\Foundation\Testing\TestResponse;

/**
 * @group feature
 */
class FeatureToggleTest extends TestCase
{
    /**
     * @param  string  $name
     * @param  string|int|bool  $status
     * @param  int  $abort
     * @returns void
     */
    public function registerRoute(string $name, $status, $abort = 404): void
    {
        // Make sure to refresh the toggles.
        feature_toggle_api()->refreshToggles();
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app['router'];
        $middleware = "featureToggle:{$name},{$status},{$abort}";

        $router->get('testing/toggle')->name('toggle.testing')->middleware($middleware)->uses(function () {
            return response()->json(['success' => true]);
        });
    }

    /**
     * @param  string  $name
     * @param $status
     * @param  int  $abort
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function callEndpoint(string $name, $status, $abort = 404): TestResponse
    {
        $this->registerRoute($name, $status, $abort);

        return $this->get(route('toggle.testing'));
    }

    /**
     * @returns void
     */
    public function testRequestIsOkWhenToggleIsNotActiveAndMiddlewareMatches(): void
    {
        $this->callEndpoint('foo', false)->assertOk()->assertJson(['success' => true]);
        $this->callEndpoint('foo', 'off')->assertOk()->assertJson(['success' => true]);
        $this->callEndpoint('foo', 'no')->assertOk()->assertJson(['success' => true]);
        $this->callEndpoint('foo', 'foo')->assertOk()->assertJson(['success' => true]);
    }

    /**
     * @returns void
     */
    public function testRequestIsNotFoundWhenToggleIsActiveAndMiddlewareNotMatching(): void
    {
        config()->set('feature-toggle.toggles', ['foo' => true]);

        $this->callEndpoint('foo', false)->assertNotFound();
    }

    /**
     * @returns void
     */
    public function testRequestIsOKWhenToggleIsActiveAndMiddlewareMatches(): void
    {
        config()->set('feature-toggle.toggles', ['foo' => true]);

        $this->callEndpoint('foo', true)->assertOk()->assertJson(['success' => true]);
        $this->callEndpoint('foo', 'on')->assertOk()->assertJson(['success' => true]);
        $this->callEndpoint('foo', 'yes')->assertOk()->assertJson(['success' => true]);
    }

    /**
     * @returns void
     */
    public function testRequestIsNotFoundWhenToggleIsNotActiveAndMiddlewareNotMatching(): void
    {
        $this->callEndpoint('foo', true)->assertNotFound();
    }

    /**
     * @returns void
     */
    public function testRequestIsForbiddenWhenToggleIsNotActiveWhenMiddlewarePassed403(): void
    {
        $this->callEndpoint('foo', true, 403)->assertForbidden();
    }
}
