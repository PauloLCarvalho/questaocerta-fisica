<?php
// API pública para carregar questões (somente leitura) com filtros opcionais
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$dataFile = __DIR__ . '/../data/questoes.json';

if (!file_exists($dataFile)) {
    http_response_code(404);
    echo json_encode(['error' => 'Arquivo de questões não encontrado']);
    exit;
}

$raw = file_get_contents($dataFile);
if ($raw === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao ler arquivo']);
    exit;
}

// Validar JSON
$questoes = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['error' => 'JSON inválido: ' . json_last_error_msg()]);
    exit;
}

// Filtros opcionais via querystring
$id = $_GET['id'] ?? '';
$materia = $_GET['materia'] ?? $_GET['grande_area'] ?? '';
$componente = $_GET['componente'] ?? '';
$nivelParam = $_GET['nivel'] ?? '';
$niveis = array_values(array_filter(array_map('trim', explode(',', $nivelParam)))); // suporta múltiplos níveis separados por vírgula

if ($id || $materia || $componente || !empty($niveis)) {
    $questoes = array_values(array_filter($questoes, function ($q) use ($id, $materia, $componente, $niveis) {
        if ($id && (!isset($q['id']) || $q['id'] !== $id)) return false;
        if ($materia) {
            $ga = $q['grande_area'] ?? ($q['materia'] ?? '');
            if ($ga !== $materia) return false;
        }
        if ($componente && (($q['componente'] ?? '') !== $componente)) return false;
        if (!empty($niveis) && (!isset($q['nivel']) || !in_array($q['nivel'], $niveis))) return false;
        return true;
    }));
}

echo json_encode($questoes, JSON_UNESCAPED_UNICODE);
?>
