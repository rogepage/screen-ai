# Screen AI — Visual Screen Understanding & Continuous Analysis (macOS)

O **Screen AI** é uma aplicação nativa para **macOS** projetada para monitoramento inteligente de regiões da tela, detecção perceptual de mudanças e análise visual multimodal via Inteligência Artificial (Google Gemini ou provedor local).

---

## 📚 Documentação e Manuais

- 📖 **[Manual do Usuário (USER_MANUAL.md)](file:///Users/roger/Desktop/docker/screen-ai/USER_MANUAL.md)**: Guia completo de utilização, seleção de tela, atalhos do macOS, modos de análise e configurações.
- 🛠️ **[Guia de Build e Compilação (BUILD.md)](file:///Users/roger/Desktop/docker/screen-ai/BUILD.md)**: Instruções de compilação, empacotamento `.app` e geração do instalador `.dmg`.
- 🔄 **[Manual de Atualização (UPDATE_MANUAL.md)](file:///Users/roger/Desktop/docker/screen-ai/UPDATE_MANUAL.md)**: Procedimentos de atualização in-app, atualização manual e publicação de novas versões.
- 🩺 **[Guia de Troubleshooting (TROUBLESHOOTING.md)](file:///Users/roger/Desktop/docker/screen-ai/TROUBLESHOOTING.md)**: Soluções para permissões de tela, Circuit Breaker e problemas comuns.
- 📋 **[Checklist de Release (RELEASE_CHECKLIST.md)](file:///Users/roger/Desktop/docker/screen-ai/RELEASE_CHECKLIST.md)**: Checklist de 20 pontos para liberação de produção.
- 🏛️ **[Arquitetura do Sistema (ARCHITECTURE.md)](file:///Users/roger/Desktop/docker/screen-ai/ARCHITECTURE.md)**: Detalhamento técnico da arquitetura híbrida Laravel + NativePHP + Electron.

---

## ⚡ Início Rápido (Makefile)

```bash
make help          # Exibe todos os comandos disponíveis
make build         # Compila assets, gera o .app e o instalador .dmg
make serve         # Inicia a aplicação Desktop em modo de desenvolvimento
make test          # Executa a suíte de testes completa do PHPUnit
make open-app      # Abre o aplicativo compilado .app no macOS
```

---

## 🎯 Princípios Fundamentais

1. **Native First**: Utilização de APIs nativas do macOS (ScreenCaptureKit, MenuBar, Atalhos Globais).
2. **Modularidade**: Camadas desacopladas de captura, detecção, processamento, IA e interface.
3. **Eficiência**: Detecção perceptual de mudanças com debounce inteligente para economizar CPU e requisições.
4. **Privacidade Rigorosa**: Nenhuma imagem é armazenada permanentemente; limpeza automática de temporários.
5. **Provider Agnostic**: Suporte flexível a múltiplos modelos e provedores de IA.

---

## 💻 Stack Tecnológica

| Tecnologia | Versão |
| :--- | :--- |
| **Sistema Operacional** | macOS Sonoma 14.7+ (x86_64 / arm64) |
| **PHP** | 8.4.x |
| **Laravel** | 13.x |
| **NativePHP Desktop** | 2.2.1 |
| **Node.js** | 22.x LTS |
| **Electron** | 38.x |
