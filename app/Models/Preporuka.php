<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Preporuka agronoma gospodarstvu. Prihvaćanje ne upisuje ništa u evidenciju: zapis nastaje tek kad je posao stvarno obavljen. */
class Preporuka extends Model
{
    protected $table = 'preporuke';
    protected $fillable = ['paket', 'savjetnik_id', 'savjetnik_ime', 'vlasnik_id', 'kultura_id', 'vrsta', 'parcela_oznaka', 'kultura_naziv',
        'sredstvo', 'doza', 'jedinica', 'rok', 'napomena', 'provjera', 'status', 'pitanje', 'odgovor_savjetnika', 'odgovoreno_at', 'savjetnik_vidio_at', 'zapis_id'];
    protected $casts = ['rok' => 'date', 'doza' => 'float', 'provjera' => 'array', 'odgovoreno_at' => 'datetime', 'savjetnik_vidio_at' => 'datetime'];

    public const VRSTE = ['prskanje' => 'Prskanje', 'gnojidba' => 'Gnojidba', 'poruka' => 'Opća poruka'];
    public const JEDINICE = ['l/ha', 'kg/ha', 'ml/ha', 'g/ha'];
    public const STATUSI = [
        'poslano' => 'čeka odgovor', 'prihvaceno' => 'prihvaćeno', 'odbijeno' => 'odbijeno', 'pitanje' => 'ima pitanje',
        'napravljeno' => 'napravljeno', 'nije_napravljeno' => 'nije napravljeno', 'povuceno' => 'povučeno',
    ];
    /** Stanja u kojima se posao još može obaviti. */
    public const OTVORENO = ['poslano', 'prihvaceno', 'pitanje'];

    public function vlasnik() { return $this->belongsTo(User::class, 'vlasnik_id'); }
    public function savjetnik() { return $this->belongsTo(User::class, 'savjetnik_id'); }
    public function kultura() { return $this->belongsTo(Kultura::class); }

    public function imaUnos(): bool
    {
        return in_array($this->vrsta, ['prskanje', 'gnojidba'], true) && $this->kultura_id && in_array($this->status, self::OTVORENO, true);
    }

    public function dozaTekst(): ?string
    {
        if ($this->doza === null) {
            return null;
        }

        return rtrim(rtrim(number_format($this->doza, 3, ',', '.'), '0'), ',') . ' ' . $this->jedinica;
    }

    /** Sažetak za obavijesti, npr. "Pšenica · ARKOD 123 · Roundup 2 l/ha". */
    public function sazetak(): string
    {
        return collect([$this->kultura_naziv, $this->parcela_oznaka, trim($this->sredstvo . ' ' . $this->dozaTekst())])->filter()->implode(' · ');
    }

    public function upozorenja(): array
    {
        return collect($this->provjera ?? [])->where('razina', 'upozorenje')->pluck('tekst')->all();
    }
}
