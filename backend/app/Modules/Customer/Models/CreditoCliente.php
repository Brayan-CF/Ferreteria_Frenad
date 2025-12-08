<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Sales\Models\Venta;

class CreditoCliente extends Model
{
    use HasFactory;

    protected $table = 'creditos_clientes';

    protected $fillable = [
        'cliente_id',
        'venta_id',
        'monto_total',
        'monto_pagado',
        'saldo_pendiente',
        'fecha_vencimiento',
        'estado',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    /**
     * Estados de crédito
     */
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_PAGADO_PARCIAL = 'pagado_parcial';
    const ESTADO_PAGADO_COMPLETO = 'pagado_completo';
    const ESTADO_VENCIDO = 'vencido';
    const ESTADO_CANCELADO = 'cancelado';

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Actualizar estado automáticamente al guardar
        static::saving(function ($credito) {
            $credito->actualizarEstado();
        });
    }

    /**
     * Relación con Cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Relación con Venta
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    /**
     * Relación con Pagos
     */
    public function pagos()
    {
        return $this->hasMany(PagoCredito::class, 'credito_cliente_id');
    }

    /**
     * Scope: Pendientes de pago
     */
    public function scopePendientes($query)
    {
        return $query->whereIn('estado', [
            self::ESTADO_PENDIENTE,
            self::ESTADO_PAGADO_PARCIAL,
            self::ESTADO_VENCIDO
        ]);
    }

    /**
     * Scope: Vencidos
     */
    public function scopeVencidos($query)
    {
        return $query->where('estado', self::ESTADO_VENCIDO);
    }

    /**
     * Scope: Por vencer (próximos X días)
     */
    public function scopePorVencer($query, $dias = 7)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE)
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays($dias)]);
    }

    /**
     * Accessor: Días transcurridos desde vencimiento
     */
    public function getDiasVencidosAttribute()
    {
        if ($this->fecha_vencimiento >= now()) {
            return 0;
        }
        return now()->diffInDays($this->fecha_vencimiento);
    }

    /**
     * Accessor: Días hasta vencimiento
     */
    public function getDiasParaVencimientoAttribute()
    {
        if ($this->fecha_vencimiento < now()) {
            return 0;
        }
        return now()->diffInDays($this->fecha_vencimiento);
    }

    /**
     * Accessor: Está vencido
     */
    public function getEstaVencidoAttribute()
    {
        return $this->fecha_vencimiento < now() && $this->saldo_pendiente > 0;
    }

    /**
     * Accessor: Porcentaje pagado
     */
    public function getPorcentajePagadoAttribute()
    {
        if ($this->monto_total == 0) {
            return 0;
        }
        return round(($this->monto_pagado / $this->monto_total) * 100, 2);
    }

    /**
     * Actualizar estado del crédito
     */
    public function actualizarEstado(): void
    {
        // Si fue cancelado manualmente, no cambiar
        if ($this->estado === self::ESTADO_CANCELADO) {
            return;
        }

        // Calcular saldo
        $this->saldo_pendiente = $this->monto_total - $this->monto_pagado;

        // Determinar estado
        if ($this->saldo_pendiente <= 0) {
            $this->estado = self::ESTADO_PAGADO_COMPLETO;
        } elseif ($this->monto_pagado > 0) {
            $this->estado = self::ESTADO_PAGADO_PARCIAL;
        } elseif ($this->fecha_vencimiento < now()) {
            $this->estado = self::ESTADO_VENCIDO;
        } else {
            $this->estado = self::ESTADO_PENDIENTE;
        }
    }

    /**
     * Registrar pago
     */
    public function registrarPago($monto, $metodoPago, $notas = null, $usuarioId = null)
    {
        if ($monto > $this->saldo_pendiente) {
            throw new \Exception('El monto del pago excede el saldo pendiente');
        }

        $this->monto_pagado += $monto;
        $this->save();

        $usuarioId = $usuarioId ?? auth()->id();

        return PagoCredito::create([
            'credito_cliente_id' => $this->id,
            'monto_pago' => $monto,
            'metodo_pago' => $metodoPago,
            'notas' => $notas,
            'usuario_id' => $usuarioId,
            'creado_por' => $usuarioId,
        ]);
    }

    /**
     * Obtener todos los estados disponibles
     */
    public static function estadosDisponibles(): array
    {
        return [
            self::ESTADO_PENDIENTE,
            self::ESTADO_PAGADO_PARCIAL,
            self::ESTADO_PAGADO_COMPLETO,
            self::ESTADO_VENCIDO,
            self::ESTADO_CANCELADO,
        ];
    }
}