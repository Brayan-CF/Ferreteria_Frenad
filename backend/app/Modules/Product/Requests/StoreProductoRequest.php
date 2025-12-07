<?php

namespace Modules\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => ['nullable', 'string', 'max:50', 'unique:productos,sku'],
            'codigo_barras' => ['nullable', 'string', 'max:100', 'unique:productos,codigo_barras'],
            'nombre' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'marca_id' => ['nullable', 'integer', 'exists:marcas,id'],
            'unidad_base_id' => ['required', 'integer', 'exists:unidades_medida,id'],
            'precio_compra' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0', 'gte:precio_compra'],
            'fecha_vencimiento' => ['nullable', 'date', 'after:today'],
            'ubicacion_fisica' => ['nullable', 'string', 'max:100'],
            'activo' => ['boolean'],
            'unidades' => ['nullable', 'array'],
            'unidades.*.unidad_id' => ['required', 'integer', 'exists:unidades_medida,id'],
            'unidades.*.factor_conversion' => ['required', 'numeric', 'min:0.0001'],
            'unidades.*.es_unidad_compra' => ['boolean'],
            'unidades.*.es_unidad_venta' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio',
            'categoria_id.required' => 'Debe seleccionar una categoría',
            'categoria_id.exists' => 'La categoría seleccionada no existe',
            'unidad_base_id.required' => 'Debe seleccionar una unidad de medida',
            'precio_compra.required' => 'El precio de compra es obligatorio',
            'precio_compra.min' => 'El precio de compra debe ser mayor o igual a 0',
            'precio_venta.required' => 'El precio de venta es obligatorio',
            'precio_venta.gte' => 'El precio de venta debe ser mayor o igual al precio de compra',
            'codigo_barras.unique' => 'Este código de barras ya está registrado',
            'sku.unique' => 'Este SKU ya está registrado',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            error_response('Error de validación', $validator->errors(), 422)
        );
    }
}