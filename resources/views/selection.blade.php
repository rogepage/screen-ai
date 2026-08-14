<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Seleção de Área da Tela</title>
    <style>
        * {
            box-sizing: border-box;
            user-select: none;
        }

        body, html {
            margin: 0;
            padding: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            background-color: rgba(15, 23, 42, 0.4);
            cursor: crosshair;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .instructions {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(30, 41, 59, 0.9);
            color: #f8fafc;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            pointer-events: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
        }

        #selectionBox {
            position: absolute;
            border: 2px dashed #3b82f6;
            background-color: rgba(59, 130, 246, 0.25);
            display: none;
            pointer-events: none;
        }

        #coordsLabel {
            position: absolute;
            bottom: -24px;
            right: 0;
            background-color: #3b82f6;
            color: #ffffff;
            font-size: 11px;
            font-family: monospace;
            padding: 2px 6px;
            border-radius: 4px;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="instructions">Clique e arraste para selecionar a área do monitor</div>
    <div id="selectionBox">
        <span id="coordsLabel">0x0</span>
    </div>

    <script>
        let isSelecting = false;
        let startX = 0, startY = 0;
        const box = document.getElementById('selectionBox');
        const coords = document.getElementById('coordsLabel');

        window.addEventListener('mousedown', (e) => {
            isSelecting = true;
            startX = e.clientX;
            startY = e.clientY;
            box.style.left = `${startX}px`;
            box.style.top = `${startY}px`;
            box.style.width = '0px';
            box.style.height = '0px';
            box.style.display = 'block';
        });

        window.addEventListener('mousemove', (e) => {
            if (!isSelecting) return;

            const currentX = e.clientX;
            const currentY = e.clientY;

            const width = Math.abs(currentX - startX);
            const height = Math.abs(currentY - startY);
            const left = Math.min(startX, currentX);
            const top = Math.min(startY, currentY);

            box.style.left = `${left}px`;
            box.style.top = `${top}px`;
            box.style.width = `${width}px`;
            box.style.height = `${height}px`;

            coords.innerText = `${width}px × ${height}px (X: ${left}, Y: ${top})`;
        });

        window.addEventListener('mouseup', async (e) => {
            if (!isSelecting) return;
            isSelecting = false;

            const currentX = e.clientX;
            const currentY = e.clientY;

            const width = Math.abs(currentX - startX);
            const height = Math.abs(currentY - startY);
            const x = Math.min(startX, currentX);
            const y = Math.min(startY, currentY);

            if (width > 10 && height > 10) {
                try {
                    await fetch('/api/monitor/region', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ x, y, width, height })
                    });
                    alert(`Região selecionada: X:${x}, Y:${y}, ${width}x${height}px`);
                } catch (err) {
                    console.error('Erro ao salvar região:', err);
                }
            }

            box.style.display = 'none';
        });
    </script>
</body>
</html>
