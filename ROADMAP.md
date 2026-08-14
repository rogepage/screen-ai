# Screen AI Roadmap

## FASE 0 — Ambiente
Status: CONCLUÍDA

## FASE 1 — NativePHP
Status: CONCLUÍDA

## FASE 2 — Estrutura da aplicação
Status: CONCLUÍDA

### Objetivos

- [x] Criar interface inicial
- [x] Criar estados do monitor
- [x] Criar configuração
- [x] Criar Services
- [x] Criar DTOs
- [x] Criar Enums
- [x] Criar testes

## FASE 3 — Seleção da área
Status: CONCLUÍDA

### Objetivos

- [x] Criar ScreenRegionService
- [x] Criar DTO de Região e Eventos (RegionSelected, RegionCleared)
- [x] Criar Overlay de Seleção (selection.blade.php)
- [x] Criar Endpoints HTTP API de Região
- [x] Integrar seleção na Interface Principal
- [x] Criar testes unitários e de integração

## FASE 4 — ScreenCaptureKit
Status: CONCLUÍDA

### Objetivos

- [x] Criar ScreenCaptureInterface
- [x] Criar MacScreenCaptureService (usando screencapture nativo do macOS) e MockScreenCaptureService
- [x] Vincular serviço de captura no AppServiceProvider
- [x] Adicionar endpoint de captura de frame (/api/monitor/capture) no MonitorController
- [x] Adicionar interface visual para teste de captura nativa no welcome.blade.php
- [x] Criar testes unitários e de integração para o serviço de captura

## FASE 5 — Detecção de mudanças
Status: CONCLUÍDA

### Objetivos

- [x] Criar ChangeDetectorInterface e PerceptualChangeDetector (subamostragem GD + threshold)
- [x] Criar MockChangeDetector para ambiente de teste
- [x] Configurar threshold via config/screen_ai.php (SCREEN_CHANGE_THRESHOLD)
- [x] Tratar a primeira captura ($previousImagePath === null) como alteração válida
- [x] Integrar ChangeDetectorInterface ao ScreenMonitorService (processCapture)
- [x] Disparar evento ScreenChanged quando mudança relevante for detectada
- [x] Configurar binding centralizado no AppServiceProvider
- [x] Criar testes unitários (PerceptualChangeDetectorTest) e de integração (ScreenMonitorChangeDetectionTest)

## FASE 6 — Processamento da imagem
Status: PENDENTE