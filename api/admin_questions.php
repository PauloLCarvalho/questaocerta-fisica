<?php
require_once __DIR__ . '/../includes/auth.php';
qc_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Método não permitido';
    exit;
}

// Require CSRF token for any POST action in admin
if (!qc_csrf_validate($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    die('CSRF token inválido ou expirado. <a href="../views/admin.php">Voltar</a>');
}

$action = $_POST['action'] ?? '';
$dataFile = __DIR__ . '/../data/questoes.json';

function load_bank($file) {
    if (!file_exists($file)) return [];
    $fp = fopen($file, 'c+');
    if (!$fp) return [];
    $raw = '';
    if (flock($fp, LOCK_SH)) {
        clearstatcache(true, $file);
        $raw = stream_get_contents($fp);
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    $bank = json_decode($raw, true);
    return is_array($bank) ? $bank : [];
}

function save_bank($file, $bank) {
    $dir = dirname($file);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $fp = fopen($file, 'c+');
    if (!$fp) return false;
    if (!flock($fp, LOCK_EX)) { fclose($fp); return false; }
    ftruncate($fp, 0);
    rewind($fp);
    $json = json_encode($bank, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $written = fwrite($fp, $json);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return $written !== false;
}

function validate_question_input($data, &$out, &$err) {
    $err = [];
    $grande_area = trim($data['grande_area'] ?? '');
    $componente = trim($data['componente'] ?? '');
    $nivel = trim($data['nivel'] ?? '');
    $enunciado = trim($data['enunciado'] ?? '');
    $alts = $data['alt'] ?? [];
    $gabarito = isset($data['gabarito']) ? intval($data['gabarito']) : 0;
    $numero = isset($data['numero']) ? intval($data['numero']) : null;
    
    if ($grande_area === '') $err[] = 'Grande área obrigatória';
    if ($componente === '') $err[] = 'Componente obrigatório';
    if ($nivel === '') $err[] = 'Nível obrigatório';
    if ($enunciado === '') $err[] = 'Enunciado obrigatório';
    if ($numero === null || $numero <= 0) $err[] = 'Número da questão inválido';
    
    $alternativas = [];
    if (!is_array($alts)) $alts = [];
    foreach ($alts as $a) { $a = trim($a); if ($a !== '') $alternativas[] = $a; }
    if (count($alternativas) < 2) $err[] = 'Pelo menos duas alternativas são obrigatórias';
    if ($gabarito < 0 || $gabarito >= count($alternativas)) $err[] = 'Índice do gabarito fora do intervalo';
    
    // Gerar ID no formato: {grande_area}-{componente}-{numero}
    $id = "{$grande_area}-{$componente}-{$numero}";
    
    $out = [
        'id' => $id,
        'grande_area' => $grande_area,
        'componente' => $componente,
        'nivel' => $nivel,
        'numero' => $numero,
        'ano' => isset($data['ano']) ? intval($data['ano']) : null,
        'enunciado' => $enunciado,
        'alternativas' => $alternativas,
        'gabarito' => $gabarito,
        'dica' => trim($data['dica'] ?? '')
    ];
    return count($err) === 0;
}

if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

if ($action === 'add') {
    $ok = validate_question_input($_POST, $q, $errs);
    if (!$ok) { http_response_code(400); echo implode('; ', $errs); exit; }
    
    $bank = load_bank($dataFile);
    
    // Verificar se já existe questão com mesmo ID
    foreach ($bank as $existing) {
        if (isset($existing['id']) && $existing['id'] === $q['id']) {
            http_response_code(400);
            echo "Já existe uma questão com ID {$q['id']}";
            exit;
        }
    }
    
    $bank[] = $q;
    if (!save_bank($dataFile, $bank)) { http_response_code(500); echo 'Falha ao salvar arquivo.'; exit; }
    header('Location: ../views/admin.php?saved=1'); exit;

} elseif ($action === 'delete') {
    $id = $_POST['id'] ?? '';
    if (!$id) { http_response_code(400); echo 'ID ausente'; exit; }
    $bank = load_bank($dataFile);
    $found = false;
    foreach ($bank as $i => $item) {
        if (isset($item['id']) && $item['id'] === $id) {
            array_splice($bank, $i, 1);
            $found = true;
            break;
        }
    }
    if (!$found) { http_response_code(404); echo 'Questão não encontrada'; exit; }
    if (!save_bank($dataFile, $bank)) { http_response_code(500); echo 'Falha ao salvar'; exit; }
    header('Location: ../views/admin.php?deleted=1'); exit;

} elseif ($action === 'edit') {
    $id = $_POST['id'] ?? '';
    if (!$id) { http_response_code(400); echo 'ID ausente'; exit; }
    $ok = validate_question_input($_POST, $q, $errs);
    if (!$ok) { http_response_code(400); echo implode('; ', $errs); exit; }
    $bank = load_bank($dataFile);
    $found = false;
    foreach ($bank as $i => $item) {
        if (isset($item['id']) && $item['id'] === $id) {
            // Se o ID mudou (área/componente/número alterados), verificar duplicata
            if ($q['id'] !== $id) {
                foreach ($bank as $existing) {
                    if (isset($existing['id']) && $existing['id'] === $q['id']) {
                        http_response_code(400);
                        echo "Já existe uma questão com ID {$q['id']}";
                        exit;
                    }
                }
            }
            $bank[$i] = $q;
            $found = true;
            break;
        }
    }
    if (!$found) { http_response_code(404); echo 'Questão não encontrada'; exit; }
    if (!save_bank($dataFile, $bank)) { http_response_code(500); echo 'Falha ao salvar'; exit; }
    header('Location: ../views/admin.php?edited=1'); exit;

} else {
    http_response_code(400);
    echo 'Invalid action';
    exit;
}

