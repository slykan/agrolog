<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tržnica: proizvodi uz prezentaciju gospodarstva. Javni su kad je prezentacija javna i proizvod dostupan.
        Schema::create('proizvodi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prezentacija_id')->constrained('prezentacije')->cascadeOnDelete();
            $table->string('naziv', 100);
            $table->string('vrsta', 40);                 // jedna od Prezentacija::OZNAKE (filter)
            $table->decimal('cijena', 10, 2);
            $table->string('jedinica', 20);              // kg, L, kom…
            $table->text('opis')->nullable();
            $table->string('slika', 120)->nullable();
            $table->boolean('dostupno')->default(true);
            $table->timestamps();
            $table->index(['dostupno', 'vrsta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proizvodi');
    }
};
