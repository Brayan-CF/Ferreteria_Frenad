<?php

namespace Modules\Customer\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Modules\Customer\Models\PagoCredito;

class RegistrarPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'monto_pago' => ['required', 'numeric', 'min:0.01'],
            'metodo_pago' => ['required', 'string', Rule::in(PagoCredito::metodosDisponibles())],
            'notas' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'monto_pago.required' => 'El monto del pago es obligatorio',
            'monto_pago.min' => 'El monto debe ser mayor a 0',
            'metodo_pago.required' => 'Debe seleccionar un método de pago',
            'metodo_pago.in' => 'Método de pago no válido',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            error_response('Error de validación', $validator->errors(), 422)
        );
    }
}
