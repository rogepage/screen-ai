# Manual de Atualização — Screen AI (macOS)

Este documento orienta usuários finais e mantenedores/desenvolvedores sobre como atualizar o **Screen AI**, garantindo a integridade dos dados, histórico de análises e configurações locais.

---

## 1. Visão Geral da Arquitetura de Dados

O **Screen AI** foi projetado com **desacoplamento total** entre a aplicação e os dados do usuário:

```text
       ATUALIZAÇÃO DO APLICATIVO                 DADOS PERSISTENTES DO USUÁRIO
    ┌─────────────────────────────┐           ┌────────────────────────────────┐
    │ /Applications/Screen AI.app │ ──(Atualiza)──►   Mantidos intactos em:     │
    │   - PHP 8.4 runtime         │           │   - SQLite (Histórico)         │
    │   - Electron & Laravel      │           │   - settings.json (Preferências│
    │   - Assets & UI             │           │   - Diretório de Logs          │
    └─────────────────────────────┘           └────────────────────────────────┘
```

> [!IMPORTANT]
> A substituição do arquivo `Screen AI.app` em `/Applications` **não remove nem afeta** suas configurações, chave de IA ou histórico de capturas salvas.

---

## 2. Como Atualizar (Usuário Final)

### Método A: Atualização Manual via `.dmg` (Recomendado)

1. Baixe o instalador mais recente `Screen-AI-x.x.x-x64.dmg`.
2. Dê um duplo-clique no arquivo `.dmg` para montar a imagem de disco.
3. Arraste o ícone do **Screen AI** para a pasta **Applications (Aplicativos)**.
4. Quando o macOS perguntar se deseja substituir o aplicativo existente, clique em **Substituir (Replace)**.
5. Abra o **Screen AI** normalmente.

### Método B: Atualização Automática (In-App Updater)

Quando configurado com GitHub Releases ou S3/Spaces:
1. O aplicativo verifica periodicamente por novas versões em segundo plano.
2. Uma notificação no macOS informará: *"Nova versão do Screen AI disponível"*.
3. Clique em **Atualizar e Reiniciar** para aplicar a nova versão automaticamente.

---

## 3. Como Lançar uma Nova Versão (Guia do Desenvolvedor)

Para criar e disponibilizar uma nova versão do **Screen AI**, siga o passo a passo:

### Passo 1: Incrementar a Versão
Atualize a versão unificada da aplicação nos arquivos de configuração:
- [`config/screen_ai.php`](file:///Users/roger/Desktop/docker/screen-ai/config/screen_ai.php):
  ```php
  'version' => env('APP_VERSION', '1.1.0'),
  ```
- [`config/nativephp.php`](file:///Users/roger/Desktop/docker/screen-ai/config/nativephp.php):
  ```php
  'version' => env('NATIVEPHP_APP_VERSION', '1.1.0'),
  ```

### Passo 2: Executar Testes de Regressão e Validação
Certifique-se de que 100% dos testes e padrões de código estejam aprovados:
```bash
make test
make lint
```

### Passo 3: Executar o Build de Produção
Gere a nova versão empacotada e o instalador `.dmg`:
```bash
make build
```
Os arquivos serão gerados em `nativephp/electron/dist/`:
- `Screen-AI-1.1.0-x64.dmg`
- `Laravel-1.1.0-x64.zip`
- `Screen-AI-1.1.0-x64.dmg.sha256`

### Passo 4: Assinatura e Notarização Apple (Quando aplicável)
```bash
# Assinar pacote
codesign --deep --force --verify --verbose --sign "Developer ID Application: Your Team" --options runtime "nativephp/electron/dist/mac/Laravel.app"

# Notarizar DMG
xcrun notarytool submit "nativephp/electron/dist/Screen-AI-1.1.0-x64.dmg" --apple-id "seu-email@apple.com" --password "app-password" --team-id "TEAM_ID" --wait

# Grampear ticket
xcrun stapler staple "nativephp/electron/dist/Screen-AI-1.1.0-x64.dmg"
```

### Passo 5: Publicar a Release
1. Crie uma nova tag no repositório:
   ```bash
   git tag v1.1.0
   git push origin v1.1.0
   ```
2. No GitHub Releases, anexe os arquivos:
   - `Screen-AI-1.1.0-x64.dmg`
   - `Laravel-1.1.0-x64.zip`
   - `Screen-AI-1.1.0-x64.dmg.sha256`

---

## 4. Migração Automática de Esquema de Banco de Dados

Quando a nova versão inclui novas tabelas ou colunas:
1. O NativePHP executa automaticamente as migrações do Laravel (`php artisan migrate --force`) durante a inicialização do app.
2. Nenhuma intervenção manual no banco SQLite local é necessária por parte do usuário.

---

## 5. Procedimento de Rollback (Reversão para Versão Anterior)

Se precisar reverter para uma versão anterior por qualquer motivo:

1. Feche o **Screen AI** em execução.
2. Baixe o `.dmg` da versão anterior desejada.
3. Arraste para `/Applications` e confirme a substituição.
4. Ao abrir, o aplicativo continuará utilizando seu histórico existente de forma segura.

---

## 6. Troubleshooting Pós-Atualização

| Problema | Causa | Resolução |
| :--- | :--- | :--- |
| **Aviso de permissão de gravação de tela após atualizar** | O macOS revalida a assinatura do binário em atualizações | Abra **Ajustes $\rightarrow$ Privacidade e Segurança $\rightarrow$ Gravação de Tela**, desmarque e marque novamente o **Screen AI**. |
| **Aviso de desenvolvedor não verificado (Gatekeeper)** | Aplicativo distribuído sem certificado pago Apple Developer | Clique com o **botão direito** no aplicativo em `/Applications/Screen AI.app` $\rightarrow$ selecione **Abrir** $\rightarrow$ clique em **Abrir**. |
| **Histórico não carregou** | Permissão de leitura no diretório de dados | Verifique se o diretório de dados do usuário possui permissão de leitura/escrita para o usuário local atual. |
