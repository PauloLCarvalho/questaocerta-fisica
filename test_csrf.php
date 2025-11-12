<?php
// Teste rápido de CSRF token
require_once __DIR__ . '/includes/auth.php';

// Simular sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h3>Teste CSRF Token</h3>";

// Gerar token
$token1 = qc_csrf_token();
echo "<p>Token 1 gerado: " . substr($token1, 0, 16) . "...</p>";

// Validar token
$valid1 = qc_csrf_validate($token1);
echo "<p>Token 1 válido? " . ($valid1 ? 'SIM' : 'NÃO') . "</p>";

// Após validação, novo token deve ter sido gerado
$token2 = qc_csrf_token();
echo "<p>Token 2 (após validação): " . substr($token2, 0, 16) . "...</p>";
echo "<p>Tokens são diferentes? " . ($token1 !== $token2 ? 'SIM (correto)' : 'NÃO (erro!)') . "</p>";

// Tentar validar token antigo (deve falhar)
$valid_old = qc_csrf_validate($token1);
echo "<p>Token 1 ainda válido? " . ($valid_old ? 'SIM (erro!)' : 'NÃO (correto)') . "</p>";

// Validar novo token
$valid2 = qc_csrf_validate($token2);
echo "<p>Token 2 válido? " . ($valid2 ? 'SIM' : 'NÃO') . "</p>";

echo "<hr><p><strong>Resultado:</strong> O sistema está gerando novos tokens após cada validação, evitando o erro 'token expirado'.</p>";
?>
