<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdenProduccion;
use App\Http\Resources\OrdenProduccionResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrdenProduccionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ordenes = OrdenProduccion::with(['producto', 'empresa', 'insumos.insumo'])->paginate(15);
        return OrdenProduccionResource::collection($ordenes);
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
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'fecha_solicitud' => 'required|date',
            'fecha_entrega' => 'nullable|date',
            'estado' => 'required|in:Pendiente,En Proceso,Finalizada,Cancelada',
            'observaciones' => 'nullable|string',
            'empresa_id' => 'required|exists:empresas,id',
            'insumos' => 'required|array|min:1',
            'insumos.*.insumo_id' => 'required|exists:productos,id',
            'insumos.*.cantidad_utilizada' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $orden = OrdenProduccion::create(array_merge($data, [
                'created_by' => Auth::id(),
            ]));
            foreach ($data['insumos'] as $insumo) {
                $orden->insumos()->create($insumo);
                // Aquí deberías descontar el insumo del inventario
            }
            DB::commit();
            return new OrdenProduccionResource($orden->load(['producto', 'empresa', 'insumos.insumo']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear la orden de producción', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $orden = OrdenProduccion::with(['producto', 'empresa', 'insumos.insumo'])->findOrFail($id);
        return new OrdenProduccionResource($orden);
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
        $orden = OrdenProduccion::findOrFail($id);
        $data = $request->validate([
            'cantidad' => 'sometimes|integer|min:1',
            'fecha_solicitud' => 'sometimes|date',
            'fecha_entrega' => 'nullable|date',
            'estado' => 'sometimes|in:Pendiente,En Proceso,Finalizada,Cancelada',
            'observaciones' => 'nullable|string',
        ]);
        $orden->update(array_merge($data, [
            'updated_by' => Auth::id(),
        ]));
        return new OrdenProduccionResource($orden->load(['producto', 'empresa', 'insumos.insumo']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $orden = OrdenProduccion::findOrFail($id);
        $orden->deleted_by = Auth::id();
        $orden->save();
        $orden->delete();
        return response()->json(['message' => 'Orden de producción eliminada correctamente.']);
    }
}
