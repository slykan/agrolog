<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // zapisnik ponuda poslanih mogućim oglašivačima, jedna po jedna
        Schema::create('ponude_oglasivacima', function (Blueprint $table) {
            $table->id();
            $table->string('tvrtka', 150);
            $table->string('email', 190)->index();
            $table->string('proizvodi', 150);
            $table->string('predmet', 150);
            $table->timestamp('poslano_at');
            $table->timestamp('ne_zeli_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ponude_oglasivacima');
    }
};
