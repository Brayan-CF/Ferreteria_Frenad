<?php

namespace Modules\Product\Controllers;

use App\Http\Controllers\Controller;
use Modules\Product\Models\Categoria;
use Modules\Product\Requests\StoreCategoriaRequest;
use Modules\Product\Services\CategoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class CategoriaController extends Controller
{
    protected CategoriaService $categoriaService;

    public function __construct(CategoriaService $categoriaService)
    {
        $this->categoriaService = $categoriaService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['activo', 'search', 'per_page']);
            $categorias = $this->categoriaService->list($filters);
            return success_response($categorias);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    public function store(StoreCategoriaRequest $request): JsonResponse
    {
        try {
            $categoria = $this->categoriaService->create($request->validated());
            return success_response($categoria, 'Categoría creada exitosamente', 201);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    public function show(Categoria $categoria): JsonResponse
    {
        try {
            $categoria->loadCount('productos');
            return success_response($categoria);

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    public function update(Request $request, Categoria $categoria): JsonResponse
    {
        try {
            $categoriaActualizada = $this->categoriaService->update($categoria, $request->all());
            return success_response($categoriaActualizada, 'Categoría actualizada exitosamente');

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    public function destroy(Categoria $categoria): JsonResponse
    {
        try {
            $this->categoriaService->delete($categoria);
            return success_response(null, 'Categoría desactivada exitosamente');

        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }
}