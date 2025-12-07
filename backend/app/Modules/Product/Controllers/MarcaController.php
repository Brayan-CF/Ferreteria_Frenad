<?php

namespace Modules\Product\Controllers;

use App\Http\Controllers\Controller;
use Modules\Product\Models\Marca;
use Modules\Product\Requests\StoreMarcaRequest;
use Modules\Product\Services\MarcaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class MarcaController extends Controller
{
    protected MarcaService $marcaService;

    public function __construct(MarcaService $marcaService)
    {
        $this->marcaService = $marcaService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['activo', 'search', 'per_page']);
            $marcas = $this->marcaService->list($filters);
            return success_response($marcas);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    public function store(StoreMarcaRequest $request): JsonResponse
    {
        try {
            $marca = $this->marcaService->create($request->validated());
            return success_response($marca, 'Marca creada exitosamente', 201);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    public function show(Marca $marca): JsonResponse
    {
        try {
            $marca->loadCount('productos');
            return success_response($marca);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    public function update(Request $request, Marca $marca): JsonResponse
    {
        try {
            $marcaActualizada = $this->marcaService->update($marca, $request->all());
            return success_response($marcaActualizada, 'Marca actualizada exitosamente');

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    public function destroy(Marca $marca): JsonResponse
    {
        try {
            $this->marcaService->delete($marca);
            return success_response(null, 'Marca desactivada exitosamente');

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }
}