<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Registar sredstava za zaštitu bilja iz javne FIS tražilice (Ministarstvo), osvježava se naredbom szb:registar
        Schema::create('szb_sredstva', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('fis_id')->unique();
            $table->string('naziv')->index();
            $table->string('registracijski_broj')->nullable();
            $table->string('nositelj')->nullable();
            $table->string('stari_naziv')->nullable();
            $table->boolean('aktivno')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('szb_sredstva');
    }
};
