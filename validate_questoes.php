<?php
// Validador de data/questoes.json
header('Content-Type: text/html; charset=utf-8');

$path = __DIR__ . '/data/questoes.json';
if (!file_exists($path)) { die('<p style="color:red">Arquivo não encontrado: data/questoes.json</p>'); }
$raw = file_get_contents($path);
$data = json_decode($raw, true);
if (!is_array($data)) {
  die('<p style="color:red">JSON inválido ou não é um array.</p>');
}

$areas = ['humanas','matematica','natureza','linguagens'];
$componentesMapa = [
  'humanas' => ['historia','geografia','filosofia','sociologia'],
  'matematica' => ['matematica'],
  'natureza' => ['fisica','quimica','biologia'],
  'linguagens' => ['portugues','literatura','ingles','espanhol','artes']
];
$niveis = ['facil','medio','dificil','impossivel'];

function h($s){return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');}

$erros = [];
$contagens = [
  'total' => count($data),
  'por_area' => [],
  'por_comp' => [],
  'por_nivel' => []
];

$ids = [];
foreach ($data as $idx => $q) {
  $linha = $idx+1;
  $qid = $q['id'] ?? '';
  if (!$qid) { $erros[] = "[$linha] Sem id"; }
  elseif (isset($ids[$qid])) { $erros[] = "[$linha] ID duplicado: $qid (primeiro em {$ids[$qid]})"; }
  else { $ids[$qid] = $linha; }

  $ga = $q['grande_area'] ?? ($q['materia'] ?? '');
  $comp = $q['componente'] ?? '';
  $nivel = $q['nivel'] ?? '';
  $alts = $q['alternativas'] ?? [];
  $gab = $q['gabarito'] ?? null;

  if (!$ga) { $erros[] = "[$linha] grande_area/materia ausente (id=$qid)"; }
  elseif (!in_array($ga, $areas, true)) { $erros[] = "[$linha] grande_area inválida '$ga' (id=$qid)"; }

  if (!$comp) { $erros[] = "[$linha] componente ausente (id=$qid)"; }
  elseif ($ga && isset($componentesMapa[$ga]) && !in_array($comp, $componentesMapa[$ga], true)) {
    $erros[] = "[$linha] componente '$comp' não pertence à área '$ga' (id=$qid)";
  }

  if (!$nivel) { $erros[] = "[$linha] nivel ausente (id=$qid)"; }
  elseif (!in_array($nivel, $niveis, true)) { $erros[] = "[$linha] nivel inválido '$nivel' (id=$qid)"; }

  if (!is_array($alts) || count($alts) < 2) { $erros[] = "[$linha] alternativas insuficientes (<2) (id=$qid)"; }
  if (!is_int($gab) || $gab < 0 || (is_array($alts) && $gab >= count($alts))) {
    $erros[] = "[$linha] gabarito fora do intervalo (id=$qid)";
  }

  // Contagens
  $contagens['por_area'][$ga] = ($contagens['por_area'][$ga] ?? 0) + 1;
  $chComp = $ga . ':' . $comp;
  $contagens['por_comp'][$chComp] = ($contagens['por_comp'][$chComp] ?? 0) + 1;
  $contagens['por_nivel'][$nivel] = ($contagens['por_nivel'][$nivel] ?? 0) + 1;
}
?>
<!doctype html>
<html lang="pt-BR">
<meta charset="utf-8">
<title>Validador de Questões</title>
<body style="font-family:Segoe UI, Tahoma, sans-serif; padding:20px;">
<h1>Validação de data/questoes.json</h1>
<p>Total de questões: <strong><?= (int)$contagens['total'] ?></strong></p>

<h2>Contagens</h2>
<ul>
  <?php foreach ($contagens['por_area'] as $k=>$v): ?>
    <li>Área <?= h($k) ?>: <?= (int)$v ?></li>
  <?php endforeach; ?>
</ul>

<h2>Erros</h2>
<?php if (!$erros): ?>
  <p style="color:green">Nenhum erro encontrado. ✔</p>
<?php else: ?>
  <ul>
    <?php foreach ($erros as $e): ?>
      <li style="color:#b91c1c;"><?= h($e) ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<p><a href="views/admin.php">Voltar ao Admin</a></p>
</body>
</html>
