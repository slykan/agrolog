<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skladište je neobavezno: uključuje se u profilu (zadano isključeno)
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('koristi_skladiste')->default(false)->after('obavijesti_odjava_at');
        });

        // Ulazi na skladište (nabava ili korekcija stanja). Potrošnja se ne sprema: računa se iz
        // evidencije prskanja i gnojidbe (doza × površina), pa izmjena ili brisanje zapisa samo ispravi stanje.
        Schema::create('skladiste_ulazi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('vrsta', 10);            // szb | gnojivo
            $table->string('naziv', 200);
            $table->date('datum');
            $table->decimal('kolicina', 12, 3);     // u osnovnoj jedinici (L ili kg); korekcija može biti negativna
            $table->string('jedinica', 5);          // L | kg
            $table->decimal('cijena', 12, 2)->nullable();   // ukupno €, za ovaj ulaz
            $table->string('dobavljac', 150)->nullable();
            $table->string('napomena', 255)->nullable();
            $table->boolean('korekcija')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'vrsta', 'naziv']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skladiste_ulazi');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('koristi_skladiste'));
    }
};
