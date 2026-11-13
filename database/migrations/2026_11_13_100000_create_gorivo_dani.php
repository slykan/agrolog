<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gorivo po danu: jedan unos po danu (ukupno natočeno), raspodjela na poslove tog dana računa se pri prikazu.
 * users.prati_gorivo: popup nakon prskanja/gnojidbe (isključivo u Profilu); users.gorivo_kasnije: dan za koji je unos odgođen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gorivo_dani', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('datum');
            $table->decimal('litara', 10, 2)->nullable();
            $table->decimal('cijena_l', 8, 3)->nullable();     // €/L
            $table->decimal('iznos', 12, 2);                    // ukupno €
            $table->json('izuzeto')->nullable();                // ključevi poslova koji nisu trošili ovo gorivo, npr. "prskanje:12"
            $table->string('napomena', 300)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'datum']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('prati_gorivo')->default(true);
            $table->date('gorivo_kasnije')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['prati_gorivo', 'gorivo_kasnije']));
        Schema::dropIfExists('gorivo_dani');
    }
};
