# Checklist Operacional de Release — Screen AI

Este checklist consolida todos os 20 critérios obrigatórios para liberação de versões de produção do **Screen AI**.

---

### Identidade e Configuração
- [x] Versão do aplicativo unificada (`1.0.0`) em `config/screen_ai.php` e `config/nativephp.php`.
- [x] Bundle Identifier verificado (`com.screenai.desktop`).
- [x] Nome da aplicação padronizado (`Screen AI`).
- [x] Chaves de segredo (`AWS_*`, `AI_API_KEY`, etc.) expurgadas do `.env` empacotado.

### Testes e Qualidade
- [x] 100% dos testes unitários e de integração aprovados via `./vendor/bin/phpunit`.
- [x] Teste de estresse de longa duração (*Long-Run*) com 100+ ciclos contínuos validado.
- [x] Conformidade de estilo e padrões de código verificada via Laravel Pint.
- [x] Composer dependencies auditadas (`composer validate`).

### Runtime e Arquitetura do Pacote
- [x] Assets de frontend compilados em produção (`npm run build`).
- [x] Runtime PHP 8.4 embutido no pacote sem dependências externas de ambiente.
- [x] Electron e NativePHP Desktop 2.2.1 integrados com sucesso.
- [x] Camada nativa do macOS (ScreenCaptureKit, MenuBar, Atalhos Globais) operacional.

### Isolamento e Armazenamento
- [x] Paths absolutos de desenvolvimento (`/Users/...`, `/Desktop/...`) eliminados do código de produção.
- [x] Banco de dados SQLite de histórico gravado no diretório de dados do usuário fora do bundle `.app`.
- [x] Limpeza automática de arquivos temporários de captura de tela em funcionamento.

### Distribuição e Instalação
- [x] Geração do bundle executável `Screen AI.app`.
- [x] Geração da imagem de instalação `Screen AI.dmg` com atalho para `/Applications`.
- [x] Teste de inicialização a partir de `/Applications` validado.
- [x] Fluxo de Onboarding e verificação de permissão de gravação de tela testados.
- [x] Hash SHA-256 gerado e documentado junto aos artefatos de release.
- [x] Status de assinatura e notarização Apple documentado (**PENDING EXTERNAL REQUIREMENT** para certificados de desenvolvedor).
