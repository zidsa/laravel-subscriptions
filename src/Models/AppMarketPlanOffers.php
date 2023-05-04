<?php

declare(strict_types=1);

namespace Rinvex\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Rinvex\Support\Traits\HasTranslations;
use Rinvex\Support\Traits\ValidatingTrait;
use Illuminate\Database\Eloquent\SoftDeletes;


class AppMarketPlanOffers extends Model
{
    use SoftDeletes;
    use HasTranslations;
    use ValidatingTrait;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'price',
        'app_id',
        'plan_id',
        'purchasable_id',
        'offer_period',
        'offer_interval',
    ];


    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'float',
        'app_id' => 'integer',
        'purchasable_id' => 'string',
        'offer_period' => 'integer',
        'offer_interval' => 'string',
        'deleted_at' => 'datetime',
    ];

    /**
     * {@inheritdoc}
     */
    protected $observables = [
        'validating',
        'validated',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatable = [
        'name',
        'description',
    ];

    /**
     * The default rules that the model will validate against.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Whether the model should throw a
     * ValidationException if it fails validation.
     *
     * @var bool
     */
    protected $throwValidationExceptions = true;

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('rinvex.subscriptions.tables.app_market_plan_offers'));
        $this->setRules([
            'name' => 'required|string|strip_tags|max:150',
            'description' => 'nullable|string|max:32768',
            'is_active' => 'sometimes|boolean',
            'price' => 'required|numeric',
            'offer_period' => 'sometimes|integer|max:100000',
            'offer_interval' => 'sometimes|in:hour,day,week,month',
        ]);
    }

    /**
     * Get app_id for that plan offer
     */
    public function getAppId()
    {
        return $this->app_id;
    }

    /**
     * Get offer_period for that plan offer
     */
    public function getOfferPeriod()
    {
        return $this->offer_period;
    }

    /**
     * Get offer_interval for that plan offer
     */
    public function getOfferInterval()
    {
        return $this->offer_interval;
    }

    /**
     * Activate the plan offer.
     *
     * @return $this
     */
    public function activate()
    {
        $this->update(['is_active' => true]);

        return $this;
    }

    /**
     * Deactivate the plan offer.
     *
     * @return $this
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);

        return $this;
    }
}
