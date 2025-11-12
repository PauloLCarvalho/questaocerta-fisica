<?php
// API pública para carregar questões (somente leitura)
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

// Retornar questões
echo $raw;
?>
