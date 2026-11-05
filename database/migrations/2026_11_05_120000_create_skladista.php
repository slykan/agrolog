<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Više skladišta po korisniku. Skladište se "koristi" čim korisnik doda prvo skladište (nema više prekidača u profilu).
        Schema::create('skladista', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('naziv', 100);
            $table->string('lokacija', 150)->nullable();
            $table->boolean('zadano')->default(false);   // iz njega se troši kad zapis nema izabrano skladište
            $table->timestamps();
        });

        Schema::table('skladiste_ulazi', function (Blueprint $table) {
            $table->foreignId('skladiste_id')->nullable()->after('user_id')->constrained('skladista')->cascadeOnDelete();
            $table->string('prijenos_par', 36)->nullable()->after('korekcija');   // par ulaza pri prijenosu između skladišta
        });
        Schema::table('prskanja', function (Blueprint $table) {
            $table->foreignId('skladiste_id')->nullable()->after('kultura_id')->constrained('skladista')->nullOnDelete();
        });
        Schema::table('gnojidbe', function (Blueprint $table) {
            $table->foreignId('skladiste_id')->nullable()->after('kultura_id')->constrained('skladista')->nullOnDelete();
        });

        // postojeći ulazi (iz prve verzije skladišta): korisniku se napravi "Glavno skladište"
        foreach (DB::table('skladiste_ulazi')->distinct()->pluck('user_id') as $userId) {
            $id = DB::table('skladista')->insertGetId(['user_id' => $userId, 'naziv' => 'Glavno skladište', 'zadano' => true, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('skladiste_ulazi')->where('user_id', $userId)->update(['skladiste_id' => $id]);
        }

        if (Schema::hasColumn('users', 'koristi_skladiste')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('koristi_skladiste'));
        }
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->boolean('koristi_skladiste')->default(false));
        Schema::table('skladiste_ulazi', fn (Blueprint $table) => $table->dropColumn('prijenos_par'));
        foreach (['gnojidbe', 'prskanja', 'skladiste_ulazi'] as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropConstrainedForeignId('skladiste_id');
            });
        }
        Schema::dropIfExists('skladista');
    }
};
