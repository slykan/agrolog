<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Parcela bez ARKOD-a: naziv je u arkod_broj (popisi, pretraga i sortiranje rade isto), a oblik u arkod_poligon
        Schema::table('parcele', function (Blueprint $table) {
            $table->boolean('rucno_ucrtana')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('parcele', function (Blueprint $table) {
            $table->dropColumn('rucno_ucrtana');
        });
    }
};
