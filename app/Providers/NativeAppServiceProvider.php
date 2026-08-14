<?php

namespace App\Providers;

use App\Services\Native\NativeBridgeService;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\MenuBar;
use Native\Desktop\Facades\Window;
use Native\Desktop\Menu\Menu;
use Throwable;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        Window::open('main')
            ->title('Screen AI — Assistente Inteligente de Tela')
            ->width(980)
            ->height(720);

        try {
            if (class_exists(MenuBar::class)) {
                MenuBar::create()
                    ->label('Screen AI')
                    ->tooltip('Screen AI — Assistente Inteligente de Tela')
                    ->contextMenu(
                        Menu::new()
                            ->label('● Screen AI')
                            ->separator()
                            ->link('/overlay', 'Abrir Overlay')
                            ->separator()
                            ->quit()
                    );
            }
        } catch (Throwable $e) {
            // Tratamento gracioso em ambientes onde MenuBar não estiver disponível
        }

        /** @var NativeBridgeService $bridge */
        $bridge = app(NativeBridgeService::class);
        $bridge->registerShortcuts();
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
