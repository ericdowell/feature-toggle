<?php

declare(strict_types=1);

namespace FeatureToggle\Toggle;

use FeatureToggle\Contracts\Toggle as ToggleContract;
use FeatureToggle\Traits\Toggle;
use Illuminate\Database\Eloquent\Model;

/**
 * FeatureToggle\Toggle\Eloquent.
 *
 * @property int $id
 * @property string $name
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\FeatureToggle\Toggle\Eloquent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\FeatureToggle\Toggle\Eloquent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\FeatureToggle\Toggle\Eloquent query()
 * @method static \Illuminate\Database\Eloquent\Builder|\FeatureToggle\Toggle\Eloquent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\FeatureToggle\Toggle\Eloquent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\FeatureToggle\Toggle\Eloquent whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\FeatureToggle\Toggle\Eloquent whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\FeatureToggle\Toggle\Eloquent whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Eloquent extends Model implements ToggleContract
{
    use Toggle;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

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
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'feature_toggles';

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
