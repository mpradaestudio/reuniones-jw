<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Controlador temporal para los módulos del menú cuyo CRUD aún no se implementa.
 * Permite validar la navegación, los permisos y el layout antes de construir
 * la lógica de negocio.
 */
class PlaceholderController extends Controller
{
    public function congregations(): View
    {
        return view('placeholder', [
            'title' => 'Congregaciones',
            'description' => 'El módulo de gestión de congregaciones se implementará próximamente.',
        ]);
    }

    public function users(): View
    {
        return view('placeholder', [
            'title' => 'Usuarios',
            'description' => 'El módulo de gestión de usuarios se implementará próximamente.',
        ]);
    }

    public function roles(): View
    {
        return view('placeholder', [
            'title' => 'Roles',
            'description' => 'El módulo de gestión de roles y permisos se implementará próximamente.',
        ]);
    }

    public function settings(): View
    {
        return view('placeholder', [
            'title' => 'Configuración',
            'description' => 'La configuración del sistema se implementará próximamente.',
        ]);
    }
}
