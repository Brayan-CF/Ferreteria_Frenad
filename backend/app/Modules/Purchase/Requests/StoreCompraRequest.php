<?php

namespace Modules\Purchase\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orden_compra_id' => ['nullable', 'integer', 'exists:ordenes_compra,id'],
            'proveedor_id' => ['required', 'integer', 'exists:proveedores,id'],
            'numero_factura_proveedor' => ['nullable', 'string', 'max:100'],
            'fecha_entrega_esperada' => ['nullable', 'date'],
            'metodo_pago' => ['nullable', 'string', 'max:50'],
            'notas' => ['nullable', 'string', 'max:500'],
            
            // Items de la compra
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'items.*.almacen_destino_id' => ['required', 'integer', 'exists:almacenes,id'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unidad_id' => ['required', 'integer', 'exists:unidades_medida,id'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'proveedor_id.required' => 'Debe seleccionar un proveedor',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe',
            'items.required' => 'Debe agregar al menos un producto',
            'items.min' => 'Debe agregar al menos un producto',
            'items.*.producto_id.required' => 'Debe seleccionar un producto',
            'items.*.producto_id.exists' => 'El producto seleccionado no existe',
            'items.*.almacen_destino_id.required' => 'Debe seleccionar el almacén destino',
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
