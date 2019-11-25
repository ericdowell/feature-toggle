<?php

declare(strict_types=1);

namespace FeatureToggle;

use FeatureToggle\Concerns\ToggleProvider;
use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Contracts\ToggleProvider as ToggleProviderContract;
use FeatureToggle\Toggle\QueryString;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class QueryStringToggleProvider implements ToggleProviderContract
{
    use ToggleProvider;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $activeKey;

    /**
     * @var string
     */
    protected $inactiveKey;

    /**
     * @var string
     */
    protected $apiInputKey;

    /**
     * @var string|null
     */
    protected $apiKey;

    /**
     * QueryStringToggleProvider constructor.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $activeKey
     * @param  string  $inactiveKey
     * @param  string  $apiInputKey
     * @param  string|null  $apiKey
     */
    public function __construct(
        Request $request,
        string $activeKey = 'feature',
        string $inactiveKey = 'feature_off',
        string $apiInputKey = 'feature_token',
        ?string $apiKey = null
    ) {
        $this->request = $request;
        $this->activeKey = $activeKey;
        $this->inactiveKey = $inactiveKey;
        $this->apiInputKey = $apiInputKey;
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'querystring';
    }

    /**
     * @return string
     */
    public function activeKey(): string
    {
        return $this->activeKey;
    }

    /**
     * @return string
     */
    public function inactiveKey(): string
    {
        return $this->inactiveKey;
    }

    /**
     * Determine if the current request is authorized to calculate toggles.
     *
     * @return bool
     */
    public function isAuthorized(): bool
    {
        return is_null($this->apiKey) || $this->apiKey === $this->getTokenForRequest();
    }

    /**
     * Get the token for the current request.
     *
     * @return string|null
     */
    public function getTokenForRequest(): ?string
    {
        if (is_string($token = $this->request->get($this->apiInputKey))) {
            return $token;
        }

        return null;
    }

    /**
     * Get all toggles from query strings and normalize.
     *
     * @return ToggleContract[]|Collection
     */
    protected function calculateToggles(): Collection
    {
        if (! $this->isAuthorized()) {
            return collect([]);
        }
        $activeToggles = $this->calculateTogglesFromRequest($this->activeKey());
        $inactiveToggles = $this->calculateTogglesFromRequest($this->inactiveKey());

        return collect($activeToggles + $inactiveToggles);
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
        $isActive = $key === $this->activeKey() ? true : false;
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
