<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Traits\HasAudit;
use Modules\Auth\Models\Usuario;
use Modules\Customer\Models\Cliente;

class Venta extends Model
{
    use HasFactory, HasAudit;

    protected $table = 'ventas';

    protected $fillable = [
        'numero_venta',
        'cliente_id',
        'usuario_id',
        'fecha_venta',
        'tipo_venta',
        'tipo_documento',
        'metodo_pago',
        'subtotal',
        'descuento_porcentaje',
        'descuento_monto',
        'iva',
        'total',
        'estado',
        'notas',
        'creado_por',
        'actualizado_por',
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento_porcentaje' => 'decimal:2',
        'descuento_monto' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    /**
     * Tipos de venta
     */
    const TIPO_CONTADO = 'contado';
    const TIPO_CREDITO = 'credito';

    /**
     * Tipos de documento
     */
    const DOC_FACTURA = 'factura';
    const DOC_RECIBO = 'recibo';
    const DOC_NOTA_VENTA = 'nota_venta';

    /**
     * Métodos de pago
     */
    const PAGO_EFECTIVO = 'efectivo';
    const PAGO_QR = 'qr';

    /**
     * Estados
     */
    const ESTADO_COMPLETADA = 'completada';
    const ESTADO_ANULADA = 'anulada';
    const ESTADO_DEVUELTA = 'devuelta';

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Generar número de venta automáticamente
        static::creating(function ($venta) {
            if (empty($venta->numero_venta)) {
                $venta->numero_venta = static::generarNumeroVenta();
            }
            if (empty($venta->fecha_venta)) {
                $venta->fecha_venta = now();
            }
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
     * Relación con Usuario (vendedor)
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Relación con Detalles de Venta
     */
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    /**
     * Scope: Ventas completadas
     */
    public function scopeCompletadas($query)
    {
        return $query->where('estado', self::ESTADO_COMPLETADA);
    }

    /**
     * Scope: Ventas anuladas
     */
    public function scopeAnuladas($query)
    {
        return $query->where('estado', self::ESTADO_ANULADA);
    }

    /**
     * Scope: Ventas a crédito
     */
    public function scopeCredito($query)
    {
        return $query->where('tipo_venta', self::TIPO_CREDITO);
    }

    /**
     * Scope: Ventas de contado
     */
    public function scopeContado($query)
    {
        return $query->where('tipo_venta', self::TIPO_CONTADO);
    }

    /**
     * Scope: Por rango de fechas
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_venta', [$fechaInicio, $fechaFin]);
    }

    /**
     * Scope: Por vendedor
     */
    public function scopePorVendedor($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    /**
     * Scope: Por cliente
     */
    public function scopePorCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    /**
     * Scope: Del día actual
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_venta', today());
    }

    /**
     * Accessor: Total de items en la venta
     */
    public function getTotalItemsAttribute()
    {
        return $this->detalles()->sum('cantidad');
    }

    /**
     * Accessor: Costo total de la venta
     */
    public function getCostoTotalAttribute()
    {
        return $this->detalles()
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->sum(\DB::raw('detalle_ventas.cantidad * productos.precio_compra'));
    }

    /**
     * Accessor: Utilidad bruta
     */
    public function getUtilidadBrutaAttribute()
    {
        return $this->total - $this->costo_total;
    }

    /**
     * Accessor: Margen de ganancia (%)
     */
    public function getMargenGananciaAttribute()
    {
        if ($this->costo_total == 0) {
            return 0;
        }
        return round(($this->utilidad_bruta / $this->costo_total) * 100, 2);
    }

    /**
     * Generar número de venta único
     */
    public static function generarNumeroVenta(): string
    {
        $fecha = now()->format('Ymd');
        $ultimoNumero = static::whereDate('fecha_venta', today())
            ->max('numero_venta');

        if ($ultimoNumero) {
            $numero = intval(substr($ultimoNumero, -4)) + 1;
        } else {
            $numero = 1;
        }

        return $fecha . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Verificar si puede ser anulada
     */
    public function puedeSerAnulada(): bool
    {
        return $this->estado === self::ESTADO_COMPLETADA;
    }

    /**
     * Obtener tipos de venta disponibles
     */
    public static function tiposVenta(): array
    {
        return [self::TIPO_CONTADO, self::TIPO_CREDITO];
    }

    /**
     * Obtener tipos de documento disponibles
     */
    public static function tiposDocumento(): array
    {
        return [self::DOC_FACTURA, self::DOC_RECIBO, self::DOC_NOTA_VENTA];
    }

    /**
     * Obtener métodos de pago disponibles
     */
    public static function metodosPago(): array
    {
        return [self::PAGO_EFECTIVO, self::PAGO_QR];
    }
}