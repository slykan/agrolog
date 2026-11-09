<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Obavijest koju admin šalje mailom svim korisnicima (vidi AdminController::obavijesti). */
class Obavijest extends Model
{
    protected $table = 'obavijesti';
    protected $fillable = ['user_id', 'predmet', 'naslov', 'tekst', 'gumb_tekst', 'gumb_url', 'primatelja', 'poslano_at'];
    protected $casts = ['poslano_at' => 'datetime'];

    public function autor() { return $this->belongsTo(User::class, 'user_id'); }

    /** Korisnici kojima se obavijest šalje: svi koji se nisu odjavili. */
    /** Tko prima obavijest bar na jednom kanalu: mail ili push na nekom uređaju. */
    public static function primateljiSvihKanala()
    {
        return User::where(fn ($q) => $q->whereNull('obavijesti_odjava_at')
            ->orWhere(fn ($q) => $q->where('push_obavijesti', true)
                ->where(fn ($q) => $q->whereHas('pushPretplate')->orWhereHas('mobilePushDevices'))));
    }

    public static function primatelji()
    {
        return User::whereNull('obavijesti_odjava_at');
    }

    /** Tekst u odlomke: prazan red dijeli odlomke, novi red ostaje novi red; sve escapano. */
    public function odlomci(): array
    {
        return array_values(array_filter(array_map('trim', preg_split("/\R{2,}/", trim($this->tekst)))));
    }
}
