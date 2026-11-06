<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('je_savjetnik')->default(false);
        });

        // tko već radi kao savjetnik, dobiva oznaku odmah
        DB::table('users')
            ->whereIn('id', DB::table('pristupi')->where('status', 'prihvaceno')->whereNotNull('savjetnik_id')->select('savjetnik_id'))
            ->update(['je_savjetnik' => true]);

        // Savjetnik poziva poljoprivrednika, koji možda još nema račun. Pristup nastaje tek kad poljoprivrednik prihvati.
        Schema::create('pozivi_gospodarstvima', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savjetnik_id')->constrained('users')->cascadeOnDelete();
            $table->string('email', 190);
            $table->string('token', 64)->unique();
            $table->string('status', 12)->default('poslano'); // poslano | prihvaceno | odbijeno | povuceno
            $table->unsignedTinyInteger('broj_slanja')->default(1);
            $table->timestamp('poslano_at')->nullable();
            $table->timestamp('odgovoreno_at')->nullable();
            $table->timestamps();
            $table->unique(['savjetnik_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pozivi_gospodarstvima');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('je_savjetnik');
        });
    }
};
