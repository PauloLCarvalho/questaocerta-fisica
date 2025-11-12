<?php
session_start();
$_SESSION['teste'] = 'funciona';
echo 'Sessão: ' . ($_SESSION['teste'] ?? 'não funciona');
?>