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
      }
    } else {
      $erro = "Preencha todos os campos.";
    }

    // Retorne JSON para JS/AJAX (escalável)
    echo json_encode(['success' => !!$sucesso, 'message' => $sucesso ?: $erro]);
    exit;
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
            $_SESSION['email'] = $u['email'];
            $_SESSION['nome'] = $u['nome'];
            $_SESSION['nivel'] = $u['nivel'];
            echo json_encode(['success' => true, 'redirect' => '../views/dashboard.html']);
            exit;
          }
        }
      }
      $erro = "Email ou senha incorretos.";
    } else {
      $erro = "Preencha todos os campos.";
    }

    echo json_encode(['success' => false, 'message' => $erro]);
    exit;
    break;

  case 'logout':
    session_unset();
    session_destroy();
    echo json_encode(['success' => true, 'redirect' => '../views/index.html']);
    exit;
    break;

  default:
    echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    exit;
}