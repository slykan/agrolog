<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Podaci parcele iz javnog ARKOD-a (APPRRR WMS), potvrđeni pri unosu — vidi App\Support\ArkodServis
        Schema::table('parcele', function (Blueprint $table) {
            $table->json('arkod_poligon')->nullable()->after('povrsina_ha');   // [[lat, lng], ...]
            $table->decimal('lat', 10, 7)->nullable()->after('arkod_poligon');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->decimal('arkod_povrsina_ha', 10, 2)->nullable()->after('lng');
            $table->string('arkod_naziv')->nullable()->after('arkod_povrsina_ha');
            $table->string('arkod_vrsta_uporabe')->nullable()->after('arkod_naziv');
            $table->timestamp('arkod_provjereno_at')->nullable()->after('arkod_vrsta_uporabe');
        });
    }

    public function down(): void
    {
        Schema::table('parcele', function (Blueprint $table) {
            $table->dropColumn(['arkod_poligon', 'lat', 'lng', 'arkod_povrsina_ha', 'arkod_naziv', 'arkod_vrsta_uporabe', 'arkod_provjereno_at']);
        });
    }
};
