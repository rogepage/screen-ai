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
            <h2>Simulação de Análise de IA (Mock)</h2>
            <div class="form-group">
                <label for="actionType">Tipo de Ação</label>
                <select id="actionType">
                    <option value="interpret">Interpretar Conteúdo</option>
                    <option value="translate">Traduzir Texto</option>
                    <option value="answer">Responder Pergunta</option>
                    <option value="explain">Explicar Código / Erro</option>
                    <option value="summarize">Resumir Conteúdo</option>
                    <option value="custom">Instrução Personalizada</option>
                </select>
            </div>
            <div class="form-group">
                <label for="userInstruction">Instrução de Teste</label>
                <input type="text" id="userInstruction" value="Explique o que esta tela está apresentando." />
            </div>
            <button class="btn-analyze" onclick="simulateAnalysis()">Disparar Análise Mock</button>
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

        async function simulateAnalysis() {
            const actionType = document.getElementById('actionType').value;
            const instruction = document.getElementById('userInstruction').value;

            log(`Iniciando análise simulada [${actionType}]...`);

            try {
                const res = await fetch('/api/ai/analyze', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action_type: actionType, user_instruction: instruction })
                });
                const data = await res.json();
                log(`[Resposta IA] Provedor: ${data.response.provider} (${data.response.model})`);
                log(`[Texto] ${data.response.text}`);
            } catch (e) {
                log('Erro ao simular análise: ' + e.message);
            }
        }

        fetchState();
    </script>
</body>
</html>
