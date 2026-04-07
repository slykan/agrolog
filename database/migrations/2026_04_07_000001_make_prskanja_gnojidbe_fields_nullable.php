<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE prskanja MODIFY tretirana_povrsina_ha DECIMAL(8,2) NULL');
        DB::statement('ALTER TABLE prskanja MODIFY trgovacki_naziv_sredstva VARCHAR(255) NULL');
        DB::statement('ALTER TABLE prskanja MODIFY kolicina_sredstva_l_ha DECIMAL(8,3) NULL');

        DB::statement('ALTER TABLE gnojidbe MODIFY tip_gnojiva VARCHAR(255) NULL');
        DB::statement('ALTER TABLE gnojidbe MODIFY kolicina_kg_ha DECIMAL(8,2) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE prskanja MODIFY tretirana_povrsina_ha DECIMAL(8,2) NOT NULL');
        DB::statement('ALTER TABLE prskanja MODIFY trgovacki_naziv_sredstva VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE prskanja MODIFY kolicina_sredstva_l_ha DECIMAL(8,3) NOT NULL');

        DB::statement('ALTER TABLE gnojidbe MODIFY tip_gnojiva VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE gnojidbe MODIFY kolicina_kg_ha DECIMAL(8,2) NOT NULL');
    }
};
