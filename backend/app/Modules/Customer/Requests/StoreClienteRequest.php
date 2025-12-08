<?php

namespace Modules\Customer\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_completo' => ['required', 'string', 'max:150'],
            'nit' => ['nullable', 'string', 'max:50', 'unique:clientes,nit'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'max:150'],
            'es_frecuente' => ['boolean'],
            'limite_credito' => ['nullable', 'numeric', 'min:0'],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_completo.required' => 'El nombre completo es obligatorio',
            'nombre_completo.max' => 'El nombre no puede superar 150 caracteres',
            'nit.unique' => 'Este NIT ya está registrado',
            'email.email' => 'Debe ser un correo electrónico válido',
            'limite_credito.min' => 'El límite de crédito no puede ser negativo',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            error_response('Error de validación', $validator->errors(), 422)
        );
    }
}