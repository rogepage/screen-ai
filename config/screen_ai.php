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
];
