<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Prinos i prihod kulture (za dobit po hektaru na stranici Troškovi) te troškovi kojih nema u evidenciji
        Schema::table('kulture', function (Blueprint $table) {
            $table->date('zetva_datum')->nullable()->after('zavrsena_at');
            $table->decimal('prinos_t_ha', 8, 3)->nullable()->after('zetva_datum');
            $table->decimal('cijena_t', 10, 2)->nullable()->after('prinos_t_ha');      // €/t
            $table->decimal('potpora_ha', 10, 2)->nullable()->after('cijena_t');       // €/ha, potpore
            $table->decimal('trosak_sjeme', 10, 2)->nullable()->after('potpora_ha');   // € ukupno
            $table->decimal('trosak_ostalo', 10, 2)->nullable()->after('trosak_sjeme'); // € ukupno (usluge, zakup...)
        });
    }

    public function down(): void
    {
        Schema::table('kulture', fn (Blueprint $table) => $table->dropColumn(['zetva_datum', 'prinos_t_ha', 'cijena_t', 'potpora_ha', 'trosak_sjeme', 'trosak_ostalo']));
    }
};
