<?php

use App\Support\SzbPopisi;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Polja koja traži Ministarstvov predložak evidencije SZB (EU 2023/564), obvezno od 1.1.2027.
        Schema::table('kulture', function (Blueprint $table) {
            $table->string('eppo_naziv')->nullable()->after('naziv');   // usjev s Ministarstvova popisa
            $table->string('eppo_kod', 12)->nullable()->after('eppo_naziv');
        });

        Schema::table('prskanja', function (Blueprint $table) {
            $table->string('vrsta_uporabe', 20)->nullable()->after('registracijski_broj');
            $table->string('bbch')->nullable()->after('vrsta_uporabe');
            $table->string('sustav_proizvodnje', 12)->nullable()->after('bbch');
        });

        // postojeće kulture: EPPO iz naziva gdje se može prepoznati
        DB::table('kulture')->select('id', 'naziv')->orderBy('id')->each(function ($k) {
            if ($usjev = SzbPopisi::usjevZaKulturu($k->naziv)) {
                DB::table('kulture')->where('id', $k->id)->update(['eppo_naziv' => $usjev[0], 'eppo_kod' => $usjev[1]]);
            }
        });

        // dosadašnja prskanja su gotovo sva na otvorenom, neekološki — korisnik može promijeniti
        DB::table('prskanja')->update(['vrsta_uporabe' => 'otvoreno', 'sustav_proizvodnje' => 'neekoloska']);
    }

    public function down(): void
    {
        Schema::table('prskanja', function (Blueprint $table) {
            $table->dropColumn(['vrsta_uporabe', 'bbch', 'sustav_proizvodnje']);
        });
        Schema::table('kulture', function (Blueprint $table) {
            $table->dropColumn(['eppo_naziv', 'eppo_kod']);
        });
    }
};
