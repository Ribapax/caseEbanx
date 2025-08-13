# API PHP 8.4 “Bank Events” (roteador próprio + camadas limpas)
## Visão geral
Esta API em PHP 8.4 puro implementa os endpoints do desafio de “saldo/depósito/saque/transferência”, com roteamento leve (sem frameworks) e um desenho em camadas:
- Domain: regras e entidades (Account).
- Application: orquestra casos de uso e regras de negócio (AccountService).
- Infra: persistência não persistente (em memória via tmpfs usando arquivos temporários) através de um repositório.
- Delivery (HTTP): handlers HTTP como classes invocáveis (cada rota → uma classe com __invoke), mais um micro-router.
A escolha por camadas separa regra de negócio de transporte (HTTP) e de armazenamento. Assim, trocar a persistência (ex.: APCu, Redis, ou banco real) vira apenas trocar a implementação de AccountRepository.
## Objetivos e decisões
- Sem frameworks para reduzir dependências; apenas PHP 8.4 + Apache no Docker.
- Roteamento próprio e mínimo, mas com ergonomia:
    - Handlers como classes (__invoke) para facilitar injeção de dependências.
    - .htaccess redireciona tudo para public/index.php.
- Não persistente por padrão:
    - Repositório usa um arquivo JSON em diretório tmpfs montado no container (memória), garantindo que os dados somem ao reiniciar.
- Contratos de resposta compatíveis com os testes:
    - GET /balance → texto (não JSON): “200 20” ou “404 0”.
    - POST /event → 201 com JSON; se conta de origem não existe em withdraw/transfer, retorna 404 com corpo "0".
    - POST /reset → 200 OK (texto).
- declare(strict_types=1); em todos os arquivos para tipagem estrita (evita coerção automática de tipos escalares).
