<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Persona;

class DiagnosticarPersona extends Command
{
    protected $signature = 'diagnosticar:persona {id?}';
    protected $description = 'Diagnosticar datos de persona para edición';

    public function handle()
    {
        $id = $this->argument('id') ?? 1;
        
        $this->info("=== DIAGNÓSTICO PERSONA ID: $id ===");
        
        $persona = Persona::find($id);
        
        if (!$persona) {
            $this->error('Persona no encontrada');
            return;
        }
        
        $this->info("Persona encontrada:");
        $this->info("- ID: {$persona->id}");
        $this->info("- DNI: '{$persona->dni}'");
        $this->info("- Primer Nombre: '{$persona->primer_nombre}'");
        $this->info("- Primer Apellido: '{$persona->primer_apellido}'");
        $this->info("- Tipo Persona: '{$persona->tipo_persona}'");
        
        $this->info("\n=== VERIFICACIONES ===");
        $this->info("DNI type: " . gettype($persona->dni));
        $this->info("DNI empty(): " . (empty($persona->dni) ? 'true' : 'false'));
        $this->info("DNI === null: " . ($persona->dni === null ? 'true' : 'false'));
        $this->info("DNI length: " . strlen($persona->dni));
        
        // Simular lo que haría el formulario de edición
        $formData = $persona->toArray();
        $this->info("\n=== DATOS PARA FORMULARIO ===");
        $this->info("DNI en toArray(): " . json_encode($formData['dni']));
    }
}
