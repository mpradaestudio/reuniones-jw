<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla raíz del modelo multi-congregación.
     */
    public function up(): void
    {
        Schema::create('congregations', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('subdominio', 100)->unique();
            $table->enum('estado', ['active', 'inactive', 'suspended'])
                ->default('active')
                ->index();
            $table->timestamps();
            $table->softDeletes(); // deleted_at: sin borrado físico, mantiene historial
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('congregations');
    }
};
