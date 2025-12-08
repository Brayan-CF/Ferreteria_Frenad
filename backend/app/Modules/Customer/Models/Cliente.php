<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Traits\HasAudit;
use Modules\Sales\Models\Venta;

class Cliente extends Model
{
    use HasFactory, HasAudit;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre_completo',
        'nit',
        'telefono',
        'direccion',
        'email',
        'es_frecuente',
        'limite_credito',
        'activo',
        'fecha_registro',
        'creado_por',
        'actualizado_por',
    ];

    protected $casts = [
        'es_frecuente' => 'boolean',
        'limite_credito' => 'decimal:2',
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

        static::creating(function ($cliente) {
            if (empty($cliente->fecha_registro)) {
                $cliente->fecha_registro = now();
            }
        });
    }

    /**
     * Relación con Ventas
     */
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }

    /**
     * Relación con Créditos
     */
    public function creditos()
    {
        return $this->hasMany(CreditoCliente::class, 'cliente_id');
    }

    /**
     * Relación con Pagos de Crédito
     */
    public function pagos()
    {
        return $this->hasManyThrough(
            PagoCredito::class,
            CreditoCliente::class,
            'cliente_id',
            'credito_cliente_id',
            'id',
            'id'
        );
    }

    /**
     * Scope: Solo clientes activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Clientes frecuentes
     */
    public function scopeFrecuentes($query)
    {
        return $query->where('es_frecuente', true);
    }

    /**
     * Scope: Con deuda pendiente
     */
    public function scopeConDeuda($query)
    {
        return $query->whereHas('creditos', function ($q) {
            $q->where('saldo_pendiente', '>', 0)
              ->whereIn('estado', ['pendiente', 'pagado_parcial', 'vencido']);
        });
    }

    /**
     * Scope: Buscar por texto
     */
    public function scopeBuscar($query, $texto)
    {
        return $query->where(function ($q) use ($texto) {
            $q->where('nombre_completo', 'ilike', "%{$texto}%")
              ->orWhere('nit', 'ilike', "%{$texto}%")
              ->orWhere('telefono', 'ilike', "%{$texto}%");
        });
    }

    /**
     * Accessor: Total de compras realizadas
     */
    public function getTotalComprasAttribute()
    {
        return $this->ventas()->where('estado', 'completada')->sum('total');
    }

    /**
     * Accessor: Cantidad de compras
     */
    public function getCantidadComprasAttribute()
    {
        return $this->ventas()->where('estado', 'completada')->count();
    }

    /**
     * Accessor: Deuda total actual
     */
    public function getDeudaTotalAttribute()
    {
        return $this->creditos()
            ->whereIn('estado', ['pendiente', 'pagado_parcial', 'vencido'])
            ->sum('saldo_pendiente');
    }

    /**
     * Accessor: Tiene deuda
     */
    public function getTieneDeudaAttribute()
    {
        return $this->deuda_total > 0;
    }

    /**
     * Accessor: Crédito disponible
     */
    public function getCreditoDisponibleAttribute()
    {
        return $this->limite_credito - $this->deuda_total;
    }

    /**
     * Accessor: Puede comprar a crédito
     */
    public function getPuedeComprarCreditoAttribute()
    {
        return $this->credito_disponible > 0;
    }

    /**
     * Accessor: Estado de cuenta
     */
    public function getEstadoCuentaAttribute()
    {
        if ($this->deuda_total == 0) {
            return 'AL_DIA';
        }

        $creditosVencidos = $this->creditos()
            ->where('estado', 'vencido')
            ->count();

        if ($creditosVencidos > 0) {
            return 'MOROSO';
        }

        return 'CON_DEUDA';
    }

    /**
     * Verificar si puede comprar a crédito por un monto específico
     */
    public function puedeComprarPorMonto($monto): bool
    {
        return $this->credito_disponible >= $monto;
    }

    /**
     * Obtener historial de compras
     */
    public function historialCompras()
    {
        return $this->ventas()
            ->with('detalles.producto')
            ->where('estado', 'completada')
            ->orderBy('fecha_venta', 'desc')
            ->get();
    }
}