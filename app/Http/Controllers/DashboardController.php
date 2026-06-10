<?php

namespace App\Http\Controllers;

use App\Enums\CongregationStatus;
use App\Enums\UserStatus;
use App\Models\Congregation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard con métricas. El SuperAdministrador ve totales globales;
     * el resto de usuarios ve métricas acotadas a su congregación.
     */
    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            $metrics = [
                'congregations_total' => Congregation::count(),
                'congregations_active' => Congregation::where('estado', CongregationStatus::Active)->count(),
                'users_total' => User::count(),
                'users_active' => User::where('estado', UserStatus::Active)->count(),
            ];
        } else {
            $congregationId = $user->congregation_id;

            $metrics = [
                'congregations_total' => 1,
                'congregations_active' => Congregation::whereKey($congregationId)
                    ->where('estado', CongregationStatus::Active)
                    ->count(),
                'users_total' => User::where('congregation_id', $congregationId)->count(),
                'users_active' => User::where('congregation_id', $congregationId)
                    ->where('estado', UserStatus::Active)
                    ->count(),
            ];
        }

        return view('dashboard', compact('metrics'));
    }
}
