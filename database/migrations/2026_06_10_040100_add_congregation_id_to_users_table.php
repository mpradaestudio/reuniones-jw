<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade la relación de tenant al usuario.
     *
     * congregation_id es NULLABLE a propósito: el SuperAdministrador es un
     * usuario global sin congregación. ON DELETE RESTRICT: no se permite borrar
     * una congregación con usuarios (además se usa SoftDeletes).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('congregation_id')
                ->nullable()
                ->after('id')
                ->constrained('congregations')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['congregation_id']);
            $table->dropColumn('congregation_id');
        });
    }
};
