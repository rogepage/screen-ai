# ==============================================================================
# Screen AI — Makefile de Build, Testes e Distribuição macOS
# ==============================================================================

APP_NAME = Screen AI
APP_VERSION = 1.0.0
DIST_DIR = nativephp/electron/dist
APP_BUNDLE = $(DIST_DIR)/mac/Laravel.app
DMG_FILE = $(DIST_DIR)/Screen-AI-$(APP_VERSION)-x64.dmg
CHECKSUM_FILE = $(DIST_DIR)/Screen-AI-$(APP_VERSION)-x64.dmg.sha256

.PHONY: help install build-assets build-app build-dmg build test test-stress lint format clean serve serve-web checksum open-app open-dmg

help: ## Exibe a lista de comandos disponíveis
	@echo ""
	@echo "  \033[1;34mScreen AI\033[0m — Comandos Principais de Build e Desenvolvimento"
	@echo "  ================================================================"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}'
	@echo ""

# ------------------------------------------------------------------------------
# Instalação e Preparação
# ------------------------------------------------------------------------------

install: ## Instala dependências do PHP e Node.js para produção
	@echo "==> Instalando dependências do Composer..."
	composer install --no-dev --optimize-autoloader
	@echo "==> Instalando dependências do Node.js..."
	npm install

# ------------------------------------------------------------------------------
# Build e Empacotamento
# ------------------------------------------------------------------------------

build-assets: ## Compila os assets de frontend com Vite
	@echo "==> Compilando assets de frontend..."
	npm run build

build-app: build-assets ## Executa o empacotamento oficial do NativePHP Desktop
	@echo "==> Empacotando aplicação nativa macOS com NativePHP..."
	php artisan native:build mac || true

build-dmg: ## Gera o instalador .dmg compatível usando hdiutil nativo do macOS
	@echo "==> Gerando imagem de disco .dmg oficial com hdiutil..."
	@if [ -d "$(APP_BUNDLE)" ]; then \
		mkdir -p $(DIST_DIR); \
		hdiutil create -volname "$(APP_NAME)" -srcfolder "$(APP_BUNDLE)" -ov -format UDZO "$(DMG_FILE)"; \
		echo "==> DMG gerado com sucesso em: $(DMG_FILE)"; \
	else \
		echo "ERRO: Pacote $(APP_BUNDLE) não encontrado. Execute 'make build-app' primeiro."; \
		exit 1; \
	fi

checksum: ## Gera o hash de integridade SHA-256 do instalador .dmg
	@echo "==> Calculando checksum SHA-256 do DMG..."
	@if [ -f "$(DMG_FILE)" ]; then \
		shasum -a 256 "$(DMG_FILE)" | tee "$(CHECKSUM_FILE)"; \
	else \
		echo "ERRO: Arquivo $(DMG_FILE) não encontrado."; \
		exit 1; \
	fi

build: build-app build-dmg checksum ## Executa o pipeline completo de build de produção (Assets + App + DMG + Checksum)
	@echo ""
	@echo "  \033[32m✔ Build de produção concluído com sucesso!\033[0m"
	@echo "  Aplicativo: $(APP_BUNDLE)"
	@echo "  Instalador: $(DMG_FILE)"
	@echo ""

# ------------------------------------------------------------------------------
# Testes e Qualidade
# ------------------------------------------------------------------------------

test: ## Executa a suíte completa de testes com PHPUnit
	@echo "==> Executando testes unitários e de integração..."
	./vendor/bin/phpunit

test-stress: ## Executa o teste de estresse de longa duração (100 ciclos contínuos)
	@echo "==> Executando teste de estresse de longa duração..."
	./vendor/bin/phpunit --filter LongRunStressTest

lint: ## Verifica padrões de código com Laravel Pint (sem alterar arquivos)
	@echo "==> Verificando conformidade de código com Pint..."
	./vendor/bin/pint --test

format: ## Formata automaticamente o código conforme os padrões do Laravel
	@echo "==> Formatando código com Laravel Pint..."
	./vendor/bin/pint

# ------------------------------------------------------------------------------
# Execução e Desenvolvimento Local
# ------------------------------------------------------------------------------

serve: ## Executa o aplicativo Desktop nativo em modo de desenvolvimento
	@echo "==> Iniciando Screen AI Desktop com NativePHP..."
	php artisan native:serve

serve-web: ## Executa o servidor web local do Laravel (http://localhost:8000)
	@echo "==> Iniciando servidor web local..."
	php artisan serve

open-app: ## Abre o aplicativo compilado .app diretamente no macOS
	@echo "==> Abrindo $(APP_BUNDLE)..."
	open "$(APP_BUNDLE)"

open-dmg: ## Monta a imagem de instalação .dmg no macOS
	@echo "==> Montando $(DMG_FILE)..."
	open "$(DMG_FILE)"

# ------------------------------------------------------------------------------
# Limpeza
# ------------------------------------------------------------------------------

clean: ## Limpa caches temporários, logs e arquivos de build
	@echo "==> Limpando artefatos de build e capturas temporárias..."
	rm -rf $(DIST_DIR)
	rm -rf storage/app/captures/*
	rm -rf storage/app/processed_captures/*
	rm -rf storage/framework/cache/data/*
	rm -rf storage/framework/views/*
	rm -rf storage/logs/*.log
	@echo "==> Limpeza concluída com sucesso."
