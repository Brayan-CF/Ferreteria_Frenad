<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Traits\HasAudit;
use Modules\Auth\Models\Usuario;

class PagoCredito extends Model
{
    use HasFactory, HasAudit;

    protected $table = 'pagos_credito';

    protected $fillable = [
        'credito_cliente_id',
        'monto_pago',
        'metodo_pago',
        'fecha_pago',
        'notas',
        'usuario_id',
        'creado_por',
    ];

    protected $casts = [
        'monto_pago' => 'decimal:2',
        'fecha_pago' => 'datetime',
        'creado_en' => 'datetime',
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    /**
     * Métodos de pago
     */
    const METODO_EFECTIVO = 'efectivo';
    const METODO_QR = 'qr';

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pago) {
            if (empty($pago->fecha_pago)) {
                $pago->fecha_pago = now();
            }
        });
    }

    /**
     * Relación con Crédito Cliente
     */
    public function creditoCliente()
    {
        return $this->belongsTo(CreditoCliente::class, 'credito_cliente_id');
    }

    /**
     * Relación con Usuario que registró el pago
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Scope: Por rango de fechas
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);
    }

    /**
     * Scope: Por método de pago
     */
    public function scopePorMetodo($query, $metodo)
    {
        return $query->where('metodo_pago', $metodo);
    }

    /**
     * Scope: Del día actual
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_pago', today());
    }

    /**
     * Obtener métodos de pago disponibles
     */
    public static function metodosDisponibles(): array
    {
        return [
            self::METODO_EFECTIVO,
            self::METODO_QR,
        ];
    }
}