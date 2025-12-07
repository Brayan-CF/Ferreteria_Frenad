<?php

namespace Modules\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productoId = $this->route('producto')?->id ?? $this->route('id');

        return [
            'codigo_barras' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('productos', 'codigo_barras')->ignore($productoId),
            ],
            'nombre' => ['sometimes', 'string', 'max:200'],
'descripcion' => ['nullable', 'string'],
'categoria_id' => ['sometimes', 'integer', 'exists:categorias,id'],
'marca_id' => ['nullable', 'integer', 'exists:marcas,id'],
'unidad_base_id' => ['sometimes', 'integer', 'exists:unidades_medida,id'],
'precio_compra' => ['sometimes', 'numeric', 'min:0'],
'precio_venta' => ['sometimes', 'numeric', 'min:0'],
'fecha_vencimiento' => ['nullable', 'date'],
'ubicacion_fisica' => ['nullable', 'string', 'max:100'],
            'activo' => ['boolean'],
            'unidades' => ['nullable', 'array'],
            'unidades.*.unidad_id' => ['required_with:unidades', 'integer', 'exists:unidades_medida,id'],
            'unidades.*.factor_conversion' => ['required_with:unidades', 'numeric', 'min:0.0001'],
            'unidades.*.es_unidad_compra' => ['boolean'],
            'unidades.*.es_unidad_venta' => ['boolean'],
        ];
    }public function messages(): array
{
    return [
        'nombre.required' => 'El nombre del producto es obligatorio',
        'categoria_id.exists' => 'La categoría seleccionada no existe',
        'precio_compra.min' => 'El precio de compra debe ser mayor o igual a 0',
        'codigo_barras.unique' => 'Este código de barras ya está registrado',
    ];
}

protected function failedValidation(Validator $validator)
{
    throw new HttpResponseException(
        error_response('Error de validación', $validator->errors(), 422)
    );
}

}