<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        // Otomatis filter query berdasarkan tenant yang sedang login
        static::addGlobalScope('tenant', function (Builder $builder) {
            $user = request()->user();
            if ($user && $user->tenant_id) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $user->tenant_id);
            }
        });

        // Otomatis isi tenant_id saat create data baru
        static::creating(function ($model) {
            $user = request()->user();
            if ($user && $user->tenant_id) {
                $model->tenant_id = $user->tenant_id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}