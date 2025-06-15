<?php
header("Access-Control-Allow-Origin: http://localhost:5174");
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
$request = json_decode(file_get_contents('php://input'), true);

$token = authenticate();
$user_data = json_decode(base64_decode($token), true);
$user_id = $user_data['user_id'];

switch($method) {
    case 'GET':
        getCart($db, $user_id);
        break;
    case 'POST':
        addToCart($db, $request, $user_id);
        break;
    case 'PUT':
        updateCartItem($db, $request, $user_id);
        break;
    case 'DELETE':
        if (isset($_GET['product_id'])) {
            removeFromCart($db, $_GET['product_id'], $user_id);
        } else {
            clearCart($db, $user_id);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
}

function getCart($db, $user_id) {
    try {
        $query = "SELECT c.*, p.name, p.price, p.image_url, p.stock_quantity 
                  FROM cart c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.user_id = ? AND p.status = 'active'
                  ORDER BY c.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        
        $cart_items = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cart_items[] = $row;
        }
        
        echo json_encode($cart_items);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Database error: " . $e->getMessage()));
    }
}

function addToCart($db, $request, $user_id) {
    try {
        if (empty($request['product_id']) || empty($request['quantity'])) {
            http_response_code(400);
            echo json_encode(array("message" => "Product ID and quantity are required"));
            return;
        }
        
        // Check if product exists and is active
        $checkQuery = "SELECT id, stock_quantity FROM products WHERE id = ? AND status = 'active'";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(1, $request['product_id']);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() == 0) {
            http_response_code(404);
            echo json_encode(array("message" => "Product not found or inactive"));
            return;
        }
        
        $product = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if ($product['stock_quantity'] < $request['quantity']) {
            http_response_code(400);
            echo json_encode(array("message" => "Insufficient stock"));
            return;
        }
        
        // Check if item already in cart
        $existQuery = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
        $existStmt = $db->prepare($existQuery);
        $existStmt->bindParam(1, $user_id);
        $existStmt->bindParam(2, $request['product_id']);
        $existStmt->execute();
        
        if ($existStmt->rowCount() > 0) {
            // Update existing item
            $existing = $existStmt->fetch(PDO::FETCH_ASSOC);
            $newQuantity = $existing['quantity'] + $request['quantity'];
            
            if ($newQuantity > $product['stock_quantity']) {
                http_response_code(400);
                echo json_encode(array("message" => "Total quantity exceeds stock"));
                return;
            }
            
            $updateQuery = "UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(1, $newQuantity);
            $updateStmt->bindParam(2, $user_id);
            $updateStmt->bindParam(3, $request['product_id']);
            $updateStmt->execute();
        } else {
            // Add new item
            $insertQuery = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
            $insertStmt = $db->prepare($insertQuery);
            $insertStmt->bindParam(1, $user_id);
            $insertStmt->bindParam(2, $request['product_id']);
            $insertStmt->bindParam(3, $request['quantity']);
            $insertStmt->execute();
        }
        
        echo json_encode(array("message" => "Product added to cart successfully"));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Database error: " . $e->getMessage()));
    }
}

function updateCartItem($db, $request, $user_id) {
    try {
        if (empty($request['product_id']) || !isset($request['quantity'])) {
            http_response_code(400);
            echo json_encode(array("message" => "Product ID and quantity are required"));
            return;
        }
        
        if ($request['quantity'] <= 0) {
            // Remove item if quantity is 0 or negative
            removeFromCart($db, $request['product_id'], $user_id);
            return;
        }
        
        // Check stock
        $checkQuery = "SELECT stock_quantity FROM products WHERE id = ? AND status = 'active'";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(1, $request['product_id']);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() == 0) {
            http_response_code(404);
            echo json_encode(array("message" => "Product not found"));
            return;
        }
        
        $product = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if ($product['stock_quantity'] < $request['quantity']) {
            http_response_code(400);
            echo json_encode(array("message" => "Insufficient stock"));
            return;
        }
        
        $query = "UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $request['quantity']);
        $stmt->bindParam(2, $user_id);
        $stmt->bindParam(3, $request['product_id']);
        
        if ($stmt->execute()) {
            echo json_encode(array("message" => "Cart updated successfully"));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Failed to update cart"));
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Database error: " . $e->getMessage()));
    }
}

function removeFromCart($db, $product_id, $user_id) {
    try {
        $query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $product_id);
        
        if ($stmt->execute()) {
            echo json_encode(array("message" => "Item removed from cart"));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Failed to remove item"));
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Database error: " . $e->getMessage()));
    }
}

function clearCart($db, $user_id) {
    try {
        $query = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(array("message" => "Cart cleared successfully"));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Failed to clear cart"));
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Database error: " . $e->getMessage()));
    }
}
?>
