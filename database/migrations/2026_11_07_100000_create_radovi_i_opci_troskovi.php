<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ostali radovi na parceli (oranje, rezidba, košnja...): vlastiti rad (gorivo + rad) ili usluga. `trosak` je izračunati zbroj.
        Schema::create('radovi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kultura_id')->constrained('kulture')->cascadeOnDelete();
            $table->date('datum');
            $table->string('vrsta', 100);
            $table->string('izvedba', 10)->default('vlastito');   // vlastito | usluga
            $table->foreignId('stroj_id')->nullable()->constrained('strojevi')->nullOnDelete();
            $table->decimal('povrsina_ha', 8, 2)->nullable();
            $table->decimal('sati', 8, 2)->nullable();             // sati rada stroja
            $table->decimal('gorivo_l', 10, 2)->nullable();
            $table->decimal('cijena_goriva', 8, 3)->nullable();    // €/L
            $table->decimal('radni_sati', 8, 2)->nullable();       // radnici × sati
            $table->decimal('cijena_sata', 8, 2)->nullable();      // €/h
            $table->decimal('usluga_cijena', 12, 2)->nullable();
            $table->string('usluga_po', 8)->nullable();            // ukupno | ha
            $table->string('izvodac', 150)->nullable();
            $table->decimal('trosak', 12, 2)->nullable();
            $table->string('napomena', 500)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'datum']);
        });

        // Spremljene rute za vožnju (npr. "Voćnjak, 25 km")
        Schema::create('rute', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('naziv', 100);
            $table->decimal('km', 8, 1);
            $table->boolean('povratno')->default(true);
            $table->timestamps();
        });

        // Opći troškovi gospodarstva: vožnja, zakup, osiguranje... Kultura je neobavezna (tada ulazi i u €/ha te kulture).
        // Savjetnik može vezati trošak na klijenta (klijent_id = vlasnik gospodarstva).
        Schema::create('opci_troskovi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('datum');
            $table->string('vrsta', 100);
            $table->string('opis', 300)->nullable();
            $table->decimal('km', 8, 1)->nullable();
            $table->boolean('povratno')->default(false);
            $table->decimal('cijena_km', 6, 3)->nullable();
            $table->decimal('iznos', 12, 2);
            $table->foreignId('kultura_id')->nullable()->constrained('kulture')->nullOnDelete();
            $table->foreignId('klijent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'datum']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opci_troskovi');
        Schema::dropIfExists('rute');
        Schema::dropIfExists('radovi');
    }
};
