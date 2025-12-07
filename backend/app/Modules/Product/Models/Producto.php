<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Traits\HasAudit;
use Modules\Inventory\Models\Inventario;
use Modules\Inventory\Models\MovimientoInventario;

class Producto extends Model
{
    use HasFactory, HasAudit;

    protected $table = 'productos';

    protected $fillable = [
        'sku',
        'codigo_barras',
        'nombre',
        'descripcion',
        'categoria_id',
        'marca_id',
        'unidad_base_id',
        'precio_compra',
        'precio_venta',
        'fecha_vencimiento',
        'ubicacion_fisica',
        'activo',
        'creado_por',
        'actualizado_por',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'activo' => 'boolean',
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

        // Generar SKU automáticamente si no existe
        static::creating(function ($producto) {
            if (empty($producto->sku)) {
                $producto->sku = static::generarSKU();
            }
        });
    }

    /**
     * Relación con Categoría
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Relación con Marca
     */
    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    /**
     * Relación con Unidad de Medida Base
     */
    public function unidadBase()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_base_id');
    }

    /**
     * Relación con Unidades de Conversión
     */
    public function unidades()
    {
        return $this->hasMany(ProductoUnidad::class, 'producto_id');
    }

    /**
     * Relación con Inventario (stock por almacén)
     */
    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'producto_id');
    }

    /**
     * Relación con Movimientos de Inventario
     */
    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }

    /**
     * Scope: Solo productos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Productos con stock bajo
     */
    public function scopeStockBajo($query)
    {
        return $query->whereHas('inventarios', function ($q) {
            $q->whereRaw('cantidad_actual <= stock_minimo');
        });
    }

    /**
     * Scope: Buscar por texto
     */
    public function scopeBuscar($query, $texto)
    {
        return $query->where(function ($q) use ($texto) {
            $q->where('nombre', 'ilike', "%{$texto}%")
              ->orWhere('sku', 'ilike', "%{$texto}%")
              ->orWhere('codigo_barras', 'ilike', "%{$texto}%");
        });
    }

    /**
     * Scope: Por categoría
     */
    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    /**
     * Scope: Por marca
     */
    public function scopePorMarca($query, $marcaId)
    {
        return $query->where('marca_id', $marcaId);
    }

    /**
     * Scope: Próximos a vencer
     */
    public function scopeProximosVencer($query, $dias = 30)
    {
        return $query->whereNotNull('fecha_vencimiento')
                    ->whereBetween('fecha_vencimiento', [now(), now()->addDays($dias)]);
    }

    /**
     * Accessor: Stock total (suma de todos los almacenes)
     */
    public function getStockTotalAttribute()
    {
        return $this->inventarios()->sum('cantidad_actual');
    }

    /**
     * Accessor: Margen de ganancia (porcentaje)
     */
    public function getMargenGananciaAttribute()
    {
        if ($this->precio_compra == 0) {
            return 0;
        }
        return round((($this->precio_venta - $this->precio_compra) / $this->precio_compra) * 100, 2);
    }

    /**
     * Accessor: Utilidad unitaria
     */
    public function getUtilidadUnitariaAttribute()
    {
        return $this->precio_venta - $this->precio_compra;
    }

    /**
     * Accessor: Tiene stock suficiente en algún almacén
     */
    public function getTieneStockAttribute()
    {
        return $this->stock_total > 0;
    }

    /**
     * Método estático: Generar SKU único
     */
    public static function generarSKU(): string
    {
        do {
            $sku = 'PROD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (static::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Obtener stock en un almacén específico
     */
    public function stockEnAlmacen($almacenId)
    {
        $inventario = $this->inventarios()->where('almacen_id', $almacenId)->first();
        return $inventario ? $inventario->cantidad_actual : 0;
    }

    /**
     * Verificar si tiene stock suficiente en un almacén
     */
    public function tieneStockSuficiente($almacenId, $cantidad)
    {
        return $this->stockEnAlmacen($almacenId) >= $cantidad;
    }
}