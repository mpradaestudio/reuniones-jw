<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índices recomendados para la consulta de auditoría (módulo Auditoría, PR A).
 *
 * La pantalla de auditoría ordena por fecha y filtra por congregación + fecha.
 * Estos índices soportan esos accesos sin escaneos completos:
 *  - `created_at`               → orden/filtrado temporal global (SuperAdministrador).
 *  - `congregation_id, created_at` → listado aislado por congregación ordenado por fecha.
 *
 * `event`, `auditable_type+auditable_id` ya están indexados en la migración de
 * creación. `user_id` y `congregation_id` ya disponen de índice por su clave
 * foránea (MySQL), por lo que no se duplican aquí.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('created_at', 'audit_logs_created_at_index');
            $table->index(['congregation_id', 'created_at'], 'audit_logs_congregation_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_logs_created_at_index');
            $table->dropIndex('audit_logs_congregation_created_index');
        });
    }
};
