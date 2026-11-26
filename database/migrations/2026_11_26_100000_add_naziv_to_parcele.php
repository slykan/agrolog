<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vlastiti naziv ARKOD parcele (npr. "Iza kuće", "Kod bunara") — prikazuje se uz ARKOD broj u svim popisima.
 * Ručno ucrtana parcela ga ne koristi: njezin naziv je već u arkod_broj.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parcele', function (Blueprint $table) {
            $table->string('naziv', 60)->nullable()->after('arkod_broj');
        });
    }

    public function down(): void
    {
        Schema::table('parcele', function (Blueprint $table) {
            $table->dropColumn('naziv');
        });
    }
};
