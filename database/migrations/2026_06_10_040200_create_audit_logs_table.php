<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registro de auditoría, creado desde el MVP para trazabilidad futura.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Tenant: NULL = acción global del SuperAdministrador.
            $table->foreignId('congregation_id')
                ->nullable()
                ->constrained('congregations')
                ->nullOnDelete();

            // Autor de la acción.
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('event', 50); // created, updated, deleted, login, logout...

            // Modelo afectado (relación polimórfica).
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index('event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
