// app/Http/Controllers/OrdenCompraController.php
public function edit($id)
{
    $ordenCompra = OrdenCompra::findOrFail($id);
    return view('ordenes.edit', compact('ordenCompra'));
}
