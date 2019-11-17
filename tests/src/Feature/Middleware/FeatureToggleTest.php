<?php

declare(strict_types=1);

namespace FeatureToggle\Tests\Feature\Middleware;

use FeatureToggle\Middleware\FeatureToggle;
use FeatureToggle\Tests\TestCase;

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
     */
    public function registerRoute(string $name, $status, $abort = 404): void
    {
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app['router'];

        $middlewareName = 'featureToggle';
        $middleware = "{$middlewareName}:{$name},{$status},{$abort}";
        $router->aliasMiddleware($middlewareName, FeatureToggle::class);

        $router->get('testing/toggle')->name(self::ROUTE)->middleware($middleware);
    }

    /**
     * @returns void
     */
    public function testRequestIsOkWhenToggleIsNotActiveAndMiddlewareMatches(): void
    {
        $this->registerRoute('foo', false);

        $response = $this->get(route(self::ROUTE));
        $response->isOk();
    }

    /**
     * @returns void
     */
    public function testRequestIsNotFoundWhenToggleIsActiveAndMiddlewareNotMatching(): void
    {
        config()->set('feature-toggle.toggles', ['foo' => true]);
        $this->registerRoute('foo', false);

        $response = $this->get(route(self::ROUTE));
        $response->isNotFound();
    }

    /**
     * @returns void
     */
    public function testRequestIsOKWhenToggleIsActiveAndMiddlewareMatches(): void
    {
        config()->set('feature-toggle.toggles', ['foo' => true]);
        $this->registerRoute('foo', true);

        $response = $this->get(route(self::ROUTE));
        $response->isOk();
    }

    /**
     * @returns void
     */
    public function testRequestIsNotFoundWhenToggleIsNotActiveAndMiddlewareNotMatching(): void
    {
        $this->registerRoute('foo', true);

        $response = $this->get(route(self::ROUTE));
        $response->isNotFound();
    }

    /**
     * @returns void
     */
    public function testRequestIsForbiddenWhenToggleIsNotActiveWhenMiddlewarePassed403(): void
    {
        $this->registerRoute('foo', true, 403);

        $response = $this->get(route(self::ROUTE));
        $response->isForbidden();
    }
}
