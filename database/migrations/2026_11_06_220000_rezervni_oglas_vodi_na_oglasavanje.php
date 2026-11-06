<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Naš rezervni banner na svim mjestima vodi na cjenik; oglasi plaćenih oglašivača se ne diraju. */
    public function up(): void
    {
        DB::table('oglasi')->where('rezervni', true)->update(['url' => '/oglasavanje', 'updated_at' => now()]);
    }

    public function down(): void
    {
        // bez povratka: prijašnja poveznica nije bila namjerna
    }
};
