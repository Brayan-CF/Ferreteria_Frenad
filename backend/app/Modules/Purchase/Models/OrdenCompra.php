<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Traits\HasAudit;
use Modules\Auth\Models\Usuario;

class OrdenCompra extends Model
{
    use HasFactory, HasAudit;

    protected $table = 'ordenes_compra';

    protected $fillable = [
        'numero_orden',
        'proveedor_id',
        'fecha_orden',
        'fecha_entrega_esperada',
        'total_ordenado',
        'estado',
        'notas',
        'usuario_id',
        'creado_por',
        'actualizado_por',
    ];

    protected $casts = [
        'fecha_orden' => 'date',
        'fecha_entrega_esperada' => 'date',
        'total_ordenado' => 'decimal:2',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    /**
     * Estados de orden de compra
     */
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_RECIBIDA_PARCIAL = 'recibida_parcial';
    const ESTADO_RECIBIDA_COMPLETA = 'recibida_completa';
    const ESTADO_CANCELADA = 'cancelada';

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Generar número de orden automáticamente
        static::creating(function ($orden) {
            if (empty($orden->numero_orden)) {
                $orden->numero_orden = static::generarNumeroOrden();
            }
            if (empty($orden->fecha_orden)) {
                $orden->fecha_orden = now();
            }
        });
    }

    /**
     * Relación con Proveedor
     */
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    /**
     * Relación con Usuario que creó la orden
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Relación con Compras (recepciones)
     */
    public function compras()
    {
        return $this->hasMany(Compra::class, 'orden_compra_id');
    }

    /**
     * Scope: Órdenes pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
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
        return $query->whereBetween('fecha_orden', [$fechaInicio, $fechaFin]);
    }

    /**
     * Accessor: Días hasta entrega esperada
     */
    public function getDiasParaEntregaAttribute()
    {
        if ($this->fecha_entrega_esperada < now()) {
            return 0;
        }
        return now()->diffInDays($this->fecha_entrega_esperada);
    }

    /**
     * Accessor: Está retrasada
     */
    public function getEstaRetrasadaAttribute()
    {
        return $this->fecha_entrega_esperada < now() && 
               $this->estado === self::ESTADO_PENDIENTE;
    }

    /**
     * Generar número de orden único
     */
    public static function generarNumeroOrden(): string
    {
        $fecha = now()->format('Ymd');
        $ultimoNumero = static::whereDate('fecha_orden', today())
            ->max('numero_orden');

        if ($ultimoNumero) {
            $numero = intval(substr($ultimoNumero, -4)) + 1;
        } else {
            $numero = 1;
        }

        return 'OC-' . $fecha . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener estados disponibles
     */
    public static function estadosDisponibles(): array
    {
        return [
            self::ESTADO_PENDIENTE,
            self::ESTADO_RECIBIDA_PARCIAL,
            self::ESTADO_RECIBIDA_COMPLETA,
            self::ESTADO_CANCELADA,
        ];
    }
}