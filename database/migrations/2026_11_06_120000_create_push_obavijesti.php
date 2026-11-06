<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // zadano uključeno; pretplata na uređaju svejedno traži dopuštenje preglednika
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('push_obavijesti')->default(true);
        });

        // jedan red po uređaju/pregledniku
        Schema::create('push_pretplate', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('endpoint');
            $table->char('endpoint_hash', 64)->unique();
            $table->string('p256dh', 255);
            $table->string('auth', 64);
            $table->timestamps();
        });

        // što je već javljeno, da se ista stavka iz zvona ne šalje svaki dan
        Schema::create('push_poslano', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kljuc', 120);
            $table->timestamp('created_at')->nullable();
            $table->unique(['user_id', 'kljuc']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_poslano');
        Schema::dropIfExists('push_pretplate');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('push_obavijesti');
        });
    }
};
