<?php
require_once __DIR__ . '/../includes/auth.php';

$erro = $sucesso = '';
$action = $_POST['action'] ?? '';

switch ($action) {
  case 'cadastrar':
    qc_require_csrf();
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
    qc_require_csrf();
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
            // log successful login
            $log = sprintf("%s LOGIN SUCCESS: %s ip=%s nivel=%s\n", date('c'), $u['email'], $_SERVER['REMOTE_ADDR'] ?? '-', $u['nivel']);
            @file_put_contents(__DIR__ . '/../logs/auth.log', $log, FILE_APPEND | LOCK_EX);
            header("Location: ../views/dashboard.php");
            exit;
          }
        }
      }
      // log failed login attempt
      $log = sprintf("%s LOGIN FAIL: %s ip=%s\n", date('c'), $email, $_SERVER['REMOTE_ADDR'] ?? '-');
      @file_put_contents(__DIR__ . '/../logs/auth.log', $log, FILE_APPEND | LOCK_EX);
      $erro = "Email ou senha incorretos.";
    } else {
      $erro = "Preencha todos os campos.";
    }
    echo $erro;
    break;

  case 'logout':
    // enforce POST + CSRF for logout
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../views/index.html'); exit; }
    qc_require_csrf();
    // log logout
    $log = sprintf("%s LOGOUT: %s ip=%s\n", date('c'), $_SESSION['email'] ?? '-', $_SERVER['REMOTE_ADDR'] ?? '-');
    @file_put_contents(__DIR__ . '/../logs/auth.log', $log, FILE_APPEND | LOCK_EX);
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