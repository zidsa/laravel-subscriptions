<?php

namespace Rinvex\Subscriptions\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PrivateScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->whereNull('store_id');
    }
}
