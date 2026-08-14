<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Screen AI — Configurações</title>
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
            padding: 24px;
            box-sizing: border-box;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 16px;
        }

        h1 { font-size: 20px; margin: 0; }

        .tabs {
            display: flex;
            gap: 8px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 8px;
        }

        .tab-btn {
            background: transparent;
            border: none;
            color: var(--muted-text);
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }

        .tab-btn.active {
            background-color: var(--card-bg);
            color: #fff;
            border: 1px solid var(--border-color);
        }

        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
        }

        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 16px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        label { font-size: 13px; color: var(--muted-text); font-weight: 600; }
        select, input {
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }

        button {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-primary { background-color: var(--primary); color: #fff; }
        .btn-secondary { background-color: var(--border-color); color: #fff; }
        .btn-danger { background-color: var(--danger); color: #fff; }

        .status-msg {
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-top: 12px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div style="display: flex; align-items: center; gap: 8px;">
                <h1 style="margin: 0;">Configurações — Screen AI</h1>
                <span style="font-size: 11px; font-weight: 700; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 2px 7px; border-radius: 4px; letter-spacing: 0.5px;">v{{ config('nativephp.version', '1.0.0') }}</span>
            </div>
            <button class="btn-secondary" onclick="window.location.href='/'">« Voltar ao Painel</button>
        </header>

        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('tabGeneral')">Geral</button>
            <button class="tab-btn" onclick="switchTab('tabMonitor')">Monitoramento</button>
            <button class="tab-btn" onclick="switchTab('tabAI')">Inteligência Artificial</button>
            <button class="tab-btn" onclick="switchTab('tabPrivacy')">Privacidade</button>
            <button class="tab-btn" onclick="switchTab('tabDiagnostics')">Diagnóstico</button>
            <button class="tab-btn" onclick="switchTab('tabAbout')">Sobre</button>
        </div>

        <!-- ABA GERAL -->
        <div id="tabGeneral" class="tab-pane card active">
            <h2>Preferências Gerais</h2>
            <div class="grid-2">
                <div class="form-group">
                    <label for="uiLanguage">Idioma da Interface</label>
                    <select id="uiLanguage">
                        <option value="pt-BR">Português (Brasil)</option>
                        <option value="en-US">English (US)</option>
                        <option value="es-ES">Español</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="responseLanguage">Idioma de Resposta da IA</label>
                    <select id="responseLanguage">
                        <option value="pt-BR">Português (Brasil)</option>
                        <option value="en-US">English (US)</option>
                        <option value="es-ES">Español</option>
                        <option value="fr-FR">Français</option>
                    </select>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="overlayPosition">Posição do Overlay Flutuante</label>
                    <select id="overlayPosition">
                        <option value="bottom-right">Canto Inferior Direito</option>
                        <option value="top-right">Canto Superior Direito</option>
                        <option value="bottom-left">Canto Inferior Esquerdo</option>
                        <option value="top-left">Canto Superior Esquerdo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="autoStart">Iniciar Monitoramento com o App</label>
                    <select id="autoStart">
                        <option value="false">Não (Manual)</option>
                        <option value="true">Sim (Automático)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ABA MONITOR -->
        <div id="tabMonitor" class="tab-pane card">
            <h2>Configurações do Monitor</h2>
            <div class="grid-2">
                <div class="form-group">
                    <label for="intervalMs">Intervalo de Captura (ms)</label>
                    <input type="number" id="intervalMs" min="100" step="100" value="1000" />
                </div>
                <div class="form-group">
                    <label for="debounceMs">Tempo de Debounce / Estabilização (ms)</label>
                    <input type="number" id="debounceMs" min="0" step="50" value="500" />
                </div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label for="changeThreshold">Sensibilidade de Mudança (0.01 a 0.50)</label>
                    <input type="number" id="changeThreshold" min="0.01" max="1.0" step="0.01" value="0.05" />
                </div>
                <div class="form-group">
                    <label for="selectedDisplayId">Monitor Selecionado</label>
                    <select id="selectedDisplayId">
                        <option value="1">Display Principal (macOS)</option>
                    </select>
                </div>
            </div>
            <button class="btn-secondary" onclick="testCapturePreview()">Testar Captura de Frame</button>
            <div id="captureTestMsg" class="status-msg"></div>
        </div>

        <!-- ABA IA -->
        <div id="tabAI" class="tab-pane card">
            <h2>Provedor de Inteligência Artificial</h2>
            <div class="grid-2">
                <div class="form-group">
                    <label for="aiProvider">Provedor Multimodal</label>
                    <select id="aiProvider">
                        <option value="mock">Mock (Simulador Local)</option>
                        <option value="gemini">Google Gemini</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="aiModel">Modelo da IA</label>
                    <select id="aiModel">
                        <option value="gemini-1.5-flash">gemini-1.5-flash</option>
                        <option value="gemini-1.5-pro">gemini-1.5-pro</option>
                    </select>
                </div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label for="defaultAction">Ação Padrão</label>
                    <select id="defaultAction">
                        <option value="interpret">Interpretar</option>
                        <option value="translate">Traduzir</option>
                        <option value="answer">Responder</option>
                        <option value="explain">Explicar</option>
                        <option value="summarize">Resumir</option>
                        <option value="custom">Personalizado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="aiTimeout">Timeout da Conexão (segundos)</label>
                    <input type="number" id="aiTimeout" min="5" max="120" value="30" />
                </div>
            </div>
            <button class="btn-secondary" onclick="testAiConnection()">Testar Conexão com a IA</button>
            <div id="aiTestMsg" class="status-msg"></div>
        </div>

        <!-- ABA PRIVACIDADE -->
        <div id="tabPrivacy" class="tab-pane card">
            <h2>Políticas de Privacidade e Retenção</h2>
            <div class="form-group">
                <label for="historyRetention">Retenção do Histórico Local</label>
                <select id="historyRetention">
                    <option value="always">Sempre Manter (Até limpeza manual)</option>
                    <option value="30_days">30 Dias</option>
                    <option value="7_days">7 Dias</option>
                </select>
            </div>
            <p style="font-size: 13px; color: var(--muted-text); line-height: 1.5;">
                O Screen AI opera exclusivamente com banco de dados SQLite local. Nenhuma captura de tela ou chave de autenticação é salva no banco ou compartilhada com terceiros além do provedor de IA escolhido.
            </p>
        </div>

        <!-- ABA DIAGNÓSTICO -->
        <div id="tabDiagnostics" class="tab-pane card">
            <h2>Diagnóstico do Sistema</h2>
            <div id="diagnosticContent" style="background: var(--bg-color); padding: 12px; border-radius: 6px; border: 1px solid var(--border-color); font-family: monospace; font-size: 12px; margin-bottom: 16px; max-height: 250px; overflow-y: auto;">
                Carregando relatório técnico...
            </div>
            <div class="btn-group" style="margin: 0;">
                <button class="btn-secondary" onclick="loadDiagnostics()">Atualizar Diagnóstico</button>
                <button class="btn-primary" onclick="exportDiagnosticsJson()">Exportar Diagnóstico (.JSON)</button>
                <button class="btn-danger" onclick="cleanTemporaryFiles()">Limpar Arquivos Temporários</button>
            </div>
        </div>

        <!-- ABA SOBRE -->
        <div id="tabAbout" class="tab-pane card">
            <h2>Sobre o Screen AI</h2>
            <p><strong>Versão:</strong> 1.0.0 (Build macOS)</p>
            <p><strong>Tecnologias:</strong> Laravel 13, NativePHP Desktop 2.2, PHP 8.4, ScreenCaptureKit macOS</p>
            <p style="color: var(--muted-text); font-size: 12px;">Desenvolvido com foco em alta performance, privacidade estrita e experiência nativa no macOS.</p>
        </div>

        <div class="btn-group">
            <button class="btn-primary" onclick="saveSettings()">Salvar Alterações</button>
            <button class="btn-secondary" onclick="loadSettings()">Recarregar</button>
        </div>
        <div id="saveStatusMsg" class="status-msg"></div>
    </div>

    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            event.target.classList.add('active');
        }

        async function loadSettings() {
            try {
                const res = await fetch('/api/settings');
                const data = await res.json();

                document.getElementById('uiLanguage').value = data.ui_language || 'pt-BR';
                document.getElementById('responseLanguage').value = data.response_language || 'pt-BR';
                document.getElementById('overlayPosition').value = data.overlay_position || 'bottom-right';
                document.getElementById('autoStart').value = data.auto_start ? 'true' : 'false';
                document.getElementById('intervalMs').value = data.interval_ms || 1000;
                document.getElementById('debounceMs').value = data.debounce_ms || 500;
                document.getElementById('changeThreshold').value = data.change_threshold || 0.05;
                document.getElementById('aiProvider').value = data.ai_provider || 'mock';
                document.getElementById('aiModel').value = data.ai_model || 'gemini-1.5-flash';
                document.getElementById('defaultAction').value = data.default_action || 'interpret';
                document.getElementById('aiTimeout').value = data.ai_timeout || 30;
                document.getElementById('historyRetention').value = data.history_retention || 'always';
            } catch (e) {
                alert('Erro ao carregar configurações: ' + e.message);
            }
        }

        async function saveSettings() {
            const body = {
                ui_language: document.getElementById('uiLanguage').value,
                response_language: document.getElementById('responseLanguage').value,
                overlay_position: document.getElementById('overlayPosition').value,
                auto_start: document.getElementById('autoStart').value === 'true',
                interval_ms: parseInt(document.getElementById('intervalMs').value),
                debounce_ms: parseInt(document.getElementById('debounceMs').value),
                change_threshold: parseFloat(document.getElementById('changeThreshold').value),
                ai_provider: document.getElementById('aiProvider').value,
                ai_model: document.getElementById('aiModel').value,
                default_action: document.getElementById('defaultAction').value,
                ai_timeout: parseInt(document.getElementById('aiTimeout').value),
                history_retention: document.getElementById('historyRetention').value
            };

            const msgBox = document.getElementById('saveStatusMsg');
            try {
                const res = await fetch('/api/settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body)
                });
                const data = await res.json();
                msgBox.style.display = 'block';

                if (res.ok) {
                    msgBox.style.backgroundColor = 'var(--bg-color)';
                    msgBox.style.color = 'var(--success)';
                    msgBox.innerText = '✓ ' + data.message;
                } else {
                    msgBox.style.backgroundColor = 'var(--bg-color)';
                    msgBox.style.color = 'var(--danger)';
                    msgBox.innerText = '✕ Erro ao salvar: ' + JSON.stringify(data.errors || data.error);
                }
            } catch (e) {
                msgBox.style.display = 'block';
                msgBox.style.color = 'var(--danger)';
                msgBox.innerText = '✕ Falha de conexão: ' + e.message;
            }
        }

        async function testAiConnection() {
            const provider = document.getElementById('aiProvider').value;
            const model = document.getElementById('aiModel').value;
            const msg = document.getElementById('aiTestMsg');
            msg.style.display = 'block';
            msg.innerText = 'Testando conexão...';
            msg.style.color = 'var(--muted-text)';

            try {
                const res = await fetch('/api/settings/test-ai', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ai_provider: provider, ai_model: model })
                });
                const data = await res.json();
                msg.innerText = data.success ? '✓ ' + data.message : '✕ ' + data.message;
                msg.style.color = data.success ? 'var(--success)' : 'var(--danger)';
            } catch (e) {
                msg.innerText = '✕ Erro: ' + e.message;
                msg.style.color = 'var(--danger)';
            }
        }

        async function testCapturePreview() {
            const msg = document.getElementById('captureTestMsg');
            msg.style.display = 'block';
            msg.innerText = 'Capturando frame de teste...';
            msg.style.color = 'var(--muted-text)';

            try {
                const res = await fetch('/api/settings/test-capture', { method: 'POST' });
                const data = await res.json();
                if (data.success) {
                    msg.innerText = `✓ Captura OK! Resolução: ${data.preview.width}x${data.preview.height}px (${data.preview.format.toUpperCase()})`;
                    msg.style.color = 'var(--success)';
                } else {
                    msg.innerText = '✕ ' + data.message;
                    msg.style.color = 'var(--danger)';
                }
            } catch (e) {
                msg.innerText = '✕ Erro: ' + e.message;
                msg.style.color = 'var(--danger)';
            }
        }

        async function loadDiagnostics() {
            const container = document.getElementById('diagnosticContent');
            container.innerText = 'Carregando relatório técnico...';
            try {
                const res = await fetch('/api/diagnostics/export');
                const data = await res.json();
                container.innerText = JSON.stringify(data, null, 2);
            } catch (e) {
                container.innerText = 'Erro ao obter diagnóstico: ' + e.message;
            }
        }

        async function exportDiagnosticsJson() {
            try {
                const res = await fetch('/api/diagnostics/export');
                const data = await res.json();
                const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `screen_ai_diagnostics_${new Date().toISOString().slice(0,19).replace(/[:T]/g,'_')}.json`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            } catch (e) {
                alert('Erro ao exportar diagnóstico: ' + e.message);
            }
        }

        async function cleanTemporaryFiles() {
            if (!confirm('Deseja realmente limpar todos os arquivos temporários de captura de tela?')) return;
            try {
                const res = await fetch('/api/maintenance/cleanup', { method: 'POST' });
                const data = await res.json();
                alert(data.message);
                loadDiagnostics();
            } catch (e) {
                alert('Erro ao executar limpeza: ' + e.message);
            }
        }

        loadSettings();
        loadDiagnostics();
    </script>
</body>
</html>
