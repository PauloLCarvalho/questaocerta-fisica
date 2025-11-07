<?php
require_once __DIR__ . '/../includes/auth.php';
qc_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
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
    $materia = trim($data['materia'] ?? '');
    $enunciado = trim($data['enunciado'] ?? '');
    $alts = $data['alt'] ?? [];
    $gabarito = isset($data['gabarito']) ? intval($data['gabarito']) : 0;
    if ($materia === '') $err[] = 'Materia is required';
    if ($enunciado === '') $err[] = 'Enunciado is required';
    $alternativas = [];
    if (!is_array($alts)) $alts = [];
    foreach ($alts as $a) { $a = trim($a); if ($a !== '') $alternativas[] = $a; }
    if (count($alternativas) < 2) $err[] = 'At least two alternatives required';
    if ($gabarito < 0 || $gabarito >= count($alternativas)) $err[] = 'Gabarito index out of range';
    $out = [
        'materia' => $materia,
        'numero' => isset($data['numero']) ? intval($data['numero']) : null,
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
    $q['id'] = uniqid('', true);
    $bank = load_bank($dataFile);
    if (!isset($bank[$q['materia']]) || !is_array($bank[$q['materia']])) $bank[$q['materia']] = [];
    $bank[$q['materia']][] = $q;
    if (!save_bank($dataFile, $bank)) { http_response_code(500); echo 'Failed to write data file.'; exit; }
    header('Location: ../views/admin.html?saved=1'); exit;

} elseif ($action === 'delete') {
    $id = $_POST['id'] ?? '';
    if (!$id) { http_response_code(400); echo 'Missing id'; exit; }
    $bank = load_bank($dataFile);
    $found = false;
    foreach ($bank as $mat => $list) {
        foreach ($list as $i => $item) {
            if (isset($item['id']) && $item['id'] === $id) {
                array_splice($bank[$mat], $i, 1);
                $found = true; break 2;
            }
        }
    }
    if (!$found) { http_response_code(404); echo 'Not found'; exit; }
    if (!save_bank($dataFile, $bank)) { http_response_code(500); echo 'Failed to write'; exit; }
    header('Location: ../views/admin.html?deleted=1'); exit;

} elseif ($action === 'edit') {
    $id = $_POST['id'] ?? '';
    if (!$id) { http_response_code(400); echo 'Missing id'; exit; }
    $ok = validate_question_input($_POST, $q, $errs);
    if (!$ok) { http_response_code(400); echo implode('; ', $errs); exit; }
    $bank = load_bank($dataFile);
    $found = false;
    foreach ($bank as $mat => $list) {
        foreach ($list as $i => $item) {
            if (isset($item['id']) && $item['id'] === $id) {
                if ($mat !== $q['materia']) {
                    array_splice($bank[$mat], $i, 1);
                    if (!isset($bank[$q['materia']]) || !is_array($bank[$q['materia']])) $bank[$q['materia']] = [];
                    $q['id'] = $id;
                    $bank[$q['materia']][] = $q;
                } else {
                    $q['id'] = $id;
                    $bank[$mat][$i] = $q;
                }
                $found = true; break 2;
            }
        }
    }
    if (!$found) { http_response_code(404); echo 'Not found'; exit; }
    if (!save_bank($dataFile, $bank)) { http_response_code(500); echo 'Failed to write'; exit; }
    header('Location: ../views/admin.html?edited=1'); exit;

} else {
    http_response_code(400);
    echo 'Invalid action';
    exit;
}

