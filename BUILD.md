# Guia Oficial de Build e Empacotamento — Screen AI (macOS)

Este documento descreve o procedimento oficial e reproduzível para compilar, empacotar e gerar a distribuição do **Screen AI** no macOS 14.7+.

---

## Atalhos Rápidos com Makefile

Você pode usar o `Makefile` para automatizar todas as etapas:

```bash
make help          # Exibe todos os comandos disponíveis
make build         # Pipeline completo: compila assets, gera .app, cria .dmg e gera checksum
make test          # Roda os testes unitários e de integração
make serve         # Roda a aplicação Desktop nativa em desenvolvimento
make open-app      # Abre o aplicativo .app compilado
make open-dmg      # Monta o instalador .dmg compilado
make clean         # Limpa artefatos temporários e de build
```

Para procedimentos de atualização de versão, consulte também o [`UPDATE_MANUAL.md`](file:///Users/roger/Desktop/docker/screen-ai/UPDATE_MANUAL.md).

---

## 1. Pré-requisitos do Ambiente

- **Sistema Operacional**: macOS Sonoma 14.7+ (Arquiteturas `x86_64` / Intel ou `arm64` / Apple Silicon)
- **PHP**: PHP 8.4.x com extensões `gd`, `pdo_sqlite`, `mbstring`, `openssl`, `curl`
- **Composer**: 2.8+
- **Node.js & npm**: Node.js 22.x LTS e npm 10+
- **NativePHP Desktop**: Versão 2.2.1

---

## 2. Preparação do Ambiente e Compilação dos Assets

Antes de iniciar o empacotamento, limpe o cache e compile os assets do frontend:

```bash
# 1. Instalar dependências do PHP para produção (sem dev tools)
composer install --no-dev --optimize-autoloader

# 2. Instalar dependências do Node.js
npm ci

# 3. Compilar assets de produção (Vite / Tailwind)
npm run build
```

---

## 3. Empacotamento com NativePHP Desktop

O NativePHP 2.2.1 empacota o runtime do PHP 8.4, o runtime do Electron, a aplicação Laravel e os componentes nativos de captura do macOS:

```bash
# Comando oficial de build para macOS
php artisan native:build mac
```

> [!NOTE]
> Durante o processo de build, o NativePHP executa o script configurado em `prebuild` (`npm run build`) e aplica as regras de `cleanup_env_keys` para remover chaves de desenvolvimento e segredos do arquivo `.env` final.

---

## 4. Localização dos Artefatos de Build

Após a conclusão da compilação, os artefatos de release são gerados nos diretórios:

- **Aplicativo macOS**: `dist-native/mac/Screen AI.app` (ou `dist/Screen AI.app`)
- **Imagem de Instalação**: `dist-native/mac/Screen AI.dmg`

---

## 5. Assinatura de Código e Notarização Apple (*Code Signing & Notarization*)

Para distribuição pública fora da Mac App Store sem avisos do macOS Gatekeeper:

### A. Requisitos Externos
- Conta ativa no **Apple Developer Program**
- Certificado **Developer ID Application** instalado no Keychain do macOS
- Senha de aplicativo (*App-Specific Password*) para autenticação com a Apple

### B. Assinatura do Bundle `.app`
```bash
codesign --deep --force --verify --verbose --sign "Developer ID Application: Your Name (TEAM_ID)" --options runtime "dist-native/mac/Screen AI.app"
```

### C. Notarização com NotaryTool
```bash
xcrun notarytool submit "dist-native/mac/Screen AI.dmg" --apple-id "seu-email@apple.com" --password "app-specific-password" --team-id "TEAM_ID" --wait
```

### D. Grampeamento do Ticket de Notarização (*Stapling*)
```bash
xcrun stapler staple "dist-native/mac/Screen AI.dmg"
```

> [!IMPORTANT]
> Caso a assinatura e notarização estejam pendentes de certificados externos de desenvolvedor (**PENDING EXTERNAL REQUIREMENT**), o aplicativo pode ser executado localmente clicando com o botão direito no `.app` $\rightarrow$ **Abrir** (confirmando a execução pelo Gatekeeper do macOS).

---

## 6. Geração de Checksum (SHA-256)

Para garantir a integridade dos binários distribuídos, gere o hash SHA-256:

```bash
shasum -a 256 "dist-native/mac/Screen AI.dmg" > "dist-native/mac/Screen AI.dmg.sha256"
```

---

## 7. Troubleshooting de Build

| Problema | Causa Provável | Solução |
| :--- | :--- | :--- |
| `npm run build` falha | Dependências desatualizadas | Execute `rm -rf node_modules package-lock.json && npm install` |
| Erro de permissão de gravação de tela | macOS TCC não configurado no terminal | Autorize o Terminal / IDE em **Ajustes $\rightarrow$ Privacidade e Segurança $\rightarrow$ Gravação de Tela** |
| Falha no runtime PHP embutido | Incompatibilidade de arquitetura | Verifique se a arquitetura de compilação corresponde a `uname -m` |
