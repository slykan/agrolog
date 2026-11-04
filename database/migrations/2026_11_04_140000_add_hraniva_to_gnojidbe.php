<?php

use App\Support\Gnojiva;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sastav gnojiva (%) i izračunati kg dušika po ha — evidencija gnojidbe prema Akcijskom programu (NN 95/25)
        Schema::table('gnojidbe', function (Blueprint $table) {
            $table->decimal('n_posto', 5, 2)->nullable()->after('kolicina_kg_ha');
            $table->decimal('p_posto', 5, 2)->nullable()->after('n_posto');
            $table->decimal('k_posto', 5, 2)->nullable()->after('p_posto');
            $table->boolean('organsko')->default(false)->after('k_posto');
            $table->decimal('kg_n_ha', 8, 1)->nullable()->after('organsko');
        });

        // postojeći zapisi: sastav iz upisanog naziva gdje se može prepoznati
        DB::table('gnojidbe')->select('id', 'tip_gnojiva', 'kolicina_kg_ha')->orderBy('id')->each(function ($g) {
            if ($s = Gnojiva::prepoznaj($g->tip_gnojiva)) {
                DB::table('gnojidbe')->where('id', $g->id)->update([
                    'n_posto' => $s['n'], 'p_posto' => $s['p'], 'k_posto' => $s['k'], 'organsko' => $s['organsko'],
                    'kg_n_ha' => Gnojiva::kgHraniva($g->kolicina_kg_ha, $s['n']),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('gnojidbe', function (Blueprint $table) {
            $table->dropColumn(['n_posto', 'p_posto', 'k_posto', 'organsko', 'kg_n_ha']);
        });
    }
};
