<?php

namespace App\Providers;

use App\Models\Pet;
use App\Policies\PetPolicy;
use App\Services\SystemMailSettings;
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
        $this->app->singleton(SystemMailSettings::class, fn ($app): SystemMailSettings => new SystemMailSettings(
            $app['config']->get('mail.default'),
            $app['config']->get('mail.mailers.smtp'),
            $app['config']->get('mail.from')
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        app(SystemMailSettings::class)->applySaved();
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
