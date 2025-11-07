<?php
session_start();

$erro = $sucesso = '';
$action = $_POST['action'] ?? '';

switch ($action) {
  case 'cadastrar':
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($nome && $email && $senha) {
      $file = __DIR__ . '/../data/users.json';
      $users = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

      foreach ($users as $u) {
        if ($u['email'] === $email) {
          $erro = "Email já cadastrado!";
          break;
        }
      }

      if (!$erro) {
        $users[] = [
          'nome' => $nome,
          'email' => $email,
          'senha' => password_hash($senha, PASSWORD_DEFAULT),
          'nivel' => 'aluno'
        ];
        file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
        $sucesso = "Cadastro realizado! Faça login.";
        header('Location: ../views/index.html');
        exit;
      }
    } else {
      $erro = "Preencha todos os campos.";
    }
    echo $erro ? $erro : $sucesso;
    break;

  case 'login':
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email && $senha) {
      $file = __DIR__ . '/../data/users.json';
      if (file_exists($file)) {
        $users = json_decode(file_get_contents($file), true);
        foreach ($users as $u) {
          if ($u['email'] === $email && password_verify($senha, $u['senha'])) {
            // regenerate session id to prevent session fixation
            session_regenerate_id(true);
            $_SESSION['email'] = $u['email'];
            $_SESSION['nome'] = $u['nome'];
            $_SESSION['nivel'] = $u['nivel'];
            header("Location: ../views/dashboard.html");
            exit;
          }
        }
      }
      $erro = "Email ou senha incorretos.";
    } else {
      $erro = "Preencha todos os campos.";
    }
    echo $erro;
    break;

  case 'logout':
    // clear session and cookie
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
      );
    }
    session_unset();
    session_destroy();
    header("Location: ../views/index.html");
    exit;
    break;

  default:
    header('Location: ../views/index.html');
    exit;
}
?>