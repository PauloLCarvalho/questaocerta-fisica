<?php
require_once __DIR__ . '/../includes/auth.php';
qc_require_admin();

// Simple admin API to add questions to data/questoes.json
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$action = $_POST['action'] ?? '';
if ($action !== 'add') {
    http_response_code(400);
    echo 'Invalid action';
    exit;
}

$materia = $_POST['materia'] ?? '';
$numero = intval($_POST['numero'] ?? 0);
$ano = intval($_POST['ano'] ?? 0);
$enunciado = trim($_POST['enunciado'] ?? '');
$alts = $_POST['alt'] ?? [];
$dica = trim($_POST['dica'] ?? '');
$gabarito = isset($_POST['gabarito']) ? intval($_POST['gabarito']) : 0;

if (!$materia || !$enunciado || count($alts) < 2) {
    http_response_code(400);
    echo 'Missing required fields';
    exit;
}

// sanitize alternatives: keep non-empty
$alternativas = [];
foreach ($alts as $a) {
    $a = trim($a);
    if ($a !== '') $alternativas[] = $a;
}
if (count($alternativas) < 2) {
    http_response_code(400);
    echo 'Provide at least two alternatives';
    exit;
}

// Build new question object
$new = [
    'id' => uniqid('', true),
    'numero' => $numero ?: null,
    'ano' => $ano ?: null,
    'materia' => $materia,
    'enunciado' => $enunciado,
    'alternativas' => $alternativas,
    'gabarito' => $gabarito,
    'dica' => $dica
];

$dataFile = __DIR__ . '/../data/questoes.json';
if (!is_writable($dataFile) && !is_writable(dirname($dataFile))) {
    // not writable; show a friendly error
    // attempt to continue but fail
    http_response_code(500);
    echo 'Server: data file not writable.';
    exit;
}

$raw = file_get_contents($dataFile);
if ($raw === false) {
    http_response_code(500);
    echo 'Failed to read data file.';
    exit;
}

$bank = json_decode($raw, true);
if (!is_array($bank)) $bank = [];
if (!isset($bank[$materia]) || !is_array($bank[$materia])) $bank[$materia] = [];

// push to end
$bank[$materia][] = $new;

// save back
$ok = file_put_contents($dataFile, json_encode($bank, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
if ($ok === false) {
    http_response_code(500);
    echo 'Failed to write data file.';
    exit;
}

// redirect back to admin with success
header('Location: ../views/admin.html?saved=1');
exit;
