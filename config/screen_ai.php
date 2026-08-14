<?php

return [
    'version' => env('APP_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Provedor de IA Padrão
    |--------------------------------------------------------------------------
    |
    | Define o provedor de IA utilizado pela aplicação. Na Fase 2, o valor
    | padrão é estritamente 'mock'.
    | NOTA DE SEGURANÇA: Chaves de API não devem ser armazenadas em banco de dados,
    | código ou arquivos de configuração. Em ambiente de produção distribuído,
    | será utilizado o macOS Keychain para armazenamento seguro.
    |
    */
    'ai' => [
        'default_provider' => env('AI_PROVIDER', 'mock'),
        'default_model' => env('AI_MODEL', 'gemini-1.5-flash'),
        'api_key' => env('AI_API_KEY', ''),
        'timeout' => (int) env('AI_TIMEOUT', 30),
        'max_retries' => (int) env('AI_MAX_RETRIES', 2),
        'response_language' => env('AI_RESPONSE_LANGUAGE', 'pt-BR'),
        'circuit_breaker' => [
            'failure_threshold' => (int) env('AI_CB_FAILURE_THRESHOLD', 3),
            'cooldown_seconds' => (int) env('AI_CB_COOLDOWN_SECONDS', 15),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Monitoramento
    |--------------------------------------------------------------------------
    |
    | Parâmetros padrão do ciclo de monitoramento de tela.
    |
    */
    'monitor' => [
        'default_interval_ms' => 1000,
        'debounce_ms' => (int) env('SCREEN_DEBOUNCE_MS', 500),
        'change_threshold' => (float) env('SCREEN_CHANGE_THRESHOLD', 0.05),
        'queue_name' => env('SCREEN_ANALYSIS_QUEUE', 'default'),
        'storage_path' => storage_path('app/captures'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Processamento de Imagem (Fase 6)
    |--------------------------------------------------------------------------
    |
    | Limites e parâmetros de normalização para o Image Processor.
    |
    */
    'image' => [
        'max_width' => (int) env('IMAGE_MAX_WIDTH', 2048),
        'max_height' => (int) env('IMAGE_MAX_HEIGHT', 2048),
        'max_file_size' => (int) env('IMAGE_MAX_FILE_SIZE', 5242880), // 5MB em bytes
        'default_format' => env('IMAGE_DEFAULT_FORMAT', 'png'),
        'storage_path' => storage_path('app/processed_captures'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações Nativas do macOS (Fase 11)
    |--------------------------------------------------------------------------
    |
    | Configurações para a camada nativa, Overlay flutuante e Atalhos Globais.
    |
    */
    'native' => [
        'overlay_enabled' => (bool) env('OVERLAY_ENABLED', true),
        'overlay_position' => env('OVERLAY_POSITION', 'bottom-right'),
        'global_shortcuts_enabled' => (bool) env('GLOBAL_SHORTCUTS_ENABLED', true),
        'shortcuts' => [
            'toggle_monitor' => env('SHORTCUT_TOGGLE_MONITOR', 'CmdOrCtrl+Shift+S'),
            'toggle_pause' => env('SHORTCUT_TOGGLE_PAUSE', 'CmdOrCtrl+Shift+P'),
            'analyze_now' => env('SHORTCUT_ANALYZE_NOW', 'CmdOrCtrl+Shift+A'),
            'toggle_overlay' => env('SHORTCUT_TOGGLE_OVERLAY', 'CmdOrCtrl+Shift+O'),
        ],
    ],
];
