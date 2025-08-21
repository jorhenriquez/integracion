<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('comuna_catalog', function (Blueprint $table) {
            $table->unsignedSmallInteger('codcd')->primary(); // CODCD como PK
            $table->string('nomcd', 191);
            $table->string('nif', 32);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('comuna_catalog');
    }
};
