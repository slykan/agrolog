<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prskanja', function (Blueprint $table) {
            $table->decimal('tretirana_povrsina_ha', 8, 2)->nullable()->change();
            $table->string('trgovacki_naziv_sredstva')->nullable()->change();
            $table->decimal('kolicina_sredstva_l_ha', 8, 3)->nullable()->change();
        });

        Schema::table('gnojidbe', function (Blueprint $table) {
            $table->string('tip_gnojiva')->nullable()->change();
            $table->decimal('kolicina_kg_ha', 8, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('prskanja', function (Blueprint $table) {
            $table->decimal('tretirana_povrsina_ha', 8, 2)->nullable(false)->change();
            $table->string('trgovacki_naziv_sredstva')->nullable(false)->change();
            $table->decimal('kolicina_sredstva_l_ha', 8, 3)->nullable(false)->change();
        });

        Schema::table('gnojidbe', function (Blueprint $table) {
            $table->string('tip_gnojiva')->nullable(false)->change();
            $table->decimal('kolicina_kg_ha', 8, 2)->nullable(false)->change();
        });
    }
};
