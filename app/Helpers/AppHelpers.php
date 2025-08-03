<?php

/**
 * Devuelve el ID de la empresa actual según la sesión o el usuario autenticado
 *
 * @return int|null
 */
function empresa_actual()
{
    // Primero intentar obtener de la sesión
    $empresaId = session('current_empresa_id');
    
    // Si no está en sesión y hay un usuario autenticado que no es root, obtener de su empresa
    if (!$empresaId && auth()->check() && !auth()->user()->hasRole('root')) {
        $empresaId = auth()->user()->empresa_id;
    }
    
    // Para usuario root, si no hay empresa seleccionada, devolver null para que no aplique filtro
    return $empresaId;
}

/**
 * Determina si se están mostrando datos de todas las empresas (solo root)
 *
 * @return bool
 */
function mostrando_todas_empresas()
{
    return auth()->check() && 
           auth()->user()->hasRole('root') && 
           !session('current_empresa_id');
}
