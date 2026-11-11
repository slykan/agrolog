<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Planirani posao u kalendaru. Nije evidencija: zapis (prskanje, gnojidba...) nastaje tek kad se posao obavi i upiše.
        Schema::create('planirani_radovi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kultura_id')->nullable()->constrained('kulture')->nullOnDelete();
            $table->date('datum');
            $table->string('vrsta', 20);                 // prskanje | gnojidba | berba | rad | ostalo
            $table->string('opis', 200)->nullable();     // sredstvo, gnojivo ili vrsta rada
            $table->string('napomena', 500)->nullable();
            $table->timestamp('obavljeno_at')->nullable();
            $table->string('zapis_vrsta', 20)->nullable();
            $table->unsignedBigInteger('zapis_id')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'datum']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planirani_radovi');
    }
};
