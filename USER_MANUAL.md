# Manual do Usuário — Screen AI (macOS)

Bem-vindo ao **Screen AI**, a ferramenta inteligente de interpretação visual contínua, análise de tela e assistência multimodal desenvolvida nativamente para o **macOS**.

---

## 1. Visão Geral e Principais Recursos

O **Screen AI** monitora uma área selecionada da sua tela, detecta mudanças visuais relevantes de forma inteligente e consulta um modelo de Inteligência Artificial para fornecer interpretações, explicações, traduções ou respostas em tempo real.

### Principais Funcionalidades:
- **Seleção Flexível de Região**: Escolha qualquer janela, aplicativo ou coordenada do seu monitor.
- **Detecção Inteligente de Mudanças com Debounce**: Evita requisições repetitivas ou instáveis enquanto você ainda está digitando ou navegando.
- **Modos de Análise Versáteis**: Interpretação geral, tradução instantânea, solução de dúvidas, resumos e prompts personalizados.
- **Experiência Nativa no macOS**: Ícone na Barra de Menus (*MenuBar*), Overlay flutuante com suporte a transparência e Atalhos Globais de Teclado.
- **Histórico Persistente**: Consulte, filtre e revise todas as suas análises passadas.
- **Privacidade Rigorosa**: Nenhuma imagem é retida permanentemente ou enviada para servidores externos além da API de IA configurada.

---

## 2. Primeiros Passos e Assistente de Onboarding

Ao abrir o Screen AI pela primeira vez, o assistente passo a passo (*Onboarding*) será exibido:

```text
  [ Boas-Vindas ] ──► [ Permissão de Tela ] ──► [ Configurar IA ] ──► [ Atalhos ] ──► [ Pronto! ]
```

### 2.1 Concedendo Permissão de Gravação de Tela
O macOS exige permissão explícita para capturar áreas do monitor:
1. Quando solicitado pelo aplicativo, clique em **Abrir Ajustes do Sistema**.
2. Nos **Ajustes do macOS**, navegue até **Privacidade e Segurança $\rightarrow$ Gravação de Tela**.
3. Ative a chave correspondente ao **Screen AI**.
4. Retorne ao aplicativo para continuar.

### 2.2 Configurando o Provedor de IA
Você pode escolher entre:
- **Google Gemini**: Para análises visuais de alta qualidade e interpretações complexas (requer chave de API).
- **Mock (Simulador Local)**: Para testar o aplicativo e seus atalhos sem custo ou sem internet.

---

## 3. Guia de Uso no Dia a Dia

O Screen AI suporta dois modos práticos de operação:

### 3.1 Modo A: Monitor de Capturas do Desktop (Recomendado — Cmd+Shift+4)

Neste modo, você não precisa configurar coordenadas de tela. Você tira prints normalmente com os atalhos nativos do macOS e o Screen AI detecta e analisa automaticamente:

1. No painel principal, selecione **Modo de Operação: 📂 Monitorar Pasta Desktop**.
2. A pasta padrão monitorada é `/Users/roger/Desktop` (onde o macOS salva seus prints).
3. Escolha a ação desejada no seletor (ex: *Explicar*, *Traduzir*, *Responder*, *Interpretar*).
4. Clique em **Iniciar Monitoramento** (ou deixe rodando em segundo plano).
5. A qualquer momento, pressione **`Cmd + Shift + 4`** no macOS para selecionar e fotografar qualquer parte da sua tela.
6. O **Screen AI** detecta o novo arquivo salvo no Desktop, envia para a IA e exibe a resposta instantaneamente no painel e no histórico!

---

### 3.2 Modo B: Captura Contínua de Região (ScreenCaptureKit)

Para monitoramento automatizado de uma janela ou região fixa:

1. No painel principal, clique em **Selecionar Região**.
2. Clique e arraste o mouse sobre a área que deseja monitorar e clique em **Confirmar Seleção**.
3. Clique em **Iniciar Monitor**. A cada alteração estável na tela, o frame é analisado pela IA.

---

