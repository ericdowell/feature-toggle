<?php

declare(strict_types=1);

namespace FeatureToggle\Toggle;

use FeatureToggle\Traits\Toggle;
use Illuminate\Database\Eloquent\Model;
use FeatureToggle\Contracts\Toggle as ToggleContract;

/**
 * FeatureToggle\FeatureToggle.
 *
 * @property int $id
 * @property string $name
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\FeatureToggle\Toggle\FeatureToggle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\FeatureToggle\Toggle\FeatureToggle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\FeatureToggle\Toggle\FeatureToggle query()
 * @method static \Illuminate\Database\Eloquent\Builder|\FeatureToggle\Toggle\FeatureToggle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\FeatureToggle\Toggle\FeatureToggle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\FeatureToggle\Toggle\FeatureToggle whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\FeatureToggle\Toggle\FeatureToggle whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\FeatureToggle\Toggle\FeatureToggle whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FeatureToggle extends Model implements ToggleContract
{
    use Toggle;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Set the feature toggle's is_active value.
     *
     * @param  string  $value
     * @return void
     */
    public function setIsActiveAttribute($value)
    {
        $this->attributes['is_active'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
