<?php
declare(strict_types=1);

/**
 * Mini roteador funcional, sem dependências.
 * Suporta:
 *  - route('GET', '/users/{id}', callback, ['constraints'=>['id'=>'\d+']])
 *  - group('/v1', fn() => route(...))
 *  - dispatch(); // executa a rota
 */

$__ROUTES = [];
$__GROUP_PREFIX = '';

function route(string|array $method, string $pattern, callable $handler, array $opts = []): void {
    global $__ROUTES, $__GROUP_PREFIX;

    $methods = array_map('strtoupper', (array) $method);

    // Aplica prefixo de grupo e normaliza
    $pattern = normalize_pattern($__GROUP_PREFIX . $pattern);

    // Ordens dos parâmetros {nome} no padrão
    preg_match_all('/\{([A-Za-z_][A-Za-z0-9_]*)\}/', $pattern, $m);
    $paramOrder = $m[1] ?? [];

    // Compila o padrão para regex, com constraints opcionais
    $regex = compile_pattern($pattern, $opts['constraints'] ?? []);

    $__ROUTES[] = [
        'methods'     => $methods,
        'pattern'     => $pattern,
        'regex'       => $regex,
        'handler'     => $handler,
        'paramOrder'  => $paramOrder,
    ];
}

function dispatch(string $basePath = ''): void {
    global $__ROUTES;

    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

    // Remove basePath, se fornecido
    if ($basePath !== '' && str_starts_with($uri, $basePath)) {
        $uri = substr($uri, strlen($basePath)) ?: '/';
    }
    $uri = normalize_pattern($uri);

    $pathMatched = false;
    $allowedForPath = [];

    foreach ($__ROUTES as $r) {
        if (!preg_match($r['regex'], $uri, $matches)) {
            continue;
        }
        $pathMatched = true;
        $allowedForPath = array_merge($allowedForPath, $r['methods']);

        if (!in_array($method, $r['methods'], true)) {
            continue; // método não permitido para este path
        }

        // Monta args na ordem dos placeholders do padrão
        $args = [];
        foreach ($r['paramOrder'] as $name) {
            $args[] = $matches[$name] ?? null;
        }

        // Chama a rota
        $r['handler'](...$args);
        return; // encerra após encontrar a primeira rota válida
    }

    // CORS/OPTIONS automático para paths conhecidos
    if ($method === 'OPTIONS' && $pathMatched) {
        header('Allow: ' . implode(', ', array_unique($allowedForPath)));
        http_response_code(204);
        exit;
    }

    if ($pathMatched && !empty($allowedForPath)) {
        header('Allow: ' . implode(', ', array_unique($allowedForPath)));
        json(['error' => 'Método não permitido'], 405);
    }

    json(['error' => 'Rota não encontrada'], 404);
}

/* -------------------- Helpers -------------------- */

function normalize_pattern(string $p): string {
    $p = '/' . ltrim($p, '/');
    return rtrim($p, '/') ?: '/';
}

function compile_pattern(string $pattern, array $constraints): string {
    // Marca placeholders
    $tmp = preg_replace_callback('/\{([A-Za-z_][A-Za-z0-9_]*)\}/', fn($m) =>
        '___PARAM_' . $m[1] . '___', $pattern
    );

    // Escapa o restante literalmente
    $escaped = preg_quote($tmp, '#');

    // Substitui marcadores por grupos nomeados com constraints
    $rx = preg_replace_callback('/___PARAM_([A-Za-z_][A-Za-z0-9_]*)___/', function ($m) use ($constraints) {
        $name = $m[1];
        $c = $constraints[$name] ?? '[^/]+';
        return '(?P<' . $name . '>' . $c . ')';
    }, $escaped);

    return '#^' . $rx . '$#';
}

function json(mixed $data, int $status = 200, array $headers = []): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    foreach ($headers as $k => $v) header($k . ': ' . $v);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

