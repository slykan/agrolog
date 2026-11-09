<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // kvačica s registracije "želim predstaviti gospodarstvo"; stranica s obrascem nudi se jednom nakon registracije
            $table->boolean('zeli_prezentaciju')->default(false);
            $table->timestamp('prezentacija_ponudjena_at')->nullable();
            // admin je korisnika pozvao mailom da se predstavi na popisu (najviše jednom)
            $table->timestamp('prezentacija_pozvan_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['zeli_prezentaciju', 'prezentacija_ponudjena_at', 'prezentacija_pozvan_at']);
        });
    }
};
