<?php

namespace Modules\Sales\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Modules\Sales\Models\Venta;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => [
                Rule::requiredIf($this->tipo_venta === Venta::TIPO_CREDITO),
                'nullable',
                'integer',
                'exists:clientes,id'
            ],
            'tipo_venta' => ['required', 'string', Rule::in(Venta::tiposVenta())],
            'tipo_documento' => ['nullable', 'string', Rule::in(Venta::tiposDocumento())],
            'metodo_pago' => ['required', 'string', Rule::in(Venta::metodosPago())],
            'descuento_porcentaje' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notas' => ['nullable', 'string', 'max:500'],
            
            // Items de la venta
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'items.*.almacen_id' => ['required', 'integer', 'exists:almacenes,id'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unidad_id' => ['required', 'integer', 'exists:unidades_medida,id'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'items.*.descuento' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required_if' => 'Debe seleccionar un cliente para ventas a crédito',
            'tipo_venta.required' => 'Debe especificar el tipo de venta',
            'tipo_venta.in' => 'Tipo de venta no válido',
            'metodo_pago.required' => 'Debe seleccionar un método de pago',
            'metodo_pago.in' => 'Método de pago no válido',
            'items.required' => 'Debe agregar al menos un producto',
            'items.min' => 'Debe agregar al menos un producto',
            'items.*.producto_id.required' => 'Debe seleccionar un producto',
            'items.*.producto_id.exists' => 'El producto seleccionado no existe',
            'items.*.almacen_id.required' => 'Debe seleccionar un almacén',
            'items.*.cantidad.required' => 'Debe especificar la cantidad',
            'items.*.cantidad.min' => 'La cantidad debe ser mayor a 0',
            'items.*.precio_unitario.required' => 'Debe especificar el precio unitario',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            error_response('Error de validación', $validator->errors(), 422)
        );
    }
}