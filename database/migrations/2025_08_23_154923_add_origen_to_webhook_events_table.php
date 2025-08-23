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
            $table->string('ip')->nullable(); // o después de otro campo relevante
            $table->string('origen')->nullable(); // o después de otro campo relevante
            $table->string('referer')->nullable(); // o después de otro campo relevante
            $table->string('user_agent')->nullable(); // o después de otro campo relevante
        });
    }

    public function down()
    {
        Schema::table('webhook_events', function (Blueprint $table) {
            $table->dropColumn('origen');
        });
    }
};
