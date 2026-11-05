<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // u klijentovom gospodarstvu savjetnik nikad nije admin (aktivni korisnik je tada vlasnik gospodarstva)
        Gate::define('admin', fn (User $user) => ! request()->attributes->has('savjetnik') && $user->isAdmin());
    }
}
