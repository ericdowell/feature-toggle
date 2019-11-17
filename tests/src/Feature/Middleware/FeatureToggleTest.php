<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Feature\Middleware;

use FeatureToggle\Tests\TestCase;

/**
 * @group feature
 */
class FeatureToggleTest extends TestCase
{
    /**
     * @var string
     */
    const ROUTE = 'toggle.testing';

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

        $router->get('testing/toggle')->name(self::ROUTE)->middleware($middleware)->uses(function () {
            return response()->json(['success' => true]);
        });
    }

    /**
     * @returns void
     */
    public function testRequestIsOkWhenToggleIsNotActiveAndMiddlewareMatches(): void
    {
        $this->registerRoute('foo', false);

        $response = $this->get(route(self::ROUTE));
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    /**
     * @returns void
     */
    public function testRequestIsNotFoundWhenToggleIsActiveAndMiddlewareNotMatching(): void
    {
        config()->set('feature-toggle.toggles', ['foo' => true]);
        $this->registerRoute('foo', false);

        $response = $this->get(route(self::ROUTE));
        $response->assertNotFound();
    }

    /**
     * @returns void
     */
    public function testRequestIsOKWhenToggleIsActiveAndMiddlewareMatches(): void
    {
        config()->set('feature-toggle.toggles', ['foo' => true]);
        $this->registerRoute('foo', true);

        $response = $this->get(route(self::ROUTE));
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    /**
     * @returns void
     */
    public function testRequestIsNotFoundWhenToggleIsNotActiveAndMiddlewareNotMatching(): void
    {
        $this->registerRoute('foo', true);

        $response = $this->get(route(self::ROUTE));
        $response->assertNotFound();
    }

    /**
     * @returns void
     */
    public function testRequestIsForbiddenWhenToggleIsNotActiveWhenMiddlewarePassed403(): void
    {
        $this->registerRoute('foo', true, 403);

        $response = $this->get(route(self::ROUTE));
        $response->assertForbidden();
    }
}
