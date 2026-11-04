<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Obavijesti koje admin šalje mailom svim korisnicima (Admin → Obavijesti)
        Schema::create('obavijesti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();   // tko je poslao
            $table->string('predmet');
            $table->string('naslov');
            $table->text('tekst');
            $table->string('gumb_tekst')->nullable();
            $table->string('gumb_url')->nullable();
            $table->unsignedInteger('primatelja')->default(0);
            $table->timestamp('poslano_at')->nullable();
            $table->timestamps();
        });

        // korisnik se može odjaviti od obavijesti (link u svakom mailu)
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('obavijesti_odjava_at')->nullable()->after('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('obavijesti_odjava_at');
        });
        Schema::dropIfExists('obavijesti');
    }
};
