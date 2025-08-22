<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->json('payload'); // Guarda el JSON completo
            $table->boolean('processed')->default(false); // Flag de procesamiento
            $table->timestamp('received_at')->useCurrent(); // Fecha de recepción
            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};