<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        // Otomatis filter query berdasarkan tenant yang sedang login atau yang sedang dikunjungi
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = request('current_tenant_id') ?? (request()->user()?->tenant_id ?? null);
            if ($tenantId) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
            }
        });

        // Otomatis isi tenant_id saat create data baru
        static::creating(function ($model) {
            $tenantId = request('current_tenant_id') ?? (request()->user()?->tenant_id ?? null);
            if ($tenantId) {
                $model->tenant_id = $tenantId;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}