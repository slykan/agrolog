<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oglasi', function (Blueprint $table) {
            $table->id();
            $table->string('naziv', 120);
            $table->string('slika', 255);
            $table->unsignedInteger('slika_w');
            $table->unsignedInteger('slika_h');
            $table->string('slika_mobitel', 255)->nullable();
            $table->unsignedInteger('mobitel_w')->nullable();
            $table->unsignedInteger('mobitel_h')->nullable();
            $table->string('url', 500);
            $table->date('od')->nullable();
            $table->date('do')->nullable();
            $table->boolean('na_naslovnoj')->default(true);
            $table->boolean('na_dashboardu')->default(true);
            $table->boolean('na_clancima')->default(true);
            $table->boolean('aktivan')->default(true);
            $table->boolean('rezervni')->default(false);   // samo kad nema drugih oglasa
            $table->timestamps();
        });

        // zbirno po danu: bez korisnika, IP adrese i kolačića
        Schema::create('oglas_statistika', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oglas_id')->constrained('oglasi')->cascadeOnDelete();
            $table->string('mjesto', 20);
            $table->date('datum');
            $table->unsignedInteger('prikazi')->default(0);
            $table->unsignedInteger('klikovi')->default(0);
            $table->unique(['oglas_id', 'mjesto', 'datum']);
        });

        DB::table('oglasi')->insert([
            'naziv' => 'Oglašavajte se u AgroLogu', 'url' => '/kontakt?tema=oglasavanje#poruka', 'rezervni' => true,
            'slika' => 'images/oglasi/oglasavanje.webp', 'slika_w' => 2172, 'slika_h' => 242,
            'slika_mobitel' => 'images/oglasi/oglasavanje-mobitel.webp', 'mobitel_w' => 1080, 'mobitel_h' => 400,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('oglas_statistika');
        Schema::dropIfExists('oglasi');
    }
};
