<?php
session_start();

$erro = $sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($nome && $email && $senha) {
        $file = __DIR__ . '/../usuarios.json';
        $usuarios = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

        foreach ($usuarios as $u) {
            if ($u['email'] === $email) {
                $erro = "Email já cadastrado!";
                break;
            }
        }

        if (!$erro) {
            $usuarios[] = [
                'nome' => $nome,
                'email' => $email,
                'senha' => password_hash($senha, PASSWORD_DEFAULT),
                'nivel' => 'aluno'
            ];
            file_put_contents($file, json_encode($usuarios, JSON_PRETTY_PRINT));
            $sucesso = "Cadastro realizado! Faça login.";
        }
    } else {
        $erro = "Preencha todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Cadastro</title>
  <link rel="stylesheet" href="../style.css" />
  <style>
    body { background: #0f0f1e; color: #fff; }
    .container { max-width: 400px; margin: 100px auto; padding: 30px; background: #1a1a2e; border-radius: 16px; text-align: center; }
    input, button { width: 100%; padding: 14px; margin: 10px 0; border-radius: 10px; }
    input { background: #16213e; color: #fff; border: none; }
    button { background: #e94560; color: white; font-weight: bold; border: none; cursor: pointer; }
    .erro { color: #ff6b6b; }
    .sucesso { color: #00ff00; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Cadastre-se</h2>
    <?php if ($erro): ?><p class="erro"><?= $erro ?></p><?php endif; ?>
    <?php if ($sucesso): ?><p class="sucesso"><?= $sucesso ?></p><?php endif; ?>

    <form method="POST">
      <input type="text" name="nome" placeholder="Nome completo" required />
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="senha" placeholder="Senha" required />
      <button type="submit">Cadastrar</button>
    </form>
    <p><a href="../login.php">← Já tem conta? Faça login</a></p>
  </div>
</body>
</html>