<?php

declare(strict_types=1);

namespace Rinvex\Subscriptions\Traits;

use Rinvex\Subscriptions\Scopes\PrivateScope;

trait HasPrivate
{
    protected static function bootHasPrivate()
    {
        static::addGlobalScope(new PrivateScope);
    }

    public function scopeWithPrivate(Builder $builder)
    {
        return $builder->withoutGlobalScope(PrivateScope::class);
    }

    public function scopeWithoutPrivate(Builder $builder)
    {
        return $builder->withoutGlobalScope(PrivateScope::class)
            ->whereNull('store_id');
    }

    public function scopeOnlyPrivate(Builder $builder)
    {
        return $builder->withoutGlobalScope(PrivateScope::class)
            ->whereNotNull('store_id');
    }
}
