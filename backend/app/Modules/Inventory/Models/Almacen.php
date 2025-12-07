<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;

    protected $table = 'almacenes';

    protected $fillable = [
        'nombre',
        'tipo',
        'ubicacion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'creado_en' => 'datetime',
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    /**
     * Tipos de almacén disponibles
     */
    const TIPO_BODEGA = 'bodega';
    const TIPO_MOSTRADOR = 'mostrador';
    const TIPO_OTRO = 'otro';

    /**
     * Relación con Inventarios
     */
    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'almacen_id');
    }

    /**
     * Relación con Movimientos de Inventario (origen)
     */
    public function movimientosOrigen()
    {
        return $this->hasMany(MovimientoInventario::class, 'almacen_id');
    }

    /**
     * Relación con Movimientos de Inventario (destino)
     */
    public function movimientosDestino()
    {
        return $this->hasMany(MovimientoInventario::class, 'almacen_destino_id');
    }

    /**
     * Scope: Solo almacenes activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Accessor: Total de productos en este almacén
     */
    public function getTotalProductosAttribute()
    {
        return $this->inventarios()->count();
    }

    /**
     * Accessor: Valor total del inventario
     */
    public function getValorTotalInventarioAttribute()
    {
        return $this->inventarios()
            ->join('productos', 'inventario.producto_id', '=', 'productos.id')
            ->sum(\DB::raw('inventario.cantidad_actual * productos.precio_compra'));
    }

    /**
     * Obtener tipos de almacén disponibles
     */
    public static function tiposDisponibles(): array
    {
        return [
            self::TIPO_BODEGA,
            self::TIPO_MOSTRADOR,
            self::TIPO_OTRO,
        ];
    }
}