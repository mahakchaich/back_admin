<?php

namespace App\Providers;

use App\Models\CommandePanier;
use Illuminate\Support\facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //set the default string length for migration
        Schema::defaultStringLength(191);

        JsonResource::withoutWrapping();
    }
}
