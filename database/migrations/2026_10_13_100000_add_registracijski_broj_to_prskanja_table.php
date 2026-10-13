<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prskanja', function (Blueprint $table) {
            // Registracijski broj sredstva — obvezni podatak evidencije prema Provedbenoj uredbi (EU) 2023/564
            $table->string('registracijski_broj', 60)->nullable()->after('trgovacki_naziv_sredstva');
        });
    }

    public function down(): void
    {
        Schema::table('prskanja', function (Blueprint $table) {
            $table->dropColumn('registracijski_broj');
        });
    }
};
