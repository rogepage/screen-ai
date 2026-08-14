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

        $this->app->bind(
            \App\Services\Screen\ImageProcessorInterface::class,
            \App\Services\Screen\DefaultImageProcessor::class
        );

        $this->app->singleton(\App\Services\AI\MockAiProvider::class);
        $this->app->singleton(\App\Services\AI\GeminiAiProvider::class);

        $this->app->bind(
            \App\Services\AI\AiProviderInterface::class,
            function ($app) {
                $provider = config('screen_ai.ai.default_provider', 'mock');
                if ($provider === 'gemini') {
                    return $app->make(\App\Services\AI\GeminiAiProvider::class);
                }
                return $app->make(\App\Services\AI\MockAiProvider::class);
            }
        );

        $this->app->singleton(\App\Services\AI\AiManagerService::class, function ($app) {
            $mock = $app->make(\App\Services\AI\MockAiProvider::class);
            $gemini = $app->make(\App\Services\AI\GeminiAiProvider::class);

            $manager = new \App\Services\AI\AiManagerService($mock);
            $manager->registerProvider($gemini);
            return $manager;
        });

        $this->app->bind(
            \App\Services\AI\PromptBuilderInterface::class,
            \App\Services\AI\DefaultPromptBuilder::class
        );

        $this->app->singleton(\App\Services\AI\AnalysisService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

