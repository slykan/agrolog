<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Lokacija gospodarstva više nije približna (2 decimale), nego pin koji vlasnik postavi na karti.
    public function up(): void
    {
        Schema::table('prezentacije', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->change();
            $table->decimal('lng', 10, 7)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('prezentacije', function (Blueprint $table) {
            $table->decimal('lat', 8, 2)->nullable()->change();
            $table->decimal('lng', 8, 2)->nullable()->change();
        });
    }
};
