<?php

namespace Modules\Inventory\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransferenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'almacen_origen_id' => ['required', 'integer', 'exists:almacenes,id'],
            'almacen_destino_id' => ['required', 'integer', 'exists:almacenes,id', 'different:almacen_origen_id'],
            'cantidad' => ['required', 'numeric', 'min:0.0001'],
            'razon' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.required' => 'Debe seleccionar un producto',
            'producto_id.exists' => 'El producto seleccionado no existe',
            'almacen_origen_id.required' => 'Debe seleccionar el almacén de origen',
            'almacen_origen_id.exists' => 'El almacén de origen no existe',
            'almacen_destino_id.required' => 'Debe seleccionar el almacén de destino',
            'almacen_destino_id.exists' => 'El almacén de destino no existe',
            'almacen_destino_id.different' => 'El almacén de origen y destino no pueden ser el mismo',
            'cantidad.required' => 'Debe ingresar la cantidad a transferir',
            'cantidad.min' => 'La cantidad debe ser mayor a 0',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            error_response('Error de validación', $validator->errors(), 422)
        );
    }
}s