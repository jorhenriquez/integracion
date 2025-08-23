<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('webhook_events', function (Blueprint $table) {
            $table->string('observaciones')->nullable()->after('id'); // Puedes cambiar 'id' por el campo que prefieras
            $table->unsignedTinyInteger('estado')->default(0)->after('processed'); // 0: pendiente, 1: procesado, 2: error
        });
    }

    public function down()
    {
        Schema::table('webhook_events', function (Blueprint $table) {
            $table->dropColumn('observaciones');
        });
    }
};
