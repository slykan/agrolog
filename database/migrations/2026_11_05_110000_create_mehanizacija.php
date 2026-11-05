<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mehanizacija je neobavezna: koristi se čim korisnik doda prvi stroj
        Schema::create('strojevi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('vrsta', 20);                 // traktor, kombajn, prskalica...
            $table->string('naziv', 150);                // npr. John Deere 6120M
            $table->string('registracija', 30)->nullable();
            $table->unsignedSmallInteger('godina')->nullable();
            $table->string('serijski_broj', 60)->nullable();
            $table->string('napomena', 255)->nullable();
            $table->timestamps();
        });

        // Rokovi (registracija, tehnički, osiguranje, servis, pregled prskalice...) s podsjetnikom mailom
        Schema::create('stroj_rokovi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stroj_id')->constrained('strojevi')->cascadeOnDelete();
            $table->string('vrsta', 20);
            $table->string('naziv', 100)->nullable();    // za vrstu "ostalo"
            $table->date('datum_isteka');
            $table->unsignedSmallInteger('podsjetnik_dana')->default(30);
            $table->decimal('iznos', 10, 2)->nullable();
            $table->string('napomena', 255)->nullable();
            $table->timestamp('podsjetnik_poslan_at')->nullable();
            $table->timestamp('istek_poslan_at')->nullable();
            $table->timestamp('obnovljen_at')->nullable();   // obnovljeni rok ostaje u povijesti
            $table->timestamps();
            $table->index(['obnovljen_at', 'datum_isteka']);
        });

        // Servisna knjiga
        Schema::create('stroj_servisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stroj_id')->constrained('strojevi')->cascadeOnDelete();
            $table->date('datum');
            $table->string('opis', 255);
            $table->unsignedInteger('radni_sati')->nullable();
            $table->decimal('trosak', 10, 2)->nullable();
            $table->string('serviser', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stroj_servisi');
        Schema::dropIfExists('stroj_rokovi');
        Schema::dropIfExists('strojevi');
    }
};
