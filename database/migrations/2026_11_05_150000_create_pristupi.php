<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Gospodarstvo (vlasnik) poziva savjetnika e-mailom; savjetnik prihvaća poveznicom iz maila
        Schema::create('pristupi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vlasnik_id')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->foreignId('savjetnik_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('prava', 10)->default('unos');   // pregled | unos
            $table->string('token', 64)->unique();
            $table->string('status', 12)->default('poslano'); // poslano | prihvaceno | opozvano
            $table->timestamp('prihvaceno_at')->nullable();
            $table->timestamp('zadnji_rad_at')->nullable();
            $table->timestamps();
            $table->index(['savjetnik_id', 'status']);
        });

        // Povijest izmjena koje je savjetnik napravio u gospodarstvu
        Schema::create('pristup_izmjene', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pristup_id')->nullable()->constrained('pristupi')->nullOnDelete();
            $table->foreignId('vlasnik_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('savjetnik_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('savjetnik_ime');
            $table->string('opis');
            $table->json('tablice')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['vlasnik_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pristup_izmjene');
        Schema::dropIfExists('pristupi');
    }
};
