<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Screen AI — Configuração Inicial</title>
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --text-color: #f8fafc;
            --muted-text: #94a3b8;
            --primary: #3b82f6;
            --success: #22c55e;
            --warning: #eab308;
            --danger: #ef4444;
            --border-color: #334155;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 40px 20px;
            box-sizing: border-box;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .wizard-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 32px;
            max-width: 580px;
            width: 100%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 24px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 12px;
            font-size: 12px;
            color: var(--muted-text);
        }

        .step-indicator .active {
            color: var(--primary);
            font-weight: 700;
        }

        h1 { font-size: 24px; margin-top: 0; margin-bottom: 8px; color: #fff; }
        p { color: var(--muted-text); font-size: 14px; line-height: 1.5; margin-bottom: 20px; }

        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 24px;
        }

        button {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: opacity 0.2s;
        }

        button:hover { opacity: 0.9; }

        .btn-primary { background-color: var(--primary); color: #fff; }
        .btn-secondary { background-color: var(--border-color); color: #fff; }
        .btn-success { background-color: var(--success); color: #fff; }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 16px;
        }

        label { font-size: 13px; color: var(--muted-text); font-weight: 600; }
        select, input {
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 14px;
        }

        .status-box {
            padding: 12px;
            border-radius: 6px;
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            margin-bottom: 16px;
            font-size: 13px;
        }

        .step-content { display: none; }
        .step-content.active { display: block; }
    </style>
</head>
<body>
    <div class="wizard-card">
        <div class="step-indicator">
            <span id="stepTab1" class="active">1. Boas-vindas</span>
            <span id="stepTab2">2. Permissões</span>
            <span id="stepTab3">3. IA</span>
            <span id="stepTab4">4. Conclusão</span>
        </div>

        <!-- ETAPA 1 -->
        <div id="step1" class="step-content active">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                <h1 style="margin: 0;">Screen AI</h1>
                <span style="font-size: 11px; font-weight: 700; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 2px 7px; border-radius: 4px; letter-spacing: 0.5px;">v{{ config('nativephp.version', '1.0.0') }}</span>
            </div>
            <p>Entenda e interaja com tudo o que aparece na sua tela utilizando Inteligência Artificial multimodal e captura nativa no macOS.</p>
            <div style="background: var(--bg-color); padding: 16px; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 20px;">
                <div style="font-weight: 600; font-size: 14px; color: #38bdf8; margin-bottom: 6px;">Privacidade e Segurança</div>
                <div style="font-size: 12px; color: var(--muted-text); line-height: 1.4;">
                    Suas capturas são processadas em memória e não são salvas permanentemente no disco. Todas as configurações permanecem estritamente locais.
                </div>
            </div>
            <div class="btn-group" style="justify-content: flex-end;">
                <button class="btn-primary" onclick="goToStep(2)">Começar Configuração »</button>
            </div>
        </div>

        <!-- ETAPA 2 -->
        <div id="step2" class="step-content">
            <h1>Gravação de Tela no macOS</h1>
            <p>O Screen AI precisa de autorização de Gravação de Tela do sistema operacional para capturar o conteúdo da região monitorada.</p>
            <div class="status-box" id="permStatusBox">
                Status da Permissão: <strong id="permStatusText" style="color: var(--warning);">Verificando...</strong>
            </div>
            <div class="btn-group">
                <button class="btn-secondary" onclick="openSystemSettings()">Abrir Ajustes do macOS</button>
                <button class="btn-primary" onclick="checkPermission(true)">Verificar Novamente</button>
            </div>
            <div class="btn-group" style="margin-top: 16px;">
                <button class="btn-secondary" onclick="goToStep(1)">« Voltar</button>
                <button class="btn-primary" onclick="goToStep(3)">Avançar »</button>
            </div>
        </div>

        <!-- ETAPA 3 -->
        <div id="step3" class="step-content">
            <h1>Configuração de Inteligência Artificial</h1>
            <p>Escolha o provedor de visão computacional e o modelo para responder às suas solicitações.</p>
            <div class="form-group">
                <label for="aiProviderSelect">Provedor de IA</label>
                <select id="aiProviderSelect">
                    <option value="mock" selected>Mock (Simulador local para testes)</option>
                    <option value="gemini">Google Gemini (Multimodal)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="aiModelSelect">Modelo Padrão</label>
                <select id="aiModelSelect">
                    <option value="gemini-1.5-flash">gemini-1.5-flash (Rápido e Leve)</option>
                    <option value="gemini-1.5-pro">gemini-1.5-pro (Raciocínio Complexo)</option>
                </select>
            </div>
            <button class="btn-secondary" style="width: 100%; margin-bottom: 12px;" onclick="testAiConnection()">Testar Conexão com a IA</button>
            <div id="aiTestResult" style="display: none; font-size: 13px; margin-bottom: 12px;"></div>
            <div class="btn-group">
                <button class="btn-secondary" onclick="goToStep(2)">« Voltar</button>
                <button class="btn-primary" onclick="goToStep(4)">Avançar »</button>
            </div>
        </div>

        <!-- ETAPA 4 -->
        <div id="step4" class="step-content">
            <h1>Tudo Pronto!</h1>
            <p>O Screen AI está configurado e pronto para monitorar a sua tela.</p>
            <div style="background: var(--bg-color); padding: 16px; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 20px;">
                <div style="font-weight: 600; font-size: 13px; color: #22c55e; margin-bottom: 4px;">✓ Configurações salvas localmente</div>
                <div style="font-size: 12px; color: var(--muted-text);">Você poderá alterar qualquer preferência a qualquer momento no menu de Configurações.</div>
            </div>
            <div class="btn-group">
                <button class="btn-secondary" onclick="goToStep(3)">« Voltar</button>
                <button class="btn-success" onclick="finishOnboarding()">Concluir e Abrir Painel »</button>
            </div>
        </div>
    </div>

    <script>
        function goToStep(step) {
            for (let i = 1; i <= 4; i++) {
                document.getElementById(`step${i}`).className = 'step-content' + (i === step ? ' active' : '');
                const tab = document.getElementById(`stepTab${i}`);
                if (tab) tab.className = i === step ? 'active' : '';
            }
            if (step === 2) checkPermission();
        }

        async function checkPermission(showAlert = false) {
            try {
                const res = await fetch('/api/startup/status');
                const data = await res.json();
                const text = document.getElementById('permStatusText');
                if (data.permission_granted) {
                    text.innerText = 'Permissão Concedida ✓';
                    text.style.color = 'var(--success)';
                } else {
                    text.innerText = 'Permissão Pendente ou Negada';
                    text.style.color = 'var(--danger)';
                    if (showAlert) alert('Permissão ainda não concedida. Abra os Ajustes do macOS e autorize o Screen AI.');
                }
            } catch (e) {
                // Silencioso
            }
        }

        async function openSystemSettings() {
            try {
                await fetch('/api/startup/permissions/open-settings', { method: 'POST' });
            } catch (e) {
                alert('Erro ao abrir configurações: ' + e.message);
            }
        }

        async function testAiConnection() {
            const provider = document.getElementById('aiProviderSelect').value;
            const model = document.getElementById('aiModelSelect').value;
            const resBox = document.getElementById('aiTestResult');

            resBox.style.display = 'block';
            resBox.innerText = 'Testando conexão...';
            resBox.style.color = 'var(--muted-text)';

            try {
                const res = await fetch('/api/settings/test-ai', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ai_provider: provider, ai_model: model })
                });
                const data = await res.json();
                if (data.success) {
                    resBox.innerText = '✓ ' + data.message;
                    resBox.style.color = 'var(--success)';
                } else {
                    resBox.innerText = '✕ ' + data.message;
                    resBox.style.color = 'var(--danger)';
                }
            } catch (e) {
                resBox.innerText = '✕ Falha de conexão: ' + e.message;
                resBox.style.color = 'var(--danger)';
            }
        }

        async function finishOnboarding() {
            try {
                await fetch('/api/startup/onboarding/complete', { method: 'POST' });
                window.location.href = '/';
            } catch (e) {
                window.location.href = '/';
            }
        }
    </script>
</body>
</html>
