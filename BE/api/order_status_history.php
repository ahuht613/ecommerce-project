<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once 'config.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        getOrderStatusHistory($db);
        break;
    case 'POST':
        addOrderStatusHistory($db);
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
}

function getOrderStatusHistory($db) {
    $order_id = $_GET['order_id'] ?? null;
    
    if (empty($order_id)) {
        http_response_code(400);
        echo json_encode(array("message" => "Order ID is required"));
        return;
    }
    
    try {
        $query = "SELECT 
                    id,
                    order_id,
                    status,
                    notes,
                    created_at
                  FROM order_status_history
                  WHERE order_id = ?
                  ORDER BY created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$order_id]);
        
        $history = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $history[] = $row;
        }
        
        echo json_encode($history);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Database error: " . $e->getMessage()));
    }
}

function addOrderStatusHistory($db) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['order_id']) || empty($data['status'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Order ID and status are required"));
        return;
    }
    
    try {
        $query = "INSERT INTO order_status_history (order_id, status, notes) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);

        if ($stmt->execute([$data['order_id'], $data['status'], $data['notes'] ?? ''])) {
            echo json_encode(array("message" => "Status history added successfully"));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Failed to add status history"));
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Database error: " . $e->getMessage()));
    }
}
?>
