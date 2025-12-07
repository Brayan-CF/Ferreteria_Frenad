<?php

namespace Modules\Inventory\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AjusteInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'almacen_id' => ['required', 'integer', 'exists:almacenes,id'],
            'nueva_cantidad' => ['required', 'numeric', 'min:0'],
            'razon' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.required' => 'Debe seleccionar un producto',
            'almacen_id.required' => 'Debe seleccionar un almacén',
            'nueva_cantidad.required' => 'Debe ingresar la nueva cantidad',
            'nueva_cantidad.min' => 'La cantidad no puede ser negativa',
            'razon.required' => 'Debe ingresar una razón para el ajuste (obligatorio)',
            'razon.max' => 'La razón no puede superar 500 caracteres',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            error_response('Error de validación', $validator->errors(), 422)
        );
    }
}