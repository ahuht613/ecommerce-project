<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Nếu là preflight request (OPTIONS), trả về 200 OK và dừng lại
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once 'config.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$request = json_decode(file_get_contents('php://input'), true);

$token = authenticate();
$user_data = json_decode(base64_decode($token), true);

switch($method) {
    case 'GET':
        if ($user_data['role'] == 'admin') {
            getAllOrders($db);
        } else {
            getUserOrders($db, $user_data['user_id']);
        }
        break;
    case 'POST':
        createOrder($db, $request, $user_data['user_id']);
        break;
    case 'PUT':
        if ($user_data['role'] == 'admin') {
            updateOrderStatus($db, $request);
        }
        break;
}

function getAllOrders($db) {
    $query = "SELECT o.*, u.full_name, u.email,
                     (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as items_count
              FROM orders o
              JOIN users u ON o.user_id = u.id
              ORDER BY o.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $orders = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $orders[] = $row;
    }

    echo json_encode($orders);
}

function getUserOrders($db, $user_id) {
    $query = "SELECT o.*,
                     (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as items_count
              FROM orders o
              WHERE o.user_id = ?
              ORDER BY o.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);

    $orders = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $orders[] = $row;
    }

    echo json_encode($orders);
}

function createOrder($db, $request, $user_id) {
    try {
        // Validate required fields
        if (empty($request['phone']) || empty($request['shipping_address']) || empty($request['total_amount']) || empty($request['items'])) {
            http_response_code(400);
            echo json_encode(array("message" => "Missing required fields: phone, shipping_address, total_amount, items"));
            return;
        }

        $db->beginTransaction();

        // Create order
        $query = "INSERT INTO orders (user_id, full_name, phone, shipping_address, payment_method, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);

        // Prepare variables for binding
        $full_name = $request['full_name'] ?? 'Customer';
        $payment_method = $request['payment_method'] ?? 'cod';
        $status = 'pending';

        $success = $stmt->execute([
            $user_id,
            $full_name,
            $request['phone'],
            $request['shipping_address'],
            $payment_method,
            $request['total_amount'],
            $status
        ]);

        if (!$success) {
            throw new Exception("Failed to create order");
        }

        $order_id = $db->lastInsertId();
        
        // Add order items
        foreach ($request['items'] as $item) {
            // Validate item fields
            if (empty($item['product_id']) || empty($item['quantity']) || empty($item['price'])) {
                throw new Exception("Invalid item data: missing product_id, quantity, or price");
            }

            $query = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);

            // Prepare variables for binding
            $product_name = $item['product_name'] ?? $item['name'] ?? 'Unknown Product';

            $success = $stmt->execute([
                $order_id,
                $item['product_id'],
                $product_name,
                $item['quantity'],
                $item['price']
            ]);

            if (!$success) {
                throw new Exception("Failed to add order item");
            }

            // Update product stock
            $query = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?";
            $stmt = $db->prepare($query);

            $success = $stmt->execute([
                $item['quantity'],
                $item['product_id'],
                $item['quantity']
            ]);

            if (!$success || $stmt->rowCount() == 0) {
                throw new Exception("Insufficient stock for product: " . $product_name);
            }
        }
        
        // Clear cart
        $query = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id]);

        $db->commit();

        echo json_encode(array(
            "message" => "Order created successfully",
            "order_id" => $order_id,
            "status" => "pending"
        ));
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(array("message" => "Failed to create order: " . $e->getMessage()));
    }
}

function updateOrderStatus($db, $request) {
    try {
        $db->beginTransaction();

        // Update order status
        $query = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$request['status'], $request['id']]);

        // Add status history
        $historyQuery = "INSERT INTO order_status_history (order_id, status, notes) VALUES (?, ?, ?)";
        $historyStmt = $db->prepare($historyQuery);
        $notes = $request['notes'] ?? 'Trạng thái được cập nhật bởi admin';
        $historyStmt->execute([$request['id'], $request['status'], $notes]);

        $db->commit();
        echo json_encode(array("message" => "Order status updated successfully"));
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(array("message" => "Failed to update order status: " . $e->getMessage()));
    }
}
?>
