<?php
// Força erros para tipagem de variáveis
declare(strict_types=1);

require __DIR__ . '/../app/Autoload.php';
require __DIR__ . '/../router.php';

use App\Infra\MemoryAccountRepository;
use App\Application\AccountService;
use App\Handlers\{Balance, Event, Reset};

// Creação do repository para isolar a regra de negócios da requisição
$repo = new MemoryAccountRepository();
$svc  = new AccountService($repo);

route('POST', '/reset',   new Reset($svc));
route('GET',  '/balance', new Balance($svc));
route('POST', '/event',   new Event($svc));

route('GET', '/', function () {
    header('Content-Type: text/plain; charset=utf-8');
    echo "OK";
    exit;
});

dispatch();

