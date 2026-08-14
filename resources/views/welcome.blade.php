<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Screen AI - Painel de Controle</title>
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
            max-width: 900px;
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

        h1 {
            font-size: 20px;
            margin: 0;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            background-color: var(--border-color);
        }

        .badge.stopped { background-color: var(--danger); color: #fff; }
        .badge.monitoring { background-color: var(--success); color: #fff; }
        .badge.paused { background-color: var(--warning); color: #000; }

        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
        }

        .card h2 {
            font-size: 16px;
            margin-top: 0;
            margin-bottom: 16px;
            color: var(--muted-text);
        }

        .btn-group {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        button {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        button:hover { opacity: 0.85; }

        .btn-start { background-color: var(--success); color: #fff; }
        .btn-stop { background-color: var(--danger); color: #fff; }
        .btn-pause { background-color: var(--warning); color: #000; }
        .btn-analyze { background-color: var(--primary); color: #fff; }
        .btn-secondary { background-color: var(--border-color); color: #fff; }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 16px;
        }

        label {
            font-size: 14px;
            color: var(--muted-text);
        }

        select, input, textarea {
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            padding: 8px 12px;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
        }

        .log-box {
            font-family: monospace;
            background-color: #020617;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 12px;
            height: 180px;
            overflow-y: auto;
            font-size: 12px;
            color: #38bdf8;
            white-space: pre-wrap;
        }

        .capture-info {
            font-size: 13px;
            font-family: monospace;
            background-color: var(--bg-color);
            padding: 10px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            color: #22c55e;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <h1 style="display: inline-block; margin: 0;">Screen AI — Painel de Controle</h1>
                <span style="font-size: 11px; font-weight: 700; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); padding: 2px 7px; border-radius: 4px; letter-spacing: 0.5px;">v{{ config('nativephp.version', '1.0.0') }}</span>
                <span id="stateBadge" class="badge stopped">STOPPED</span>
                <span id="healthBadge" style="font-size: 12px; font-weight: 600; color: #22c55e;">● Operacional</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <button class="btn-secondary" onclick="window.location.href='/'">Monitor</button>
                <button class="btn-secondary" onclick="window.location.href='/settings'">Configurações</button>
                <button class="btn-secondary" onclick="window.location.href='/onboarding'">Onboarding</button>
            </div>
        </header>

        <div id="permWarningBanner" style="display: none; background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); border-radius: 8px; padding: 12px 16px; justify-content: space-between; align-items: center;">
            <div style="font-size: 13px; color: #fca5a5;">
                <strong>Aviso de Permissão:</strong> A gravação de tela do macOS não está autorizada para o Screen AI.
            </div>
            <button class="btn-secondary" style="font-size: 12px; padding: 4px 10px;" onclick="openSystemSettings()">Abrir Ajustes do macOS</button>
        </div>

        <!-- CARD PRINCIPAL: MONITOR DE PRINTS DO DESKTOP -->
        <div class="card" style="border: 1px solid #38bdf8;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h2 style="margin: 0; color: #38bdf8;">📂 Monitor de Capturas do Desktop (macOS)</h2>
                <span id="watcherStatusBadge" style="background: rgba(56, 189, 248, 0.2); color: #38bdf8; font-size: 11px; padding: 3px 8px; border-radius: 4px; font-weight: 600;">PRONTO</span>
            </div>
            <p style="font-size: 13px; color: var(--muted-text); margin-bottom: 14px;">
                O Screen AI monitora a pasta <strong>/Users/roger/Desktop</strong>. Tire um print com <strong>Cmd+Shift+4</strong> ou <strong>Cmd+Shift+3</strong> no macOS para que a IA analise automaticamente o conteúdo!
            </p>

            <div class="grid-2" style="margin-bottom: 12px;">
                <div class="form-group" style="margin: 0;">
                    <label for="actionType">Ação Desejada da IA</label>
                    <select id="actionType" onchange="toggleCustomInstruction()">
                        <option value="interpret">Interpretar Conteúdo Visual</option>
                        <option value="translate">Traduzir Texto Presente</option>
                        <option value="answer">Responder Pergunta Exibida</option>
                        <option value="explain" selected>Explicar Conteúdo / Erro / Código</option>
                        <option value="summarize">Resumir Conteúdo da Tela</option>
                        <option value="custom">Instrução Personalizada (Custom)</option>
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label for="responseLanguage">Idioma da Resposta</label>
                    <select id="responseLanguage">
                        <option value="pt-BR" selected>Português (Brasil)</option>
                        <option value="en-US">English (US)</option>
                        <option value="es-ES">Español</option>
                        <option value="fr-FR">Français</option>
                    </select>
                </div>
            </div>

            <div class="form-group" id="customInstructionGroup" style="display: none; margin-bottom: 12px;">
                <label for="userInstruction">Instrução Personalizada do Usuário</label>
                <textarea id="userInstruction" rows="2" placeholder="Ex: Aponte apenas os erros de sintaxe nesta imagem..."></textarea>
            </div>

            <div class="btn-group" style="margin-bottom: 12px;">
                <button class="btn-start" onclick="startMonitor()">▶ Iniciar Monitoramento</button>
                <button class="btn-pause" onclick="pauseMonitor()">⏸ Pausar</button>
                <button class="btn-stop" onclick="stopMonitor()">⏹ Parar</button>
                <button class="btn-secondary" onclick="checkDesktopPrintsNow()">📸 Verificar Prints Agora</button>
            </div>

            <!-- ÚLTIMA RESPOSTA EM DESTAQUE -->
            <div id="lastAnalysisCard" style="background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 6px; padding: 12px; margin-top: 10px;">
                <div style="font-size: 11px; font-weight: 700; color: #38bdf8; text-transform: uppercase; margin-bottom: 6px;" id="lastAnalysisHeader">Última Resposta da IA</div>
                <div id="analysisStatus" style="font-size: 12px; color: var(--muted-text); margin-bottom: 6px; display: none;"></div>
                <div id="analysisContent" style="font-size: 13px; color: #f1f5f9; line-height: 1.5; white-space: pre-wrap;">Aguardando captura de tela no Desktop (Cmd+Shift+4)...</div>
            </div>
        </div>

        <!-- SUBMENU RECOLHÍVEL: FERRAMENTAS AVANÇADAS -->
        <div class="card" style="border: 1px solid var(--border-color);">
            <div style="display: flex; justify-content: space-between; align-items: center; cursor: pointer;" onclick="toggleAdvancedSubmenu()">
                <h3 style="margin: 0; font-size: 14px; color: #cbd5e1;">⚙️ Ferramentas Avançadas e Outros Modos de Captura</h3>
                <button class="btn-secondary" id="btnToggleAdvanced" style="font-size: 12px; padding: 3px 10px;">+ Expandir</button>
            </div>

            <div id="advancedSubmenuContent" style="display: none; margin-top: 16px;">
                <!-- Orquestração do Monitoramento Contínuo (Fase 10) -->
                <div style="border-top: 1px solid var(--border-color); padding-top: 12px; margin-bottom: 16px;">
                    <h4 style="margin: 0 0 10px 0; color: #94a3b8; font-size: 13px;">Loop Contínuo e Métricas de Captura</h4>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 12px; text-align: center;">
                        <div style="background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 6px; padding: 6px;">
                            <div style="font-size: 10px; color: var(--muted-text);">Ciclos</div>
                            <div id="metricCycles" style="font-size: 16px; font-weight: 700; color: #38bdf8;">0</div>
                        </div>
                        <div style="background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 6px; padding: 6px;">
                            <div style="font-size: 10px; color: var(--muted-text);">Capturas</div>
                            <div id="metricCaptures" style="font-size: 16px; font-weight: 700; color: #22c55e;">0</div>
                        </div>
                        <div style="background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 6px; padding: 6px;">
                            <div style="font-size: 10px; color: var(--muted-text);">Debounce</div>
                            <div id="metricDebounced" style="font-size: 16px; font-weight: 700; color: #eab308;">0</div>
                        </div>
                        <div style="background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 6px; padding: 6px;">
                            <div style="font-size: 10px; color: var(--muted-text);">Jobs</div>
                            <div id="metricQueued" style="font-size: 16px; font-weight: 700; color: #a855f7;">0</div>
                        </div>
                    </div>
                    <div class="btn-group" style="margin: 0;">
                        <button class="btn-secondary" style="font-size: 12px;" onclick="triggerContinuousCycle()">Executar Loop Manual</button>
                        <button class="btn-secondary" style="font-size: 12px;" onclick="fetchPipelineStats()">Atualizar Métricas</button>
                    </div>
                </div>

                <!-- Seleção de Área da Tela (Fase 3) -->
                <div style="border-top: 1px solid var(--border-color); padding-top: 12px; margin-bottom: 16px;">
                    <h4 style="margin: 0 0 10px 0; color: #94a3b8; font-size: 13px;">Seleção de Área e Região Fixa</h4>
                    <div style="margin-bottom: 10px; font-size: 13px;">
                        Região Ativa: <strong id="activeRegionText" style="color: #38bdf8;">Nenhuma área selecionada</strong>
                    </div>
                    <div class="btn-group" style="margin-bottom: 10px;">
                        <button class="btn-secondary" style="font-size: 12px;" onclick="openSelectionOverlay()">Abrir Overlay de Seleção</button>
                        <button class="btn-secondary" style="font-size: 12px;" onclick="fetchRegion()">Atualizar Dados</button>
                        <button class="btn-stop" style="font-size: 12px;" onclick="clearRegion()">Limpar Seleção</button>
                    </div>
                    <div class="grid-2">
                        <div class="form-group" style="margin-bottom: 6px;">
                            <label for="regX">X (px)</label>
                            <input type="number" id="regX" value="100" min="0" />
                        </div>
                        <div class="form-group" style="margin-bottom: 6px;">
                            <label for="regY">Y (px)</label>
                            <input type="number" id="regY" value="100" min="0" />
                        </div>
                        <div class="form-group" style="margin-bottom: 6px;">
                            <label for="regWidth">Largura (px)</label>
                            <input type="number" id="regWidth" value="800" min="1" />
                        </div>
                        <div class="form-group" style="margin-bottom: 6px;">
                            <label for="regHeight">Altura (px)</label>
                            <input type="number" id="regHeight" value="600" min="1" />
                        </div>
                    </div>
                    <button class="btn-secondary" style="font-size: 12px; margin-top: 6px;" onclick="saveManualRegion()">Definir Coordenadas Manuais</button>
                </div>

                <!-- ScreenCaptureKit & Overlay (Fases 4 e 11) -->
                <div style="border-top: 1px solid var(--border-color); padding-top: 12px;">
                    <h4 style="margin: 0 0 10px 0; color: #94a3b8; font-size: 13px;">Captura Nativa e Overlay Flutuante</h4>
                    <div class="btn-group" style="margin: 0;">
                        <button class="btn-secondary" style="font-size: 12px;" onclick="triggerCapture()">Capturar Frame da Região</button>
                        <button class="btn-secondary" style="font-size: 12px;" onclick="openFloatingOverlay()">Abrir Janela de Overlay</button>
                    </div>
                    <div id="captureDetails" class="capture-info" style="display: none; margin-top: 8px;">
                        Nenhuma captura realizada nesta sessão.
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD DE HISTÓRICO DE ANÁLISES -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h2 style="margin: 0;">Histórico de Análises</h2>
                <div class="btn-group" style="margin: 0;">
                    <button class="btn-secondary" id="btnRefreshHistory" onclick="loadHistory(1)">↻ Atualizar</button>
                    <button class="btn-stop" onclick="clearAllHistory()">🗑 Limpar Todo o Histórico</button>
                </div>
            </div>

            <div class="grid-2" style="margin-bottom: 12px;">
                <div class="form-group" style="margin: 0;">
                    <label for="filterAction">Filtrar por Ação</label>
                    <select id="filterAction" onchange="loadHistory(1)">
                        <option value="">Todas as Ações</option>
                        <option value="interpret">Interpretar</option>
                        <option value="translate">Traduzir</option>
                        <option value="answer">Responder</option>
                        <option value="explain">Explicar</option>
                        <option value="summarize">Resumir</option>
                        <option value="custom">Personalizado</option>
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label for="filterStatus">Filtrar por Status</label>
                    <select id="filterStatus" onchange="loadHistory(1)">
                        <option value="">Todos os Status</option>
                        <option value="success">Sucesso (SUCCESS)</option>
                        <option value="failed">Falha (FAILED)</option>
                    </select>
                </div>
            </div>

            <div id="historyListContainer" style="margin-top: 12px;">
                <div id="historyEmpty" style="color: var(--muted-text); font-size: 14px; padding: 12px 0;">
                    Nenhum registro de análise encontrado.
                </div>
                <div id="historyList" style="display: flex; flex-direction: column; gap: 8px;"></div>
            </div>

            <div id="historyPagination" style="display: none; justify-content: space-between; align-items: center; margin-top: 16px; font-size: 13px; color: var(--muted-text);">
                <button class="btn-secondary" id="btnPrevPage" onclick="prevHistoryPage()">« Anterior</button>
                <span id="pageIndicator">Página 1 de 1</span>
                <button class="btn-secondary" id="btnNextPage" onclick="nextHistoryPage()">Próxima »</button>
            </div>
        </div>

        <div id="detailsModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
            <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 8px; max-width: 600px; width: 100%; max-height: 80vh; overflow-y: auto; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 16px;">
                    <h3 style="margin: 0;" id="modalTitle">Detalhes da Análise</h3>
                    <button class="btn-secondary" onclick="closeDetailsModal()">Fechar</button>
                </div>
                <div id="modalContent" style="font-size: 14px; line-height: 1.6;"></div>
            </div>
        </div>

        <div class="card">
            <h2>Logs & Respostas do Sistema</h2>
            <div id="logBox" class="log-box">[Sistema] Interface inicializada. Aguardando comandos...</div>
        </div>
    </div>

    <script>
        function log(message) {
            const box = document.getElementById('logBox');
            const time = new Date().toLocaleTimeString();
            box.innerText += `\n[${time}] ${message}`;
            box.scrollTop = box.scrollHeight;
        }

        function updateStateBadge(state) {
            const badge = document.getElementById('stateBadge');
            badge.className = `badge ${state.toLowerCase()}`;
            badge.innerText = state.toUpperCase();
        }

        function updateRegionDisplay(region) {
            const text = document.getElementById('activeRegionText');
            if (region && region.width > 0) {
                text.innerText = `X: ${region.x}, Y: ${region.y} | Dimensões: ${region.width}x${region.height}px`;
            } else {
                text.innerText = 'Nenhuma área selecionada';
            }
        }

        async function fetchState() {
            try {
                const res = await fetch('/api/monitor/state');
                const data = await res.json();
                updateStateBadge(data.state);
                if (data.region) updateRegionDisplay(data.region);
            } catch (e) {
                log('Erro ao buscar estado do monitor: ' + e.message);
            }
        }

        async function fetchRegion() {
            try {
                const res = await fetch('/api/monitor/region');
                const data = await res.json();
                updateRegionDisplay(data.region);
                log(data.region ? `Região ativa carregada: ${data.region.width}x${data.region.height}px` : 'Nenhuma região configurada');
            } catch (e) {
                log('Erro ao carregar região: ' + e.message);
            }
        }

        function openSelectionOverlay() {
            window.open('/selection', 'SelectionOverlay', 'width=800,height=600');
            log('Overlay de seleção aberto.');
        }

        async function saveManualRegion() {
            const x = parseInt(document.getElementById('regX').value);
            const y = parseInt(document.getElementById('regY').value);
            const width = parseInt(document.getElementById('regWidth').value);
            const height = parseInt(document.getElementById('regHeight').value);

            try {
                const res = await fetch('/api/monitor/region', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ x, y, width, height })
                });
                const data = await res.json();
                if (res.ok) {
                    updateRegionDisplay(data.region);
                    log(`Região salva: X:${x}, Y:${y}, ${width}x${height}px`);
                } else {
                    log('Erro: ' + data.error);
                }
            } catch (e) {
                log('Erro ao salvar região: ' + e.message);
            }
        }

        async function clearRegion() {
            try {
                const res = await fetch('/api/monitor/region', { method: 'DELETE' });
                const data = await res.json();
                updateRegionDisplay(null);
                log('Região limpa.');
            } catch (e) {
                log('Erro ao limpar região: ' + e.message);
            }
        }

        async function triggerCapture() {
            log('Disparando captura de tela nativa...');
            try {
                const res = await fetch('/api/monitor/capture', { method: 'POST' });
                const data = await res.json();
                if (res.ok) {
                    const details = document.getElementById('captureDetails');
                    details.style.display = 'block';
                    details.innerText = `[Captura OK] Arquivo: ${data.capture.file_path}\nHash: ${data.capture.hash}\nTimestamp: ${data.capture.timestamp}`;
                    log(`Frame capturado com sucesso: ${data.capture.file_path}`);
                } else {
                    log('Erro na captura: ' + data.error);
                }
            } catch (e) {
                log('Erro ao solicitar captura: ' + e.message);
            }
        }

        let desktopWatcherTimer = null;

        function toggleMonitorMode() {
            const mode = document.getElementById('monitorMode').value;
            const badge = document.getElementById('watcherStatusBadge');
            if (mode === 'desktop') {
                badge.innerText = 'MODO DESKTOP ATIVO';
                badge.style.color = '#38bdf8';
            } else {
                badge.innerText = 'MODO REGIÃO ATIVO';
                badge.style.color = '#a855f7';
            }
        }

        async function checkDesktopPrintsNow() {
            const actionType = document.getElementById('actionType').value;
            const instruction = document.getElementById('userInstruction')?.value || '';

            try {
                const res = await fetch('/api/monitor/watch-cycle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: actionType,
                        custom_prompt: instruction
                    })
                });

                const data = await res.json();
                if (data.detected && data.results && data.results.length > 0) {
                    for (const item of data.results) {
                        log(`📸 [Print Detectado] ${item.file} -> Analisado (${item.action}) em ${item.duration_ms}ms`);
                        
                        const statusBox = document.getElementById('analysisStatus');
                        const contentBox = document.getElementById('analysisContent');
                        statusBox.style.display = 'block';
                        statusBox.innerText = `[${item.status.toUpperCase()}] Print: ${item.file} | Ação: ${item.action} | Duração: ${item.duration_ms}ms`;
                        contentBox.innerText = item.content || item.error || 'Sem resposta gerada.';
                    }
                    loadHistory(1);
                }
            } catch (e) {
                // Silencioso
            }
        }

        async function startMonitor() {
            const mode = document.getElementById('monitorMode')?.value || 'desktop';
            const actionType = document.getElementById('actionType').value;
            const badge = document.getElementById('watcherStatusBadge');

            if (mode === 'desktop') {
                if (desktopWatcherTimer) clearInterval(desktopWatcherTimer);
                desktopWatcherTimer = setInterval(checkDesktopPrintsNow, 1000);
                badge.innerText = '🟢 ESCUTANDO PRINTS (Cmd+Shift+4)';
                badge.style.color = '#22c55e';
                log('Monitor de Desktop iniciado. Tire um print com Cmd+Shift+4 para analisar automaticamente!');
            }

            try {
                const res = await fetch('/api/monitor/start', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action_type: actionType })
                });
                const data = await res.json();
                updateStateBadge(data.state);
                log(`Monitor iniciado. Estado: ${data.state}`);
            } catch (e) {
                log('Erro ao iniciar monitor: ' + e.message);
            }
        }

        async function stopMonitor() {
            if (desktopWatcherTimer) {
                clearInterval(desktopWatcherTimer);
                desktopWatcherTimer = null;
            }
            const badge = document.getElementById('watcherStatusBadge');
            badge.innerText = 'PARADO';
            badge.style.color = '#ef4444';

            try {
                const res = await fetch('/api/monitor/stop', { method: 'POST' });
                const data = await res.json();
                updateStateBadge(data.state);
                log(`Monitor interrompido. Estado: ${data.state}`);
            } catch (e) {
                log('Erro ao parar monitor: ' + e.message);
            }
        }

        async function pauseMonitor() {
            if (desktopWatcherTimer) {
                clearInterval(desktopWatcherTimer);
                desktopWatcherTimer = null;
            }
            const badge = document.getElementById('watcherStatusBadge');
            badge.innerText = 'PAUSADO';
            badge.style.color = '#eab308';

            try {
                const res = await fetch('/api/monitor/pause', { method: 'POST' });
                const data = await res.json();
                updateStateBadge(data.state);
                log(`Monitor pausado. Estado: ${data.state}`);
            } catch (e) {
                log('Erro ao pausar monitor: ' + e.message);
            }
        }

        function toggleCustomInstruction() {
            const action = document.getElementById('actionType').value;
            const group = document.getElementById('customInstructionGroup');
            group.style.display = action === 'custom' ? 'block' : 'none';
        }

        async function runAnalysisNow() {
            const actionType = document.getElementById('actionType').value;
            const language = document.getElementById('responseLanguage').value;
            const instruction = document.getElementById('userInstruction').value;

            const statusBox = document.getElementById('analysisStatus');
            const statusText = document.getElementById('analysisStatusText');

            statusBox.style.display = 'block';
            statusText.innerText = 'Analisando...';
            log(`Iniciando análise [Modo: ${actionType}, Idioma: ${language}]...`);

            try {
                const res = await fetch('/api/ai/analyze', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action_type: actionType,
                        user_instruction: instruction,
                        language: language
                    })
                });
                const data = await res.json();

                if (res.ok && data.response && data.response.success) {
                    statusText.innerText = 'Concluído';
                    log(`[Resposta IA — ${data.response.provider}] ${data.response.text}`);
                    loadHistory(1);
                } else {
                    const errorMsg = data.error || (data.response ? data.response.error_message : 'Erro desconhecido');
                    statusText.innerText = 'Falha na Análise';
                    log(`[Erro IA] ${errorMsg}`);
                    loadHistory(1);
                }
            } catch (e) {
                statusText.innerText = 'Erro de Conexão';
                log('Erro na análise: ' + e.message);
            }
        }

        function toggleAdvancedSubmenu() {
            const content = document.getElementById('advancedSubmenuContent');
            const btn = document.getElementById('btnToggleAdvanced');
            if (content.style.display === 'none' || content.style.display === '') {
                content.style.display = 'block';
                btn.innerText = '- Ocultar';
            } else {
                content.style.display = 'none';
                btn.innerText = '+ Expandir';
            }
        }

        let currentHistoryPage = 1;
        let totalHistoryPages = 1;

        async function loadHistory(page = 1) {
            currentHistoryPage = page;
            const action = document.getElementById('filterAction')?.value || '';
            const status = document.getElementById('filterStatus')?.value || '';
            const refreshBtn = document.getElementById('btnRefreshHistory');

            if (refreshBtn) {
                refreshBtn.innerText = '↻ Carregando...';
                refreshBtn.disabled = true;
            }

            let url = `/api/history?page=${page}&per_page=5`;
            if (action) url += `&action_type=${encodeURIComponent(action)}`;
            if (status) url += `&status=${encodeURIComponent(status)}`;

            try {
                const res = await fetch(url);
                const data = await res.json();

                const list = document.getElementById('historyList');
                const empty = document.getElementById('historyEmpty');
                const pagination = document.getElementById('historyPagination');
                const pageIndicator = document.getElementById('pageIndicator');

                list.innerHTML = '';
                totalHistoryPages = data.last_page || 1;

                if (!data.items || data.items.length === 0) {
                    empty.style.display = 'block';
                    pagination.style.display = 'none';
                } else {
                    empty.style.display = 'none';
                    pagination.style.display = 'flex';
                    pageIndicator.innerText = `Página ${data.page} de ${totalHistoryPages} (${data.total} registros)`;

                    document.getElementById('btnPrevPage').disabled = data.page <= 1;
                    document.getElementById('btnNextPage').disabled = data.page >= totalHistoryPages;

                    data.items.forEach(item => {
                        const itemCard = document.createElement('div');
                        itemCard.style.cssText = 'background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 6px; padding: 12px; display: flex; justify-content: space-between; align-items: center; gap: 12px;';

                        const isSuccess = item.status === 'success';
                        const badgeColor = isSuccess ? 'var(--success)' : 'var(--danger)';
                        const previewText = item.response_text ? (item.response_text.length > 80 ? item.response_text.substring(0, 80) + '...' : item.response_text) : (item.error_message || 'Sem conteúdo');
                        const dateFormatted = item.created_at ? new Date(item.created_at).toLocaleString('pt-BR') : 'Data não informada';
                        const imageName = item.image_name ? item.image_name : 'Captura de Tela';

                        itemCard.innerHTML = `
                            <div style="display: flex; flex-direction: column; gap: 4px; overflow: hidden; flex-grow: 1;">
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <span style="font-size: 13px; font-weight: 700; color: #38bdf8;">📸 ${imageName}</span>
                                    <span style="font-size: 10px; font-weight: 700; background: var(--border-color); padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">${item.action_type}</span>
                                    <span style="font-size: 10px; font-weight: 700; color: ${badgeColor}; text-transform: uppercase;">${item.status}</span>
                                    <span style="font-size: 11px; color: var(--muted-text);">${dateFormatted}</span>
                                </div>
                                <div style="font-size: 12px; color: #cbd5e1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px;">
                                    ${previewText}
                                </div>
                            </div>
                            <div style="display: flex; gap: 6px; flex-shrink: 0;">
                                <button class="btn-secondary" style="padding: 5px 10px; font-size: 12px;" onclick="viewHistoryDetails(${item.id}, this)">Ver</button>
                                <button class="btn-stop" style="padding: 5px 10px; font-size: 12px;" onclick="deleteHistory(${item.id})">✕ Remover</button>
                            </div>
                        `;
                        list.appendChild(itemCard);
                    });
                }
            } catch (e) {
                log('Erro ao carregar histórico: ' + e.message);
            } finally {
                if (refreshBtn) {
                    refreshBtn.innerText = '↻ Atualizar';
                    refreshBtn.disabled = false;
                }
            }
        }

        function prevHistoryPage() {
            if (currentHistoryPage > 1) {
                loadHistory(currentHistoryPage - 1);
            }
        }

        function nextHistoryPage() {
            if (currentHistoryPage < totalHistoryPages) {
                loadHistory(currentHistoryPage + 1);
            }
        }

        async function viewHistoryDetails(id, btnElement) {
            const originalText = btnElement ? btnElement.innerText : 'Ver';
            if (btnElement) {
                btnElement.innerText = '⌛ Carregando...';
                btnElement.disabled = true;
            }

            try {
                const res = await fetch(`/api/history/${id}`);
                const data = await res.json();
                if (!res.ok) {
                    alert(data.error || 'Erro ao carregar detalhes');
                    return;
                }

                const item = data.item;
                const modal = document.getElementById('detailsModal');
                const content = document.getElementById('modalContent');
                const imageName = item.image_name ? item.image_name : 'Captura de Tela';

                content.innerHTML = `
                    <p><strong>Imagem:</strong> <span style="color: #38bdf8; font-weight: bold;">📸 ${imageName}</span></p>
                    <p><strong>Ação:</strong> <span style="text-transform: uppercase;">${item.action_type}</span></p>
                    <p><strong>Status:</strong> <span style="font-weight: bold; color: ${item.status === 'success' ? 'var(--success)' : 'var(--danger)'};">${item.status.toUpperCase()}</span></p>
                    <p><strong>Provedor / Modelo:</strong> ${item.provider} (${item.model || 'N/A'})</p>
                    <p><strong>Duração:</strong> ${item.duration_ms ? item.duration_ms + 'ms' : 'N/A'}</p>
                    <p><strong>Data/Hora:</strong> ${item.created_at ? new Date(item.created_at).toLocaleString('pt-BR') : 'N/A'}</p>
                    ${item.user_instruction ? `<p><strong>Instrução do Usuário:</strong> ${item.user_instruction}</p>` : ''}
                    ${item.response_text ? `<div style="margin-top: 12px;"><strong>Resposta da IA:</strong><div style="background: var(--bg-color); padding: 12px; border-radius: 6px; border: 1px solid var(--border-color); margin-top: 6px; white-space: pre-wrap; font-family: monospace; font-size: 13px; max-height: 250px; overflow-y: auto;">${item.response_text}</div></div>` : ''}
                    ${item.error_message ? `<div style="margin-top: 12px; color: var(--danger);"><strong>Mensagem de Erro:</strong> ${item.error_message}</div>` : ''}
                `;

                modal.style.display = 'flex';
            } catch (e) {
                alert('Erro ao buscar detalhes da análise: ' + e.message);
            } finally {
                if (btnElement) {
                    btnElement.innerText = originalText;
                    btnElement.disabled = false;
                }
            }
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        async function deleteHistory(id) {
            if (!confirm('Deseja realmente remover esta análise do histórico?')) return;

            try {
                const res = await fetch(`/api/history/${id}`, { method: 'DELETE' });
                const data = await res.json();
                if (res.ok) {
                    log(`Registro de histórico #${id} excluído com sucesso.`);
                    loadHistory(currentHistoryPage);
                } else {
                    alert('Erro ao excluir: ' + (data.error || 'Erro desconhecido'));
                }
            } catch (e) {
                alert('Erro ao excluir registro: ' + e.message);
            }
        }

        async function clearAllHistory() {
            if (!confirm('Tem certeza de que deseja apagar TODO o histórico de análises? Esta ação não pode ser desfeita.')) return;

            try {
                const res = await fetch('/api/history', { method: 'DELETE' });
                const data = await res.json();
                if (res.ok) {
                    log('Todo o histórico de análises foi limpo com sucesso.');
                    loadHistory(1);
                } else {
                    alert('Erro ao limpar histórico: ' + (data.error || 'Erro desconhecido'));
                }
            } catch (e) {
                alert('Erro ao limpar histórico: ' + e.message);
            }
        }

        async function fetchPipelineStats() {
            try {
                const res = await fetch('/api/monitor/pipeline');
                const data = await res.json();
                if (data.stats) {
                    document.getElementById('metricCycles').innerText = data.stats.cycles_count || 0;
                    document.getElementById('metricCaptures').innerText = data.stats.captures_count || 0;
                    document.getElementById('metricQueued').innerText = data.stats.queued_jobs_count || 0;
                    if (data.stats.debounce_stats) {
                        document.getElementById('metricDebounced').innerText = data.stats.debounce_stats.debounced_count || 0;
                    }
                }
            } catch (e) {
                // Silencioso
            }
        }

        async function triggerContinuousCycle() {
            try {
                const res = await fetch('/api/monitor/cycle', { method: 'POST' });
                const data = await res.json();
                log(`[Ciclo Contínuo] ${data.message}`);
                fetchPipelineStats();
            } catch (e) {
                log('Erro ao executar ciclo manual: ' + e.message);
            }
        }

        function openFloatingOverlay() {
            window.open('/overlay', 'ScreenAIOverlay', 'width=360,height=180,resizable=no,scrollbars=no');
            log('Overlay flutuante aberto com sucesso.');
        }

        async function checkStartupPermission() {
            try {
                const res = await fetch('/api/startup/status');
                const data = await res.json();
                const banner = document.getElementById('permWarningBanner');
                if (!data.permission_granted) {
                    banner.style.display = 'flex';
                } else {
                    banner.style.display = 'none';
                }
            } catch (e) {
                // Silencioso
            }
        }

        async function openSystemSettings() {
            try {
                await fetch('/api/startup/permissions/open-settings', { method: 'POST' });
            } catch (e) {
                alert('Erro ao abrir Ajustes: ' + e.message);
            }
        }

        async function fetchHealthStatus() {
            try {
                const res = await fetch('/api/health');
                const data = await res.json();
                const badge = document.getElementById('healthBadge');
                if (data.status === 'healthy') {
                    badge.style.color = '#22c55e';
                    badge.innerText = '● Operacional';
                } else if (data.status === 'degraded') {
                    badge.style.color = '#eab308';
                    badge.innerText = '● ' + data.message;
                } else {
                    badge.style.color = '#ef4444';
                    badge.innerText = '● ' + data.message;
                }
            } catch (e) {
                // Silencioso
            }
        }

        fetchState();
        loadHistory(1);
        fetchPipelineStats();
        checkStartupPermission();
        fetchHealthStatus();
    </script>
</body>
</html>
