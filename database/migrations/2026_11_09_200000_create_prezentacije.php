<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Javna prezentacija gospodarstva na /gospodarstva: vidljiva tek kad vlasnik uključi i admin odobri.
        Schema::create('prezentacije', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('vidljivo')->default(false);
            $table->timestamp('odobreno_at')->nullable();
            $table->string('odbijeno_razlog', 300)->nullable();
            $table->string('slug', 120)->unique();
            $table->string('naziv', 120);
            $table->string('mjesto', 80);
            $table->string('zupanija', 40);
            $table->text('opis');
            $table->json('oznake')->nullable();
            $table->string('telefon', 30);
            $table->boolean('whatsapp')->default(false);
            $table->boolean('viber')->default(false);
            $table->string('email', 150)->nullable();
            $table->string('adresa', 200)->nullable();
            $table->boolean('kucni_prag')->default(false);
            $table->string('radno_vrijeme', 150)->nullable();
            $table->string('web', 200)->nullable();
            $table->string('facebook', 200)->nullable();
            $table->string('instagram', 200)->nullable();
            $table->string('slika', 120)->nullable();
            $table->decimal('lat', 8, 2)->nullable();   // približno (2 decimale ≈ 1 km), iz parcela
            $table->decimal('lng', 8, 2)->nullable();
            $table->timestamps();
            $table->index(['vidljivo', 'odobreno_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prezentacije');
    }
};
