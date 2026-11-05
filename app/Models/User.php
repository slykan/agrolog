<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'naziv_gospodarstva', 'mipg', 'oib', 'google_id', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'obavijesti_odjava_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Admin pregled korisnika (/admin/korisnici) — e-mailovi iz ADMIN_EMAILS. */
    public function isAdmin(): bool
    {
        return in_array(strtolower($this->email), config('app.admin_emails'), true);
    }

    public function parcele() { return $this->hasMany(Parcela::class); }
    public function kulture() { return $this->hasMany(Kultura::class); }
    public function gnojidbe() { return $this->hasMany(Gnojidba::class); }
    public function prskanja() { return $this->hasMany(Prskanje::class); }
    public function skladisteUlazi() { return $this->hasMany(SkladisteUlaz::class); }
    public function skladista() { return $this->hasMany(Skladiste::class); }
    public function strojevi() { return $this->hasMany(Stroj::class); }
}
