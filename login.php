<?php
session_start();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email && $senha) {
        // Caminho correto: usuarios.json está na raiz do projeto
        $file = __DIR__ . '/usuarios.json';
        if (file_exists($file)) {
            $usuarios = json_decode(file_get_contents($file), true);
            foreach ($usuarios as $u) {
                if ($u['email'] === $email && password_verify($senha, $u['senha'])) {
                    $_SESSION['email'] = $u['email'];
                    $_SESSION['nome']  = $u['nome'];
                    $_SESSION['nivel'] = $u['nivel'];
                    header("Location: dashboard.html");
                    exit;
                }
            }
        }
        $erro = "Email ou senha incorretos.";
    } else {
        $erro = "Preencha todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - Questão Certa</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .login-box {
      max-width: 400px;
      margin: 4rem auto;
      background: white;
      padding: 2.5rem;
      border-radius: 12px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
      text-align: center;
    }
    .login-box h2 { color: var(--azul); margin-bottom: 1.5rem; }
    input {
      width: 100%; padding: 12px; margin: 10px 0;
      border: 1px solid #ddd; border-radius: 8px; font-size: 1rem;
    }
    .btn-login {
      width: 100%; margin-top: 1rem;
      background: var(--laranja); color: white; font-weight: 600;
    }
    .erro { color: var(--rosa); font-size: 0.9rem; margin: 10px 0; }
    .cadastro-link { margin-top: 1.5rem; font-size: 0.9rem; }
    .cadastro-link a { color: var(--azul); text-decoration: none; }
    .cadastro-link a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <div class="container">
    <div class="login-box">
      <h2>Login</h2>
      <?php if ($erro): ?>
        <p class="erro"><?= htmlspecialchars($erro) ?></p>
      <?php endif; ?>

      <form method="POST">
        <input type="email" name="email" placeholder="Seu e-mail" required />
        <input type="password" name="senha" placeholder="Sua senha" required />
        <button type="submit" class="btn btn-login">Entrar</button>
      </form>

      <p class="cadastro-link">
        Não tem conta? <a href="cadastro.html">Cadastre-se</a>
      </p>
    </div>
  </div>
</body>
</html>