<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Berba / žetva: više zapisa po kulturi. Količina i cijena čuvaju se po kg, jedinica pamti kako je korisnik upisao.
        Schema::create('berbe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kultura_id')->constrained('kulture')->cascadeOnDelete();
            $table->date('datum');
            $table->decimal('kolicina_kg', 12, 2);
            $table->string('jedinica', 2)->default('kg');
            $table->decimal('cijena_kg', 10, 4)->nullable();
            $table->decimal('povrsina_ha', 8, 2)->nullable();
            $table->string('sorta', 100)->nullable();
            $table->string('klasa', 50)->nullable();
            $table->string('kupac', 150)->nullable();
            $table->string('napomena', 500)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'datum']);
        });

        // Trajni nasad: voćnjak, vinograd, maslinik. Ostaje na parceli iz godine u godinu.
        Schema::table('kulture', function (Blueprint $table) {
            $table->boolean('trajni')->default(false)->after('godina');
            $table->string('sorta', 100)->nullable()->after('trajni');
            $table->unsignedSmallInteger('godina_sadnje')->nullable()->after('sorta');
            $table->unsignedInteger('broj_stabala')->nullable()->after('godina_sadnje');
        });

        // Potpore i ostali troškovi trajnog nasada po godinama
        Schema::create('nasad_godine', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kultura_id')->constrained('kulture')->cascadeOnDelete();
            $table->unsignedSmallInteger('godina');
            $table->decimal('potpora_ha', 10, 2)->nullable();
            $table->decimal('trosak_ostalo', 12, 2)->nullable();
            $table->timestamps();
            $table->unique(['kultura_id', 'godina']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nasad_godine');
        Schema::table('kulture', function (Blueprint $table) {
            $table->dropColumn(['trajni', 'sorta', 'godina_sadnje', 'broj_stabala']);
        });
        Schema::dropIfExists('berbe');
    }
};
