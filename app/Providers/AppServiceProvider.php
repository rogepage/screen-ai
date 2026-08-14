<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\Screen\ScreenRegionService::class);
        $this->app->singleton(\App\Services\Screen\ScreenMonitorService::class);
        $this->app->bind(
            \App\Services\Screen\ChangeDetectorInterface::class,
            function ($app) {
                if ($app->environment('testing')) {
                    return new \App\Services\Screen\MockChangeDetector();
                }
                return new \App\Services\Screen\PerceptualChangeDetector();
            }
        );

        $this->app->bind(
            \App\Services\Screen\ScreenCaptureInterface::class,
            function ($app) {
                if ($app->environment('testing')) {
                    return new \App\Services\Screen\MockScreenCaptureService();
                }
                return new \App\Services\Screen\MacScreenCaptureService();
            }
        );

        $this->app->singleton(\App\Services\AI\MockAiProvider::class);
        $this->app->bind(
            \App\Services\AI\AiProviderInterface::class,
            \App\Services\AI\MockAiProvider::class
        );

        $this->app->singleton(\App\Services\AI\AiManagerService::class, function ($app) {
            $manager = new \App\Services\AI\AiManagerService($app->make(\App\Services\AI\MockAiProvider::class));
            return $manager;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

