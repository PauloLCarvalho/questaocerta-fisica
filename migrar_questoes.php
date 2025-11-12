<?php
/**
 * Script de migração de questões
 * Adiciona os campos: grande_area, componente, nivel
 * Executa via browser: http://localhost/questaocerta/migrar_questoes.php
 */

$dataFile = __DIR__ . '/data/questoes.json';
$backupFile = __DIR__ . '/data/questoes_backup_' . date('Y-m-d_His') . '.json';

// Criar backup
if (file_exists($dataFile)) {
    copy($dataFile, $backupFile);
    echo "✓ Backup criado: " . basename($backupFile) . "<br><br>";
}

// Carregar questões
$json = file_get_contents($dataFile);
$questoes = json_decode($json, true);

if (!is_array($questoes)) {
    die("❌ Erro ao ler questoes.json");
}

echo "📊 Total de questões: " . count($questoes) . "<br><br>";

// Mapeamento de matéria antiga → grande_area + componente padrão
$mapeamento = [
    'matematica' => ['grande_area' => 'matematica', 'componente' => 'matematica'],
    'fisica' => ['grande_area' => 'natureza', 'componente' => 'fisica'],
    'natureza' => ['grande_area' => 'natureza', 'componente' => 'biologia'],
    'humanas' => ['grande_area' => 'humanas', 'componente' => 'historia'],
    'linguagens' => ['grande_area' => 'linguagens', 'componente' => 'portugues']
];

// Atribuir nível baseado no ano (quanto mais antigo, mais fácil - exemplo)
function inferir_nivel($ano) {
    if (!$ano) return 'medio';
    if ($ano <= 2020) return 'facil';
    if ($ano <= 2022) return 'medio';
    if ($ano <= 2023) return 'dificil';
    return 'impossivel';
}

$migradas = 0;
$erros = 0;

foreach ($questoes as &$q) {
    // Se já tem os campos, pula
    if (isset($q['grande_area']) && isset($q['componente']) && isset($q['nivel'])) {
        continue;
    }

    $materia = $q['materia'] ?? '';
    
    if (isset($mapeamento[$materia])) {
        $q['grande_area'] = $mapeamento[$materia]['grande_area'];
        $q['componente'] = $mapeamento[$materia]['componente'];
        $q['nivel'] = inferir_nivel($q['ano'] ?? null);
        $migradas++;
    } else {
        // Se matéria desconhecida, atribui valores padrão
        $q['grande_area'] = 'matematica';
        $q['componente'] = 'matematica';
        $q['nivel'] = 'medio';
        $erros++;
    }
}
unset($q); // quebrar referência

echo "✓ Questões migradas: <strong>$migradas</strong><br>";
if ($erros > 0) {
    echo "⚠ Questões com matéria desconhecida (atribuído padrão): <strong>$erros</strong><br>";
}

// Salvar JSON atualizado
$jsonAtualizado = json_encode($questoes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($dataFile, $jsonAtualizado);

echo "<br>✅ <strong>Migração concluída com sucesso!</strong><br><br>";
echo "Novos campos adicionados:<br>";
echo "- grande_area (humanas, matematica, natureza, linguagens)<br>";
echo "- componente (historia, matematica, fisica, etc.)<br>";
echo "- nivel (facil, medio, dificil, impossivel)<br><br>";

echo "<a href='views/admin.html' style='display:inline-block; background:#0e70fa; color:#fff; padding:10px 20px; border-radius:8px; text-decoration:none;'>Ir para Admin</a> ";
echo "<a href='views/dashboard.php' style='display:inline-block; background:#10a37f; color:#fff; padding:10px 20px; border-radius:8px; text-decoration:none; margin-left:10px;'>Ir para Dashboard</a>";

echo "<br><br><small>Backup salvo em: $backupFile</small>";
?>
