<?php

namespace Modules\Shared\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasAudit
{
    /**
     * Boot del trait
     */
    protected static function bootHasAudit(): void
    {
        static::creating(function (Model $model) {
            if (auth()->check()) {
                $model->creado_por = auth()->id();
            }
        });

        static::updating(function (Model $model) {
            if (auth()->check()) {
                $model->actualizado_por = auth()->id();
            }
        });
    }

    /**
     * Relación con usuario que creó
     */
    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    /**
     * Relación con usuario que actualizó
     */
    public function actualizador()
    {
        return $this->belongsTo(Usuario::class, 'actualizado_por');
    }
}