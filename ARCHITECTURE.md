# Screen AI — Arquitetura e Plano de Desenvolvimento

> **Screen AI** é uma aplicação desktop nativa para macOS, construída com Laravel 13 e NativePHP Desktop 2.2.1, projetada para monitorar uma região selecionada da tela, identificar alterações relevantes no conteúdo visual e utilizar Inteligência Artificial multimodal para interpretar, traduzir, resumir ou responder ao conteúdo apresentado.

## Descrição

O Screen AI funciona como um assistente inteligente de tela. O usuário seleciona uma área do monitor que deseja acompanhar e inicia o monitoramento. A aplicação utiliza recursos nativos do macOS para capturar o conteúdo dessa região e um mecanismo de detecção de mudanças para evitar o processamento desnecessário de imagens.

Quando uma alteração relevante é identificada, o sistema captura uma imagem estável, realiza o processamento necessário e envia o conteúdo para um provedor de Inteligência Artificial com suporte a visão computacional.

A IA poderá:

- interpretar o conteúdo apresentado na tela;
- identificar e traduzir textos;
- responder perguntas exibidas em imagens;
- explicar códigos, mensagens de erro ou documentos;
- resumir conteúdos;
- executar instruções definidas pelo usuário.

A arquitetura foi projetada para manter uma separação clara entre a aplicação Laravel, os recursos nativos do macOS e os provedores de Inteligência Artificial.

## Objetivo arquitetural

O projeto deve ser construído de forma modular, permitindo evoluir o produto sem criar dependência entre a captura de tela e o fornecedor de IA.

A arquitetura principal será:

```text
                    SCREEN AI
                        │
                        ▼
              NativePHP Desktop
                        │
                        ▼
                  Laravel 13
                        │
          ┌─────────────┴─────────────┐
          │                           │
          ▼                           ▼
    Serviços da aplicação       Integração nativa
          │                           │
          │                         Swift
          │                           │
          │                    ScreenCaptureKit
          │                           │
          └─────────────┬─────────────┘
                        │
                        ▼
                 Imagem capturada
                        │
                        ▼
                Change Detector
                        │
                  alteração?
                  /        \
                não        sim
                │           │
              ignora        ▼
                     Image Processor
                            │
                            ▼
                       AI Provider
                            │
              ┌─────────────┼─────────────┐
              ▼             ▼             ▼
           Entender      Traduzir      Responder
              │             │             │
              └─────────────┼─────────────┘
                            ▼
                       Screen AI 
                       

## Persistência e Histórico (Fase 9)

O Screen AI inclui uma camada desacoplada para persistência local de análises no SQLite, mantendo privacidade estrita (sem armazenar imagens nem credenciais):

```text
                UI
                 │
                 ▼
          AnalysisService
                 │
       ┌─────────┴─────────┐
       ▼                   ▼
 AIManagerService    AnalysisHistoryService
       │                   │
       ▼                   ▼
AiProviderInterface  AnalysisHistoryRepositoryInterface
       │                   │
       ▼                   ▼
Concrete Provider    EloquentAnalysisHistoryRepository
                           │
                           ▼
                  AnalysisHistory Model
                           │
                           ▼
                     SQLite Database
```

## Fases de Desenvolvimento

- FASE 0  → Ambiente (CONCLUÍDA)
- FASE 1  → NativePHP (CONCLUÍDA)
- FASE 2  → Estrutura da aplicação (CONCLUÍDA)
- FASE 3  → Seleção da área (CONCLUÍDA)
- FASE 4  → ScreenCaptureKit (CONCLUÍDA)
- FASE 5  → Detecção de mudanças (CONCLUÍDA)
- FASE 6  → Processamento da imagem (CONCLUÍDA)
- FASE 7  → Integração com IA (CONCLUÍDA)
- FASE 8  → Modos de análise (CONCLUÍDA)
- FASE 9  → Histórico (EM ANDAMENTO)
- FASE 10 → UX
- FASE 11 → Atalhos globais
- FASE 12 → Overlay
- FASE 13 → Otimização
- FASE 14 → Distribuição