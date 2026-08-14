<?php

namespace App\Providers;

use App\Events\Native\AnalyzeNowShortcutPressed;
use App\Events\Native\ToggleMonitorShortcutPressed;
use App\Events\Native\ToggleOverlayShortcutPressed;
use App\Events\Native\TogglePauseShortcutPressed;
use App\Listeners\HandleNativeShortcuts;
use App\Repositories\AnalysisHistoryRepositoryInterface;
use App\Repositories\EloquentAnalysisHistoryRepository;
use App\Services\AI\AiManagerService;
use App\Services\AI\AiProviderInterface;
use App\Services\AI\AnalysisHistoryService;
use App\Services\AI\AnalysisService;
use App\Services\AI\DefaultPromptBuilder;
use App\Services\AI\GeminiAiProvider;
use App\Services\AI\MockAiProvider;
use App\Services\AI\PromptBuilderInterface;
use App\Services\Diagnostics\DiagnosticService;
use App\Services\Maintenance\CleanupService;
use App\Services\Native\MacPermissionService;
use App\Services\Native\NativeBridgeService;
use App\Services\Native\PermissionServiceInterface;
use App\Services\Observability\HealthCheckService;
use App\Services\Observability\MetricsCollectorService;
use App\Services\Observability\ObservabilityService;
use App\Services\Resilience\AiCircuitBreakerService;
use App\Services\Screen\CaptureDebounceService;
use App\Services\Screen\ChangeDetectorInterface;
use App\Services\Screen\ContinuousMonitorService;
use App\Services\Screen\DefaultImageProcessor;
use App\Services\Screen\ImageProcessorInterface;
use App\Services\Screen\MacScreenCaptureService;
use App\Services\Screen\MockChangeDetector;
use App\Services\Screen\MockScreenCaptureService;
use App\Services\Screen\PerceptualChangeDetector;
use App\Services\Screen\ScreenCaptureInterface;
use App\Services\Screen\ScreenMonitorService;
use App\Services\Screen\ScreenRegionService;
use App\Services\Settings\AppSettingsService;
use App\Services\Settings\SettingsValidator;
use App\Services\Startup\StartupService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ScreenRegionService::class);
        $this->app->singleton(ScreenMonitorService::class);
        $this->app->bind(
            ChangeDetectorInterface::class,
            function ($app) {
                if ($app->environment('testing')) {
                    return new MockChangeDetector;
                }

                return new PerceptualChangeDetector;
            }
        );

        $this->app->bind(
            ScreenCaptureInterface::class,
            function ($app) {
                if ($app->environment('testing')) {
                    return new MockScreenCaptureService;
                }

                return new MacScreenCaptureService;
            }
        );

        $this->app->bind(
            ImageProcessorInterface::class,
            DefaultImageProcessor::class
        );

        $this->app->singleton(MockAiProvider::class);
        $this->app->singleton(GeminiAiProvider::class);

        $this->app->bind(
            AiProviderInterface::class,
            function ($app) {
                $provider = config('screen_ai.ai.default_provider', 'mock');
                if ($provider === 'gemini') {
                    return $app->make(GeminiAiProvider::class);
                }

                return $app->make(MockAiProvider::class);
            }
        );

        $this->app->singleton(AiManagerService::class, function ($app) {
            $mock = $app->make(MockAiProvider::class);
            $gemini = $app->make(GeminiAiProvider::class);

            $manager = new AiManagerService($mock);
            $manager->registerProvider($gemini);

            return $manager;
        });

        $this->app->bind(
            PromptBuilderInterface::class,
            DefaultPromptBuilder::class
        );

        $this->app->singleton(AnalysisService::class);

        $this->app->bind(
            AnalysisHistoryRepositoryInterface::class,
            EloquentAnalysisHistoryRepository::class
        );

        $this->app->singleton(AnalysisHistoryService::class);

        $this->app->bind(
            PermissionServiceInterface::class,
            MacPermissionService::class
        );

        $this->app->singleton(SettingsValidator::class);
        $this->app->singleton(AppSettingsService::class);
        $this->app->singleton(StartupService::class);

        $this->app->singleton(NativeBridgeService::class);

        $this->app->singleton(CaptureDebounceService::class, function () {
            return new CaptureDebounceService(
                (int) config('screen_ai.monitor.debounce_ms', 500)
            );
        });

        $this->app->singleton(ObservabilityService::class);
        $this->app->singleton(MetricsCollectorService::class);
        $this->app->singleton(AiCircuitBreakerService::class, function () {
            return new AiCircuitBreakerService(
                failureThreshold: (int) config('screen_ai.ai.circuit_breaker.failure_threshold', 3),
                cooldownSeconds: (int) config('screen_ai.ai.circuit_breaker.cooldown_seconds', 15)
            );
        });
        $this->app->singleton(HealthCheckService::class);
        $this->app->singleton(CleanupService::class);
        $this->app->singleton(DiagnosticService::class);

        $this->app->singleton(ContinuousMonitorService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            app(CleanupService::class)->cleanOrphanCaptures();
        } catch (\Throwable $e) {
            // Silencioso
        }

        Event::listen(
            ToggleMonitorShortcutPressed::class,
            [HandleNativeShortcuts::class, 'handle']
        );
        Event::listen(
            TogglePauseShortcutPressed::class,
            [HandleNativeShortcuts::class, 'handle']
        );
        Event::listen(
            AnalyzeNowShortcutPressed::class,
            [HandleNativeShortcuts::class, 'handle']
        );
        Event::listen(
            ToggleOverlayShortcutPressed::class,
            [HandleNativeShortcuts::class, 'handle']
        );
    }
}
