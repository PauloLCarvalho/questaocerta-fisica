<?php
// Teste manual de adição de questão
$dataFile = __DIR__ . '/data/questoes.json';

// Ler arquivo
$raw = file_get_contents($dataFile);
echo "<h3>Teste de Adição de Questão</h3>";
echo "<p>Arquivo lido: " . strlen($raw) . " bytes</p>";

$bank = json_decode($raw, true);
echo "<p>Questões existentes: " . count($bank) . "</p>";

// Nova questão de teste
$novaQuestao = [
    'id' => 'humanas-historia-1',
    'grande_area' => 'humanas',
    'componente' => 'historia',
    'nivel' => 'medio',
    'numero' => 1,
    'ano' => 2025,
    'enunciado' => 'Teste de questão adicionada manualmente',
    'alternativas' => ['A) Opção 1', 'B) Opção 2', 'C) Opção 3', 'D) Opção 4'],
    'gabarito' => 0,
    'dica' => 'Dica teste'
];

$bank[] = $novaQuestao;
echo "<p>Questões após adicionar: " . count($bank) . "</p>";

// Salvar
$json = json_encode($bank, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$result = file_put_contents($dataFile, $json);

if ($result !== false) {
    echo "<p style='color:green;'>✅ Questão adicionada com sucesso! ($result bytes escritos)</p>";
    echo "<p>ID da questão: <strong>humanas-historia-1</strong></p>";
    echo "<p><a href='views/admin.php'>Ver no Admin</a></p>";
} else {
    echo "<p style='color:red;'>❌ Erro ao salvar arquivo</p>";
}
?>
