<?php
// index.php - Punto de entrada principal para la API

header('Content-Type: application/json'); // Respuestas en JSON
require 'db.php'; // Incluye conexión y funciones

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true); // Para POST JSON de la app

// Determinar acción (puedes usar $_GET['action'] o un campo en $input)
$action = $_GET['action'] ?? $input['action'] ?? '';

switch ($action) {
    case 'login':
        // Login: Espera email y password en $input
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        $result = login($email, $password);
        echo json_encode($result);
        break;
    
    case 'register':
        // Registro: Espera datos en $input (hashea password)
        $data = $input;
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $result = insert('users', $data);
        echo json_encode(is_numeric($result) ? ['success' => true, 'id' => $result] : $result);
        break;
    
    case 'select':
        // Buscar: Verifica sesión si es necesario
        if (!checkSession()) {
            echo json_encode(['error' => 'No autorizado']);
            break;
        }
        $table = $input['table'] ?? 'users';
        $columns = $input['columns'] ?? '*';
        $where = $input['where'] ?? '';
        $result = select($table, $columns, $where);
        echo json_encode(is_array($result) ? ['success' => true, 'data' => $result] : $result);
        break;
    
    case 'update':
        // Actualizar: Verifica sesión
        if (!checkSession()) {
            echo json_encode(['error' => 'No autorizado']);
            break;
        }
        $table = $input['table'] ?? 'users';
        $data = $input['data'] ?? [];
        $where = $input['where'] ?? '';
        $result = update($table, $data, $where);
        echo json_encode(is_numeric($result) ? ['success' => true, 'affected' => $result] : $result);
        break;
    
    case 'delete':
        // Eliminar: Verifica sesión
        if (!checkSession()) {
            echo json_encode(['error' => 'No autorizado']);
            break;
        }
        $table = $input['table'] ?? 'users';
        $where = $input['where'] ?? '';
        $result = delete($table, $where);
        echo json_encode(is_numeric($result) ? ['success' => true, 'affected' => $result] : $result);
        break;
    
    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}

// Ejemplo de request desde app Android:
// POST a http://localhost/index.php?action=login
// Body JSON: {"email": "juan@gmal.com", "password": "password123"}
?>