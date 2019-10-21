<?php

declare(strict_types=1);

namespace FeatureToggle;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use FeatureToggle\Toggle\QueryString;
use FeatureToggle\Contracts\Toggle as ToggleContract;

class QueryStringToggleProvider extends LocalToggleProvider
{
    /**
     * @var string
     */
    const NAME = 'querystring';

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * QueryStringToggleProvider constructor.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get from all sources of toggles and normalize.
     *
     * @return ToggleContract[]|Collection
     */
    protected function calculateToggles(): Collection
    {
        $toggles = $this->calculateTogglesFromRequest('feature') + $this->calculateTogglesFromRequest('featureOff');

        return collect($toggles);
    }

    /**
     * Get toggles 'feature' or 'featureOff' query string.
     *
     * @param  string  $key
     * @return array
     */
    protected function calculateTogglesFromRequest(string $key): array
    {
        $toggles = [];
        $features = $this->request->get($key);
        $isActive = $key === 'feature' ? true : false;
        if (is_array($features)) {
            foreach ($features as $name) {
                $toggles[$name] = new QueryString($name, $isActive);
            }
        } elseif (is_string($features)) {
            $name = trim($features);

            return ! empty($name) ? [$name => new QueryString($name, $isActive)] : [];
        }

        return $toggles;
    }
}
