# Screen AI — Guia de Troubleshooting e Diagnóstico

Este documento fornece soluções práticas para problemas comuns encontrados durante a execução, testes ou empacotamento do **Screen AI** no macOS 14.7.

---

## 1. Permissão de Gravação de Tela do macOS (*Screen Recording*)

### Sintoma
- O app exibe o banner: `Aviso de Permissão: A gravação de tela do macOS não está autorizada para o Screen AI.`
- Os testes de captura retornam imagem em branco ou erro de permissão.

### Causa
O macOS requer autorização explícita em **Privacidade e Segurança** para permitir que aplicações capturem o display.

### Solução
1. Abra os **Ajustes do Sistema** do macOS.
2. Navegue até **Privacidade e Segurança** $\rightarrow$ **Gravação de Tela**.
3. Localize e habilite a permissão para o **Screen AI** (ou o terminal/IDE de desenvolvimento).
4. Reinicie a aplicação para que o subsistema nativo do macOS aplique a autorização.

---

## 2. Provedor de IA Temporariamente Indisponível (Circuit Breaker Ativo)

### Sintoma
- O indicador de saúde da UI exibe: `● IA temporariamente indisponível (Circuit Breaker Ativo)`.
- As requisições de análise retornam erro de bloqueio de segurança.

### Causa
O provedor de IA falhou consecutivamente (ex: 3 vezes por timeout ou instabilidade de rede). O `AiCircuitBreakerService` entra em estado `OPEN` para evitar consumo excessivo de recursos e chamadas em cascata.

### Solução
1. Aguarde o período de arrefecimento (*cooldown* padrão de 15 segundos).
2. Verifique se a sua conexão com a internet está ativa.
3. Teste a conexão manual com o provedor na aba **Configurações $\rightarrow$ Inteligência Artificial $\rightarrow$ Testar Conexão com a IA**.
4. Quando a conexão for restabelecida com sucesso, o Circuit Breaker retorna automaticamente para o estado `CLOSED` (Operacional).

---

## 3. Acúmulo de Arquivos Temporários de Captura

### Sintoma
- Grande volume de arquivos `.png` em `storage/app/captures/` ou `storage/app/processed_captures/`.

### Causa
Interrupção abrupta do processo antes do término normal do ciclo contínuo.

### Solução
1. O **Screen AI** executa limpeza automática na inicialização via `CleanupService`.
2. Para limpar manualmente a qualquer momento, acesse **Configurações $\rightarrow$ Diagnóstico $\rightarrow$ Limpar Arquivos Temporários**, ou execute o endpoint:
   ```bash
   curl -X POST http://localhost:8000/api/maintenance/cleanup
   ```

---

## 4. Exportação de Relatório Técnico de Diagnóstico

Para fins de suporte técnico e auditoria, gere o relatório consolidado em JSON sem exposição de credenciais ou imagens sensíveis:

- **Via Interface**: Acesse **Configurações $\rightarrow$ Diagnóstico $\rightarrow$ Exportar Diagnóstico (.JSON)**.
- **Via API**:
  ```bash
  curl http://localhost:8000/api/diagnostics/export
  ```
