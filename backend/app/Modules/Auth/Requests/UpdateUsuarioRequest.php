<?php

namespace Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Modules\Auth\Models\Rol;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $usuarioId = $this->route('usuario')?->id ?? $this->route('id');

        return [
            'nombre' => ['sometimes', 'string', 'max:100'],
            'email' => [
                'sometimes',
                'email',
                'max:150',
                Rule::unique('usuarios', 'email')->ignore($usuarioId)
            ],
            'password' => ['sometimes', 'nullable', 'string', 'min:6', 'confirmed'],
            'activo' => ['sometimes', 'boolean'],
            'roles' => ['sometimes', 'array', 'min:1'],
            'roles.*' => ['required', 'string', Rule::in(Rol::rolesDisponibles())],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.max' => 'El nombre no puede superar 100 caracteres',
            'email.email' => 'Debe ser un correo válido',
            'email.unique' => 'Este correo ya está registrado',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'roles.min' => 'Debe asignar al menos un rol',
            'roles.*.in' => 'Rol no válido',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            error_response('Error de validación', $validator->errors(), 422)
        );
    }
}