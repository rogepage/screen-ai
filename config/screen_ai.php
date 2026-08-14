<?php

return [
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
        'default_model' => env('AI_MODEL', 'mock-v1'),
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
        'change_threshold' => (float) env('SCREEN_CHANGE_THRESHOLD', 0.05),
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
];
