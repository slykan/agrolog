<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Preporuka agronoma gospodarstvu. Jedan redak po kulturi (ili jedan za opću poruku); paket veže isto slanje.
        Schema::create('preporuke', function (Blueprint $table) {
            $table->id();
            $table->string('paket', 36)->index();
            $table->foreignId('savjetnik_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('savjetnik_ime');   // ostaje kao trag i kad savjetnik obriše račun
            $table->foreignId('vlasnik_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kultura_id')->nullable()->constrained('kulture')->nullOnDelete();
            $table->string('vrsta', 12);       // prskanje | gnojidba | poruka
            $table->string('parcela_oznaka')->nullable();
            $table->string('kultura_naziv')->nullable();
            $table->string('sredstvo', 200)->nullable();
            $table->decimal('doza', 12, 3)->nullable();
            $table->string('jedinica', 10)->nullable();
            $table->date('rok')->nullable();
            $table->text('napomena')->nullable();
            $table->json('provjera')->nullable();   // upozorenja iz registra u trenutku slanja
            $table->string('status', 20)->default('poslano');
            $table->text('pitanje')->nullable();
            $table->text('odgovor_savjetnika')->nullable();
            $table->timestamp('odgovoreno_at')->nullable();
            $table->timestamp('savjetnik_vidio_at')->nullable();
            $table->unsignedBigInteger('zapis_id')->nullable();   // prskanje ili gnojidba nastala iz preporuke
            $table->timestamps();
            $table->index(['vlasnik_id', 'status']);
            $table->index(['savjetnik_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preporuke');
    }
};
