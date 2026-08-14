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
                       

 → NativePHP
FASE 2  → Estrutura da aplicação
FASE 3  → Seleção da área
FASE 4  → ScreenCaptureKit
FASE 5  → Detecção de mudanças
FASE 6  → Processamento da imagem
FASE 7  → Integração com IA
FASE 8  → Modos de análise
FASE 9  → Histórico
FASE 10 → UX
FASE 11 → Atalhos globais
FASE 12 → Overlay
FASE 13 → Otimização
FASE 14 → Distribuição