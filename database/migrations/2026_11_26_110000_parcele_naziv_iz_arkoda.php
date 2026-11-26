<?php

use App\Models\Parcela;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Postojećim ARKOD parcelama bez vlastitog naziva upiši domaći naziv iz ARKOD-a (arkod_naziv), npr. "KOD PETROSA" → "Kod Petrosa". */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('parcele')->whereNull('naziv')->whereNotNull('arkod_naziv')
            ->where(fn ($q) => $q->where('rucno_ucrtana', false)->orWhereNull('rucno_ucrtana'))
            ->orderBy('id')->select(['id', 'arkod_naziv'])
            ->each(function ($p) {
                if ($naziv = Parcela::nazivIzArkoda($p->arkod_naziv)) {
                    DB::table('parcele')->where('id', $p->id)->update(['naziv' => $naziv]);
                }
            });
    }

    public function down(): void
    {
        // vlastiti nazivi ostaju (ne razlikuju se od ručno upisanih)
    }
};
