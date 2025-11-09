<?php
// db.php - Conexión y funciones de base de datos

$host = 'localhost';
$dbname = 'checkandgo';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Error de conexión a la base de datos']));
}

// Función insert (corregida para arrays asociativos, más segura)
function insert($table, $data) {
    global $pdo;
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        return ['error' => 'Error al insertar: ' . $e->getMessage()];
    }
}

// Función update
function update($table, $data, $where) {
    global $pdo;
    $set = implode(' = ?, ', array_keys($data)) . ' = ?';
    $sql = "UPDATE $table SET $set WHERE $where";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));
        return $stmt->rowCount();
    } catch (PDOException $e) {
        return ['error' => 'Error al actualizar: ' . $e->getMessage()];
    }
}

// Función delete
function delete($table, $where) {
    global $pdo;
    $sql = "DELETE FROM $table WHERE $where";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    } catch (PDOException $e) {
        return ['error' => 'Error al eliminar: ' . $e->getMessage()];
    }
}

// Función select
function select($table, $columns = '*', $where = '') {
    global $pdo;
    $sql = "SELECT $columns FROM $table";
    if (!empty($where)) $sql .= " WHERE $where";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ['error' => 'Error al buscar: ' . $e->getMessage()];
    }
}

// Función login (verificación de usuario)
function login($email, $password) {
    global $pdo;
    $sql = "SELECT id, name, password FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        return ['success' => true, 'user' => ['id' => $user['id'], 'name' => $user['name']]];
    }
    return ['error' => 'Credenciales incorrectas'];
}

// Función para verificar sesión (útil para endpoints protegidos)
function checkSession() {
    session_start();
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : false;
}
?>