<?php
// Remove duplicatas do arquivo de questões
$dataFile = __DIR__ . '/data/questoes.json';

echo "<h3>Remover Questões Duplicadas</h3>";

// Ler arquivo
$raw = file_get_contents($dataFile);
$bank = json_decode($raw, true);

echo "<p>Questões antes: <strong>" . count($bank) . "</strong></p>";

// Remover duplicatas mantendo apenas a primeira ocorrência de cada ID
$ids_vistos = [];
$bank_limpo = [];

foreach ($bank as $questao) {
    $id = $questao['id'] ?? '';
    if ($id && !in_array($id, $ids_vistos)) {
        $ids_vistos[] = $id;
        $bank_limpo[] = $questao;
    } else if ($id) {
        echo "<p style='color:orange;'>⚠️ Removendo duplicata: $id</p>";
    }
}

echo "<p>Questões depois: <strong>" . count($bank_limpo) . "</strong></p>";
echo "<p>Duplicatas removidas: <strong>" . (count($bank) - count($bank_limpo)) . "</strong></p>";

// Salvar arquivo limpo
$json = json_encode($bank_limpo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$result = file_put_contents($dataFile, $json);

if ($result !== false) {
    echo "<p style='color:green;'>✅ Arquivo atualizado com sucesso!</p>";
    echo "<p><a href='views/admin.php'>Ver no Admin</a> | <a href='views/dashboard.php'>Ir para Dashboard</a></p>";
} else {
    echo "<p style='color:red;'>❌ Erro ao salvar arquivo</p>";
}
?>
