<?php
// login.php - Endpoint específico para login (opcional, integra en index.php)
header('Content-Type: application/json');
require 'db.php';
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';
$result = login($email, $password);
echo json_encode($result);
?>