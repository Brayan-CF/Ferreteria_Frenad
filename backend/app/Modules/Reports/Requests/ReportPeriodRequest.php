<?php

namespace Modules\Reports\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReportPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'periodo' => ['nullable', 'string', 'in:hoy,semana,mes,trimestre,año,personalizado'],
            'vendedor_id' => ['nullable', 'integer', 'exists:usuarios,id'],
            'proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias,id'],
            'producto_id' => ['nullable', 'integer', 'exists:productos,id'],
            'almacen_id' => ['nullable', 'integer', 'exists:almacenes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio',
            'periodo.in' => 'El período debe ser: hoy, semana, mes, trimestre, año o personalizado',
            'vendedor_id.exists' => 'El vendedor seleccionado no existe',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe',
            'cliente_id.exists' => 'El cliente seleccionado no existe',
            'categoria_id.exists' => 'La categoría seleccionada no existe',
            'producto_id.exists' => 'El producto seleccionado no existe',
            'almacen_id.exists' => 'El almacén seleccionado no existe',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            error_response('Error de validación', $validator->errors(), 422)
        );
    }

    /**
     * Obtener fechas según el período seleccionado
     */
    public function getFechas(): array
    {
        if ($this->filled('fecha_inicio') && $this->filled('fecha_fin')) {
            return [
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin' => $this->fecha_fin,
            ];
        }

        $periodo = $this->periodo ?? 'mes';

        return match ($periodo) {
            'hoy' => [
                'fecha_inicio' => now()->startOfDay(),
                'fecha_fin' => now()->endOfDay(),
            ],
            'semana' => [
                'fecha_inicio' => now()->startOfWeek(),
                'fecha_fin' => now()->endOfWeek(),
            ],
            'mes' => [
                'fecha_inicio' => now()->startOfMonth(),
                'fecha_fin' => now()->endOfMonth(),
            ],
            'trimestre' => [
                'fecha_inicio' => now()->startOfQuarter(),
                'fecha_fin' => now()->endOfQuarter(),
            ],
            'año' => [
                'fecha_inicio' => now()->startOfYear(),
                'fecha_fin' => now()->endOfYear(),
            ],
            default => [
                'fecha_inicio' => now()->startOfMonth(),
                'fecha_fin' => now()->endOfMonth(),
            ],
        };
    }
}
