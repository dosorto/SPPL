<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use App\Models\Nominas;

class NominaExport implements FromView
{
    protected $nomina;
    protected $mesNombre;
    protected $tipoPagoNombre;
    protected $empleados;
    protected $totalNomina;

    public function __construct(Nominas $nomina, $mesNombre, $tipoPagoNombre, $empleados, $totalNomina)
    {
        $this->nomina = $nomina;
        $this->mesNombre = $mesNombre;
        $this->tipoPagoNombre = $tipoPagoNombre;
        $this->empleados = $empleados;
        $this->totalNomina = $totalNomina;
    }

    public function view(): View
    {
        return view('excel.nomina', [
            'nomina' => $this->nomina,
            'empresa' => $this->nomina->empresa,
            'mesNombre' => $this->mesNombre,
            'tipoPagoNombre' => $this->tipoPagoNombre,
            'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
            'empleados' => $this->empleados,
            'totalNomina' => $this->totalNomina,
        ]);
    }
}