### 3.3 Análise Sob Demanda (*Analyze Now*)
Se você não deseja monitorar continuamente, mas quer analisar o que está na tela naquele momento exato:
- Pressione o atalho global **`Cmd + Shift + A`** (ou clique em **Capturar e Analisar Agora** no painel).

### 3.4 Pausando e Retomando
- Para pausar temporariamente o monitor: clique em **Pausar** ou pressione **`Cmd + Shift + P`**.

---

## 4. Modos de Ação da Inteligência Artificial

| Modo | Finalidade | Exemplo de Aplicação |
| :--- | :--- | :--- |
| **Interpretar** | Visão geral e descrição do que está acontecendo na tela | Dashboards, gráficos, telas de software |
| **Traduzir** | Tradução do texto visível na região para o seu idioma | Artigos, documentos em inglês/outros idiomas |
| **Responder** | Solução de exercícios, testes ou formulários na tela | Perguntas técnicas, quizzes, formulários |
| **Explicar** | Explicação didática de código, fórmulas ou termos | Trechos de código no VS Code, fórmulas matemáticas |
| **Resumir** | Síntese dos pontos principais do conteúdo exibido | Textos longos, logs extensos, apresentações |
| **Personalizado** | Instruções livres definidas por você | *"Aponte apenas os erros de sintaxe nesta tela"* |

---

## 5. Recursos Nativos do macOS

### 5.1 Atalhos Globais de Teclado

Os atalhos funcionam mesmo quando o Screen AI está em segundo plano ou minimizado:

| Atalho | Ação |
| :--- | :--- |
| **`Cmd + Shift + M`** | Iniciar ou Parar o Monitoramento Contínuo |
| **`Cmd + Shift + P`** | Pausar ou Retomar o Monitoramento |
| **`Cmd + Shift + A`** | Executar Análise Imediata (*Analyze Now*) |
| **`Cmd + Shift + O`** | Abrir ou Fechar o Overlay Flutuante |

### 5.2 Overlay Flutuante (*Floating Window*)
- Uma janela compacta, sempre visível sobre outros apps (*Always-on-Top*), que exibe a última resposta gerada pela IA e o status atual do monitor.
- Pode ser posicionada em qualquer canto da tela através das **Configurações**.

### 5.3 Barra de Menus (*MenuBar*)
- O ícone do Screen AI permanece no canto superior direito da tela do macOS.
- Permite iniciar/parar o monitoramento, acionar o Overlay ou abrir as Configurações com um clique.

---

## 6. Histórico de Análises

- Todas as análises realizadas ficam disponíveis na aba **Histórico**.
- Você pode:
  - **Revisar** a resposta e o horário de cada análise.
  - **Filtrar** por tipo de ação realizada.
  - **Excluir** itens individuais ou limpar todo o histórico com um clique.
  - Configurar a **retenção automática** (ex: manter sempre, apagar após 7 dias ou 30 dias).

---

## 7. Painel de Configurações e Diagnóstico

Acesse **Configurações** no painel principal ou no menu para ajustar:

- **Geral**: Idioma da interface (Português, Inglês, Espanhol) e posição do Overlay.
- **Monitoramento**: Intervalo de captura (ms), tempo de debounce (estabilização) e sensibilidade de mudança.
- **Inteligência Artificial**: Seleção do modelo (Gemini Flash / Pro), chave de API e tempo limite (*timeout*).
- **Diagnóstico**: Exibição do estado operacional do sistema, métricas de latência, botão de exportação em `.JSON` e botão para **Limpar Arquivos Temporários**.

---

## 8. Dúvidas Frequentes (FAQ)

**P: O Screen AI consome muita bateria ou memória?**
> R: Não. Graças à tecnologia de detecção perceptual de mudanças, imagens só são processadas quando há alterações visíveis reais, economizando CPU, rede e bateria.

**P: Minhas capturas de tela ficam salvas na nuvem?**
> R: Não. As imagens processadas são transmitidas de forma efêmera diretamente para a API de IA escolhida e os arquivos temporários locais são removidos automaticamente pelo aplicativo.

**P: O aplicativo funciona sem internet?**
> R: Você pode selecionar o provedor **Mock** nas configurações para usar todos os recursos de captura, overlay e atalhos localmente sem necessidade de conexão.
