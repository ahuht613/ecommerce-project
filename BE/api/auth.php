<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Nếu là preflight request (OPTIONS), trả về 200 OK và dừng lại
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'config.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$request = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'POST':
        if (isset($request['action']) && $request['action'] == 'login') {
            login($db, $request);
        } elseif (isset($request['action']) && $request['action'] == 'register') {
            register($db, $request);
        }
        break;
}

function login($db, $request) {
    $query = "SELECT id, username, email, full_name, role, password FROM users WHERE username = ? OR email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$request['username'], $request['username']]);
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($request['password'], $row['password'])) {
            $token = base64_encode(json_encode(array(
                'user_id' => $row['id'],
                'username' => $row['username'],
                'role' => $row['role'],
                'exp' => time() + 3600
            )));
            
            echo json_encode(array(
                "message" => "Login successful",
                "token" => $token,
                "user" => array(
                    "id" => $row['id'],
                    "username" => $row['username'],
                    "email" => $row['email'],
                    "full_name" => $row['full_name'],
                    "role" => $row['role']
                )
            ));
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Invalid credentials"));
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "User not found"));
    }
}

function register($db, $request) {
    $query = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$request['username'], $request['email']]);
    
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(array("message" => "User already exists"));
        return;
    }
    
    $query = "INSERT INTO users (username, email, password, full_name, phone, address) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    $hashed_password = password_hash($request['password'], PASSWORD_DEFAULT);
    

    if ($stmt->execute([
        $request['username'],
        $request['email'],
        $hashed_password,
        $request['full_name'],
        $request['phone'] ?? '',
        $request['address'] ?? ''
    ])) {
        echo json_encode(array("message" => "User registered successfully"));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Registration failed"));
    }
}
?>
