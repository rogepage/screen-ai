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
            <h1>Screen AI — Painel de Controle</h1>
            <span id="stateBadge" class="badge stopped">STOPPED</span>
        </header>

        <div class="card">
            <h2>Controle do Monitor</h2>
            <div class="btn-group">
                <button class="btn-start" onclick="startMonitor()">Iniciar Monitor</button>
                <button class="btn-pause" onclick="pauseMonitor()">Pausar</button>
                <button class="btn-stop" onclick="stopMonitor()">Parar</button>
            </div>
            <div style="font-size: 13px; color: var(--muted-text);">
                Provedor de IA Ativo: <strong>mock</strong> | Armazenamento de chaves: <strong>macOS Keychain (Produção)</strong>
            </div>
        </div>

        <div class="card">
            <h2>Seleção de Área da Tela (Fase 3)</h2>
            <div style="margin-bottom: 16px; font-size: 14px;">
                Região Ativa: <strong id="activeRegionText" style="color: #38bdf8;">Nenhuma área selecionada</strong>
            </div>

            <div class="btn-group">
                <button class="btn-analyze" onclick="openSelectionOverlay()">Abrir Overlay de Seleção</button>
                <button class="btn-secondary" onclick="fetchRegion()">Atualizar Dados</button>
                <button class="btn-stop" onclick="clearRegion()">Limpar Seleção</button>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="regX">Coordenada X</label>
                    <input type="number" id="regX" value="100" min="0" />
                </div>
                <div class="form-group">
                    <label for="regY">Coordenada Y</label>
                    <input type="number" id="regY" value="100" min="0" />
                </div>
                <div class="form-group">
                    <label for="regWidth">Largura (px)</label>
                    <input type="number" id="regWidth" value="800" min="1" />
                </div>
                <div class="form-group">
                    <label for="regHeight">Altura (px)</label>
                    <input type="number" id="regHeight" value="600" min="1" />
                </div>
            </div>
            <button class="btn-secondary" onclick="saveManualRegion()">Definir Coordenadas Manuais</button>
        </div>

        <div class="card">
            <h2>Captura Nativa de Tela (ScreenCaptureKit - Fase 4)</h2>
            <div class="btn-group">
                <button class="btn-start" onclick="triggerCapture()">Capturar Frame da Área Selecionada</button>
            </div>
            <div id="captureDetails" class="capture-info" style="display: none;">
                Nenhuma captura realizada nesta sessão.
            </div>
        </div>

        <div class="card">
            <h2>Orquestração e Modos de Análise de IA (Fase 8)</h2>
            <div class="grid-2">
                <div class="form-group">
                    <label for="actionType">Modo / Ação de Análise</label>
                    <select id="actionType" onchange="toggleCustomInstruction()">
                        <option value="interpret">Interpretar Conteúdo Visual</option>
                        <option value="translate">Traduzir Texto Presente</option>
                        <option value="answer">Responder Pergunta Exibida</option>
                        <option value="explain">Explicar Conteúdo / Erro / Código</option>
                        <option value="summarize">Resumir Conteúdo da Tela</option>
                        <option value="custom">Instrução Personalizada (Custom)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="responseLanguage">Idioma da Resposta</label>
                    <select id="responseLanguage">
                        <option value="pt-BR" selected>Português (Brasil)</option>
                        <option value="en-US">English (US)</option>
                        <option value="es-ES">Español</option>
                        <option value="fr-FR">Français</option>
                    </select>
                </div>
            </div>

            <div class="form-group" id="customInstructionGroup" style="display: none;">
                <label for="userInstruction">Instrução Personalizada do Usuário</label>
                <textarea id="userInstruction" rows="3" placeholder="Ex: Explique o erro exibido nesta imagem de forma didática..."></textarea>
            </div>

            <div class="btn-group">
                <button class="btn-analyze" id="btnAnalyzeNow" onclick="runAnalysisNow()">Analisar Agora</button>
            </div>

            <div id="analysisStatus" style="display: none; font-size: 13px; margin-top: 8px; color: var(--muted-text);">
                Status: <span id="analysisStatusText">Pronto</span>
            </div>
        </div>

        <div class="card">
            <h2>Experiência Nativa do macOS (Fase 11)</h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div style="background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 6px; padding: 10px;">
                    <div style="font-size: 12px; font-weight: 700; color: #38bdf8; margin-bottom: 6px;">ATALHOS GLOBAIS ATIVOS</div>
                    <ul style="margin: 0; padding-left: 16px; font-size: 13px; color: #e2e8f0; line-height: 1.6;">
                        <li><kbd style="background: var(--border-color); padding: 2px 5px; border-radius: 3px;">⌘ + Shift + S</kbd> : Iniciar / Parar</li>
                        <li><kbd style="background: var(--border-color); padding: 2px 5px; border-radius: 3px;">⌘ + Shift + P</kbd> : Pausar / Resumir</li>
                        <li><kbd style="background: var(--border-color); padding: 2px 5px; border-radius: 3px;">⌘ + Shift + A</kbd> : Analisar Agora</li>
                        <li><kbd style="background: var(--border-color); padding: 2px 5px; border-radius: 3px;">⌘ + Shift + O</kbd> : Alternar Overlay</li>
                    </ul>
                </div>
                <div style="background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 6px; padding: 10px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="font-size: 12px; font-weight: 700; color: #38bdf8; margin-bottom: 6px;">OVERLAY FLUTUANTE DISCRETO</div>
                        <div style="font-size: 13px; color: var(--muted-text); line-height: 1.4;">
                            Janela nativa translúcida com indicador de status em tempo real e proteção anti-loop de captura.
                        </div>
                    </div>
                    <div class="btn-group" style="margin: 0; margin-top: 10px;">
                        <button class="btn-secondary" onclick="openFloatingOverlay()">Abrir Janela de Overlay</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h2 style="margin: 0;">Histórico de Análises (Fase 9)</h2>
                <div class="btn-group" style="margin: 0;">
                    <button class="btn-secondary" onclick="loadHistory(1)">Atualizar</button>
                    <button class="btn-stop" onclick="clearAllHistory()">Limpar Todo o Histórico</button>
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

        async function startMonitor() {
            const actionType = document.getElementById('actionType').value;
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

        let currentHistoryPage = 1;
        let totalHistoryPages = 1;

        async function loadHistory(page = 1) {
            currentHistoryPage = page;
            const action = document.getElementById('filterAction').value;
            const status = document.getElementById('filterStatus').value;

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
                        itemCard.style.cssText = 'background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 6px; padding: 10px; display: flex; justify-content: space-between; align-items: center;';

                        const isSuccess = item.status === 'success';
                        const badgeColor = isSuccess ? 'var(--success)' : 'var(--danger)';
                        const previewText = item.response_text ? (item.response_text.length > 70 ? item.response_text.substring(0, 70) + '...' : item.response_text) : (item.error_message || 'Sem conteúdo');
                        const dateFormatted = item.created_at ? new Date(item.created_at).toLocaleString('pt-BR') : 'Data não informada';

                        itemCard.innerHTML = `
                            <div style="display: flex; flex-direction: column; gap: 4px; overflow: hidden; margin-right: 12px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 11px; font-weight: 700; background: var(--border-color); padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">${item.action_type}</span>
                                    <span style="font-size: 11px; font-weight: 700; color: ${badgeColor}; text-transform: uppercase;">${item.status}</span>
                                    <span style="font-size: 12px; color: var(--muted-text);">${dateFormatted}</span>
                                </div>
                                <div style="font-size: 13px; color: #f1f5f9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    ${previewText}
                                </div>
                            </div>
                            <div style="display: flex; gap: 6px; flex-shrink: 0;">
                                <button class="btn-secondary" style="padding: 4px 8px; font-size: 12px;" onclick="viewHistoryDetails(${item.id})">Ver</button>
                                <button class="btn-stop" style="padding: 4px 8px; font-size: 12px;" onclick="deleteHistory(${item.id})">✕</button>
                            </div>
                        `;
                        list.appendChild(itemCard);
                    });
                }
            } catch (e) {
                log('Erro ao carregar histórico: ' + e.message);
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

        async function viewHistoryDetails(id) {
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

                content.innerHTML = `
                    <p><strong>Ação:</strong> <span style="text-transform: uppercase;">${item.action_type}</span></p>
                    <p><strong>Status:</strong> <span style="font-weight: bold; color: ${item.status === 'success' ? 'var(--success)' : 'var(--danger)'};">${item.status.toUpperCase()}</span></p>
                    <p><strong>Provedor / Modelo:</strong> ${item.provider} (${item.model || 'N/A'})</p>
                    <p><strong>Duração:</strong> ${item.duration_ms ? item.duration_ms + 'ms' : 'N/A'}</p>
                    <p><strong>Data/Hora:</strong> ${item.created_at ? new Date(item.created_at).toLocaleString('pt-BR') : 'N/A'}</p>
                    ${item.user_instruction ? `<p><strong>Instrução do Usuário:</strong> ${item.user_instruction}</p>` : ''}
                    ${item.response_text ? `<div style="margin-top: 12px;"><strong>Resposta da IA:</strong><div style="background: var(--bg-color); padding: 12px; border-radius: 6px; border: 1px solid var(--border-color); margin-top: 6px; white-space: pre-wrap; font-family: monospace; font-size: 13px;">${item.response_text}</div></div>` : ''}
                    ${item.error_message ? `<div style="margin-top: 12px; color: var(--danger);"><strong>Mensagem de Erro:</strong> ${item.error_message}</div>` : ''}
                `;

                modal.style.display = 'flex';
            } catch (e) {
                alert('Erro ao buscar detalhes da análise: ' + e.message);
            }
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        async function deleteHistory(id) {
            if (!confirm('Deseja realmente excluir este registro de análise?')) return;

            try {
                const res = await fetch(`/api/history/${id}`, { method: 'DELETE' });
                const data = await res.json();
                if (res.ok) {
                    log(`Registro de histórico #${id} excluído com sucesso.`);
                    loadHistory(currentHistoryPage);
                } else {
                    log('Erro ao excluir: ' + (data.error || 'Erro desconhecido'));
                }
            } catch (e) {
                log('Erro ao excluir registro: ' + e.message);
            }
        }

        async function clearAllHistory() {
            if (!confirm('Tem certeza que deseja apagar todo o histórico de análises? Esta ação não pode ser desfeita.')) return;

            try {
                const res = await fetch('/api/history', { method: 'DELETE' });
                const data = await res.json();
                if (res.ok) {
                    log('Todo o histórico de análises foi limpo com sucesso.');
                    loadHistory(1);
                } else {
                    log('Erro ao limpar histórico: ' + (data.error || 'Erro desconhecido'));
                }
            } catch (e) {
                log('Erro ao limpar histórico: ' + e.message);
            }
        }

        function openFloatingOverlay() {
            window.open('/overlay', 'ScreenAIOverlay', 'width=360,height=180,resizable=no,scrollbars=no');
            log('Overlay flutuante aberto com sucesso.');
        }

        fetchState();
        loadHistory(1);
    </script>
</body>
</html>
