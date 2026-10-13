<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kulture', function (Blueprint $table) {
            // Kultura je završena (požnjevena / zamijenjena drugom) — ostaje radi evidencije, ali se ne nudi za nove zapise
            $table->date('zavrsena_at')->nullable()->after('godina');
        });
    }

    public function down(): void
    {
        Schema::table('kulture', function (Blueprint $table) {
            $table->dropColumn('zavrsena_at');
        });
    }
};
