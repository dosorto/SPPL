<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdenProduccionInsumo;
use App\Http\Resources\OrdenProduccionInsumoResource;

class OrdenProduccionInsumoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $insumos = OrdenProduccionInsumo::with('insumo')->paginate(15);
        return OrdenProduccionInsumoResource::collection($insumos);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'orden_produccion_id' => 'required|exists:ordenes_produccion,id',
            'insumo_id' => 'required|exists:productos,id',
            'cantidad_utilizada' => 'required|integer|min:1',
        ]);
        $insumo = OrdenProduccionInsumo::create($data);
        // Aquí deberías descontar el insumo del inventario si aplica
        return new OrdenProduccionInsumoResource($insumo->load('insumo'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $insumo = OrdenProduccionInsumo::with('insumo')->findOrFail($id);
        return new OrdenProduccionInsumoResource($insumo);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $insumo = OrdenProduccionInsumo::findOrFail($id);
        $data = $request->validate([
            'cantidad_utilizada' => 'sometimes|integer|min:1',
        ]);
        $insumo->update($data);
        return new OrdenProduccionInsumoResource($insumo->load('insumo'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $insumo = OrdenProduccionInsumo::findOrFail($id);
        $insumo->delete();
        return response()->json(['message' => 'Insumo eliminado correctamente.']);
    }
}
