<?php
session_start();

$erro = '';

// DEBUG: Mostra erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $file = 'auth/usuarios.json';

    if (!file_exists($file)) {
        $erro = "ERRO: Arquivo usuarios.json não encontrado!";
    } elseif (!is_readable($file)) {
        $erro = "ERRO: Sem permissão para ler usuarios.json!";
    } else {
        $conteudo = file_get_contents($file);
        $usuarios = json_decode($conteudo, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $erro = "ERRO JSON: " . json_last_error_msg();
        } elseif (!is_array($usuarios)) {
            $erro = "ERRO: usuarios.json inválido!";
        } else {
            $encontrado = false;
            foreach ($usuarios as $u) {
                if (
                    isset($u['email'], $u['senha']) &&
                    $u['email'] === $email &&
                    password_verify($senha, $u['senha'])
                ) {
                    $_SESSION['email'] = $u['email'];
                    $_SESSION['nome'] = $u['nome'] ?? 'Usuário';
                    $_SESSION['nivel'] = $u['nivel'] ?? 'aluno';
                    header("Location: dashboard.html");
                    exit;
                }
            }
            $erro = "Email ou senha incorretos!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - Questão Certa</title>
  <style>
    body { background: #0f0f1e; color: #fff; font-family: Arial; margin: 0; }
    .container { max-width: 400px; margin: 100px auto; padding: 30px; background: #1a1a2e; border-radius: 16px; text-align: center; }
    input, button { width: 100%; padding: 14px; margin: 10px 0; border-radius: 10px; }
    input { background: #16213e; color: #fff; border: none; }
    button { background: #e94560; color: white; font-weight: bold; border: none; cursor: pointer; }
    .erro { color: #ff6b6b; background: #330000; padding: 10px; border-radius: 8px; margin: 10px 0; }
    .debug { color: #00ff00; background: #002200; padding: 10px; border-radius: 8px; margin: 10px 0; font-family: monospace; font-size: 12px; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Entrar</h2>

    <!-- DEBUG -->
    <?php if ($erro): ?>
      <div class="erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <?php if (isset($conteudo)): ?>
      <div class="debug">
        <strong>Conteúdo do usuarios.json:</strong><br>
        <?= nl2br(htmlspecialchars($conteudo)) ?>
      </div>
    <?php endif; ?>
    <!-- FIM DEBUG -->

    <form method="POST">
      <input type="email" name="email" placeholder="Email" value="admin@questaocerta.com" required />
      <input type="password" name="senha" placeholder="Senha" value="admin123" required />
      <button type="submit">Entrar como Admin</button>
    </form>

    <p><a href="index.html" style="color:#e94560;">← Voltar à home</a></p>
  </div>
</body>
</html>