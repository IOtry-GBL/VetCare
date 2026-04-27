<?php

namespace App\Providers;

use App\Models\Pet;
use App\Policies\PetPolicy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        Pet::class => PetPolicy::class,
    ];

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
        $this->registerPolicies();
    }

    /**
     * Register the application's policies.
     */
    protected function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            \Illuminate\Support\Facades\Gate::policy($model, $policy);
        }
    }
}
