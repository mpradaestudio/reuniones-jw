<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade a la tabla de roles:
 *  - is_system: marca los roles protegidos del sistema (no renombrables ni
 *    eliminables): SuperAdministrador, AdministradorCongregacion, Usuario.
 *  - description: descripción opcional para la UI.
 */
return new class extends Migration
{
    private function table(): string
    {
        return config('permission.table_names.roles', 'roles');
    }

    public function up(): void
    {
        Schema::table($this->table(), function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('guard_name');
            $table->string('description')->nullable()->after('is_system');
        });
    }

    public function down(): void
    {
        Schema::table($this->table(), function (Blueprint $table) {
            $table->dropColumn(['is_system', 'description']);
        });
    }
};
