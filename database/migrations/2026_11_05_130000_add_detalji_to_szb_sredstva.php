<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Detalji registracije iz FIS-a (kulture, karenca, najveći broj tretmana, način primjene) — dohvaćaju se po potrebi
        Schema::table('szb_sredstva', function (Blueprint $table) {
            $table->json('detalji')->nullable()->after('aktivno');
            $table->timestamp('detalji_at')->nullable()->after('detalji');
        });
    }

    public function down(): void
    {
        Schema::table('szb_sredstva', fn (Blueprint $table) => $table->dropColumn(['detalji', 'detalji_at']));
    }
};
