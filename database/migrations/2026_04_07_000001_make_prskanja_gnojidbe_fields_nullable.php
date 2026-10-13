<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            // npr. SQLite u testovima
            $this->schemaChange(true);
            return;
        }

        DB::statement('ALTER TABLE prskanja MODIFY tretirana_povrsina_ha DECIMAL(8,2) NULL');
        DB::statement('ALTER TABLE prskanja MODIFY trgovacki_naziv_sredstva VARCHAR(255) NULL');
        DB::statement('ALTER TABLE prskanja MODIFY kolicina_sredstva_l_ha DECIMAL(8,3) NULL');

        DB::statement('ALTER TABLE gnojidbe MODIFY tip_gnojiva VARCHAR(255) NULL');
        DB::statement('ALTER TABLE gnojidbe MODIFY kolicina_kg_ha DECIMAL(8,2) NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->schemaChange(false);
            return;
        }

        DB::statement('ALTER TABLE prskanja MODIFY tretirana_povrsina_ha DECIMAL(8,2) NOT NULL');
        DB::statement('ALTER TABLE prskanja MODIFY trgovacki_naziv_sredstva VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE prskanja MODIFY kolicina_sredstva_l_ha DECIMAL(8,3) NOT NULL');

        DB::statement('ALTER TABLE gnojidbe MODIFY tip_gnojiva VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE gnojidbe MODIFY kolicina_kg_ha DECIMAL(8,2) NOT NULL');
    }

    private function schemaChange(bool $nullable): void
    {
        Schema::table('prskanja', function (Blueprint $table) use ($nullable) {
            $table->decimal('tretirana_povrsina_ha', 8, 2)->nullable($nullable)->change();
            $table->string('trgovacki_naziv_sredstva')->nullable($nullable)->change();
            $table->decimal('kolicina_sredstva_l_ha', 8, 3)->nullable($nullable)->change();
        });
        Schema::table('gnojidbe', function (Blueprint $table) use ($nullable) {
            $table->string('tip_gnojiva')->nullable($nullable)->change();
            $table->decimal('kolicina_kg_ha', 8, 2)->nullable($nullable)->change();
        });
    }
};
