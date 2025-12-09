<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Traits\HasAudit;
use Modules\Auth\Models\Usuario;

class Compra extends Model
{
    use HasFactory, HasAudit;

    protected $table = 'compras';

    protected $fillable = [
        'numero_compra',
        'orden_compra_id',
        'proveedor_id',
        'numero_factura_proveedor',
        'fecha_compra',
        'fecha_entrega_esperada',
        'fecha_entrega_real',
        'subtotal',
        'impuestos',
        'total',
        'metodo_pago',
        'estado',
        'notas',
        'usuario_id',
        'creado_por',
        'actualizado_por',
    ];

    protected $casts = [
        'fecha_compra' => 'date',
        'fecha_entrega_esperada' => 'date',
        'fecha_entrega_real' => 'date',
        'subtotal' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'total' => 'decimal:2',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    /**
     * Estados de compra
     */
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_RECIBIDA = 'recibida';
    const ESTADO_CANCELADA = 'cancelada';

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Generar número de compra automáticamente
        static::creating(function ($compra) {
            if (empty($compra->numero_compra)) {
                $compra->numero_compra = static::generarNumeroCompra();
            }
            if (empty($compra->fecha_compra)) {
                $compra->fecha_compra = now();
            }
        });
    }

    /**
     * Relación con Orden de Compra
     */
    public function ordenCompra()
    {
        return $this->belongsTo(OrdenCompra::class, 'orden_compra_id');
    }

    /**
     * Relación con Proveedor
     */
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    /**
     * Relación con Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Relación con Detalles de Compra
     */
    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class, 'compra_id');
    }

    /**
     * Scope: Compras recibidas
     */
    public function scopeRecibidas($query)
    {
        return $query->where('estado', self::ESTADO_RECIBIDA);
    }

    /**
     * Scope: Por proveedor
     */
    public function scopePorProveedor($query, $proveedorId)
    {
        return $query->where('proveedor_id', $proveedorId);
    }

    /**
     * Scope: Por rango de fechas
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_compra', [$fechaInicio, $fechaFin]);
    }

    /**
     * Accessor: Total de items en la compra
     */
    public function getTotalItemsAttribute()
    {
        return $this->detalles()->sum('cantidad');
    }

    /**
     * Generar número de compra único
     */
    public static function generarNumeroCompra(): string
    {
        $fecha = now()->format('Ymd');
        $ultimoNumero = static::whereDate('fecha_compra', today())
            ->max('numero_compra');

        if ($ultimoNumero) {
            $numero = intval(substr($ultimoNumero, -4)) + 1;
        } else {
            $numero = 1;
        }

        return 'C-' . $fecha . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener estados disponibles
     */
    public static function estadosDisponibles(): array
    {
        return [
            self::ESTADO_PENDIENTE,
            self::ESTADO_RECIBIDA,
            self::ESTADO_CANCELADA,
        ];
    }
}