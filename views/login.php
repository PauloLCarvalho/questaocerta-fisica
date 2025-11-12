<?php
require_once __DIR__ . '/../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entrar - Questão Certa</title>
  <link rel="stylesheet" href="../assets/style.css">
  <style>
    body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:#f6f8fb; margin:0; font-family: 'Segoe UI', Tahoma, sans-serif; }
    .login-card { width: 420px; background:#fff; border-radius:12px; box-shadow:0 8px 30px rgba(2,6,23,0.08); padding:28px; }
    .login-card h1 { font-size:22px; margin:0 0 8px; text-align:center; color:#111827; }
    .login-card p.lead { font-size:14px; color:#6b7280; text-align:center; margin:0 0 18px; }
    .form-group { margin-bottom:12px; }
    .form-group input { width:100%; padding:12px 14px; border-radius:8px; border:1px solid #e5e7eb; font-size:14px; }
    .btn-primary { width:100%; background:#2c6bf3; color:#fff; border:none; padding:12px; border-radius:8px; font-weight:600; cursor:pointer; }
    .links { margin-top:14px; display:flex; justify-content:space-between; font-size:13px; }
  </style>
</head>
<body>
  <div class="login-card" role="main">
    <h1>Sign in to Questão Certa</h1>
    <p class="lead">Acesse sua conta para continuar estudando</p>

    <form action="../api/auth.php" method="POST">
      <input type="hidden" name="action" value="login">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(qc_csrf_token()) ?>">
      <div class="form-group">
        <input name="email" type="email" placeholder="Username or email address" required>
      </div>
      <div class="form-group">
        <input name="senha" type="password" placeholder="Password" required>
      </div>
      <div class="links">
        <a href="#" style="color:#2563eb; text-decoration:none;">Forgot password?</a>
        <button type="submit" class="btn-primary">Sign in</button>
      </div>
    </form>

    <div class="sep">or</div>

    <button class="social-btn social-google"> <img src="../assets/google.svg" alt="Google" style="width:18px;height:18px"> Continue with Google</button>
    <div style="height:8px"></div>
    <button class="social-btn social-apple"> <img src="../assets/apple.svg" alt="Apple" style="width:18px;height:18px"> Continue with Apple</button>

    <p class="muted">New to Questão Certa? <a href="register.php" style="color:#2563eb; text-decoration:none;">Create an account</a></p>
  </div>
</body>
</html>
