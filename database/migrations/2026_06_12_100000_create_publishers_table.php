<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulo Publicadores.
 *
 * Decisiones aplicadas (aprobadas):
 *  - Sin fecha_nacimiento ni notas (excluidas del MVP).
 *  - Sin SoftDeletes: el ciclo de vida se gestiona exclusivamente con el campo
 *    `estado` (activo / irregular / inactivo).
 *  - user_id nullable SET NULL: si se elimina la cuenta de sistema el publicador
 *    persiste (los publicadores pueden existir sin cuenta de usuario).
 *  - congregation_id RESTRICT: la congregación no puede borrarse mientras tenga
 *    publicadores.
 *
 * Índices:
 *  - (congregation_id, estado)           → listado filtrado por estado.
 *  - (congregation_id, apellidos, nombre) → ordenación alfabética paginada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publishers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('congregation_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('nombre', 100);
            $table->string('apellidos', 100);

            $table->enum('genero', ['masculino', 'femenino']);

            $table->date('fecha_bautismo')->nullable();

            $table->enum('estado', ['activo', 'irregular', 'inactivo'])
                ->default('activo');

            $table->enum('privilegio', ['publicador', 'siervo_ministerial', 'anciano'])
                ->default('publicador');

            $table->boolean('es_nombrado')->default(false);

            $table->timestamps();

            // Índices para consultas frecuentes.
            $table->index(['congregation_id', 'estado'],
                'publishers_congregation_estado_index');
            $table->index(['congregation_id', 'apellidos', 'nombre'],
                'publishers_congregation_apellidos_nombre_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publishers');
    }
};
