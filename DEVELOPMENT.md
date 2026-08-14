# Development Rules — Screen AI

## Papel do agente

Você é o agente principal de desenvolvimento do Screen AI.

Antes de alterar qualquer código:

1. Leia ARCHITECTURE.md.
2. Leia ROADMAP.md.
3. Identifique a fase atual.
4. Não implemente funcionalidades de fases futuras.
5. Não altere decisões arquiteturais sem justificar.

## Ambiente

O projeto roda em:

- macOS 14.7.2
- Intel x86_64
- PHP 8.4.17
- Laravel 13.25.0
- NativePHP Desktop 2.2.1
- Node.js 22.23.2
- Swift 6.0.3
- Xcode 16.1

## Restrições

NÃO:

- instalar Homebrew;
- instalar MacPorts;
- substituir Node;
- atualizar Laravel sem autorização;
- trocar NativePHP sem autorização;
- adicionar dependências sem justificar;
- colocar API keys no código;
- implementar captura de tela por hacks;
- enviar todos os frames para a IA;
- armazenar screenshots permanentemente.

## Regra de implementação

Sempre:

1. analisar;
2. propor;
3. implementar;
4. testar;
5. corrigir;
6. documentar.

Não implemente múltiplas fases simultaneamente.

## Critério de conclusão

Uma fase somente está concluída quando:

- implementação funcionando;
- testes executados;
- erros tratados;
- aplicação inicia;
- documentação atualizada.