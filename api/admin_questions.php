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
    require_once __DIR__ . '/../includes/auth.php';
    qc_require_admin();

    // Admin API: supports add, edit, delete
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo 'Method not allowed';
        exit;
    }

    $action = $_POST['action'] ?? '';
    $dataFile = __DIR__ . '/../data/questoes.json';

    // helper: load bank with shared lock
    function load_bank($file) {
        $raw = '';
        $fp = fopen($file, 'c+');
        if (!$fp) return [];
        // acquire shared lock for reading
        if (flock($fp, LOCK_SH)) {
            clearstatcache(true, $file);
            $raw = stream_get_contents($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
        $bank = json_decode($raw, true);
        if (!is_array($bank)) $bank = [];
        return $bank;
    }

    // helper: save bank with exclusive lock
    function save_bank($file, $bank) {
        $fp = fopen($file, 'c+');
        if (!$fp) return false;
        if (!flock($fp, LOCK_EX)) { fclose($fp); return false; }
        // truncate and write
        ftruncate($fp, 0);
        rewind($fp);
        $json = json_encode($bank, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $written = fwrite($fp, $json);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return $written !== false;
    }

    // Basic validation common
    function validate_question_input($data, &$out, &$err) {
        $err = [];
        $materia = trim($data['materia'] ?? '');
        $numero = intval($data['numero'] ?? 0);
        $ano = intval($data['ano'] ?? 0);
        $enunciado = trim($data['enunciado'] ?? '');
        $alts = $data['alt'] ?? [];
        $dica = trim($data['dica'] ?? '');
        $gabarito = isset($data['gabarito']) ? intval($data['gabarito']) : 0;

        if ($materia === '') $err[] = 'Materia is required';
        if ($enunciado === '') $err[] = 'Enunciado is required';
        // sanitize alternatives
        $alternativas = [];
        if (!is_array($alts)) $alts = [];
        foreach ($alts as $a) { $a = trim($a); if ($a !== '') $alternativas[] = $a; }
        if (count($alternativas) < 2) $err[] = 'At least two alternatives required';
        if ($gabarito < 0 || $gabarito >= count($alternativas)) $err[] = 'Gabarito index out of range';

        $out = [
            'materia' => $materia,
            'numero' => $numero ?: null,
            'ano' => $ano ?: null,
            'enunciado' => $enunciado,
            'alternativas' => $alternativas,
            'gabarito' => $gabarito,
            'dica' => $dica
        ];
        return count($err) === 0;
    }

    // ensure data file exists
    if (!file_exists($dataFile)) {
        // create empty structure
        file_put_contents($dataFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    if ($action === 'add') {
        $ok = validate_question_input($_POST, $q, $errs);
        if (!$ok) {
            http_response_code(400);
            echo implode('; ', $errs);
            exit;
        }
        $q['id'] = uniqid('', true);

        $bank = load_bank($dataFile);
        if (!isset($bank[$q['materia']]) || !is_array($bank[$q['materia']])) $bank[$q['materia']] = [];
        $bank[$q['materia']][] = $q;
        if (!save_bank($dataFile, $bank)) {
            http_response_code(500);
            echo 'Failed to write data file.';
            exit;
        }
        header('Location: ../views/admin.html?saved=1');
        exit;

    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if (!$id) { http_response_code(400); echo 'Missing id'; exit; }
        $bank = load_bank($dataFile);
        $found = false;
        foreach ($bank as $mat => $list) {
            foreach ($list as $i => $item) {
                if (isset($item['id']) && $item['id'] === $id) {
                    array_splice($bank[$mat], $i, 1);
                    $found = true;
                    break 2;
                }
            }
        }
        if (!$found) { http_response_code(404); echo 'Not found'; exit; }
        if (!save_bank($dataFile, $bank)) { http_response_code(500); echo 'Failed to write'; exit; }
        header('Location: ../views/admin.html?deleted=1');
        exit;

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
                    // if materia changed, remove from old and push into new materia array
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
        header('Location: ../views/admin.html?edited=1');
        exit;

    } else {
        http_response_code(400);
        echo 'Invalid action';
        exit;
    }
