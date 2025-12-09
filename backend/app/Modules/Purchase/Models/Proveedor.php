<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Traits\HasAudit;

class Proveedor extends Model
{
    use HasFactory, HasAudit;

    protected $table = 'proveedores';

    protected $fillable = [
        'razon_social',
        'nit',
        'telefono',
        'direccion',
        'email',
        'nombre_contacto',
        'activo',
        'fecha_registro',
        'creado_por',
        'actualizado_por',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_registro' => 'date',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($proveedor) {
            if (empty($proveedor->fecha_registro)) {
                $proveedor->fecha_registro = now();
            }
        });
    }

    /**
     * Relación con Órdenes de Compra
     */
    public function ordenesCompra()
    {
        return $this->hasMany(OrdenCompra::class, 'proveedor_id');
    }

    /**
     * Relación con Compras
     */
    public function compras()
    {
        return $this->hasMany(Compra::class, 'proveedor_id');
    }

    /**
     * Scope: Solo proveedores activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Buscar por texto
     */
    public function scopeBuscar($query, $texto)
    {
        return $query->where(function ($q) use ($texto) {
            $q->where('razon_social', 'ilike', "%{$texto}%")
              ->orWhere('nit', 'ilike', "%{$texto}%")
              ->orWhere('nombre_contacto', 'ilike', "%{$texto}%");
        });
    }

    /**
     * Accessor: Total de compras realizadas
     */
    public function getTotalComprasAttribute()
    {
        return $this->compras()->where('estado', 'recibida')->sum('total');
    }

    /**
     * Accessor: Cantidad de compras
     */
    public function getCantidadComprasAttribute()
    {
        return $this->compras()->where('estado', 'recibida')->count();
    }

    /**
     * Accessor: Total de órdenes pendientes
     */
    public function getOrdenesPendientesAttribute()
    {
        return $this->ordenesCompra()->where('estado', 'pendiente')->count();
    }

    /**
     * Accessor: Promedio de compra
     */
    public function getPromedioCompraAttribute()
    {
        if ($this->cantidad_compras == 0) {
            return 0;
        }
        return round($this->total_compras / $this->cantidad_compras, 2);
    }
}