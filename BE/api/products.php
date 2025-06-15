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

// Get request data
$input = file_get_contents('php://input');
$request = json_decode($input, true);



switch($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getProduct($db, $_GET['id']);
        } else {
            getProducts($db);
        }
        break;
    case 'POST':
        authenticate();
        if ($request === null) {
            http_response_code(400);
            echo json_encode(array("message" => "Invalid JSON data"));
            break;
        }
        createProduct($db, $request);
        break;
    case 'PUT':
        authenticate();
        if ($request === null) {
            http_response_code(400);
            echo json_encode(array("message" => "Invalid JSON data"));
            break;
        }
        updateProduct($db, $request);
        break;
    case 'DELETE':
        authenticate();
        deleteProduct($db, $_GET['id']);
        break;
}



function getProducts($db) {
    // Check if user is admin to show all products or just active ones
    $headers = getallheaders();
    $isAdmin = false;

    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $decoded = json_decode(base64_decode($token), true);
        if ($decoded && isset($decoded['role']) && $decoded['role'] === 'admin') {
            $isAdmin = true;
        }
    }

    if ($isAdmin) {
        $query = "SELECT p.*, c.name as category_name FROM products p
                  LEFT JOIN categories c ON p.category_id = c.id
                  ORDER BY p.created_at DESC";
    } else {
        $query = "SELECT p.*, c.name as category_name FROM products p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.status = 'active' ORDER BY p.created_at DESC";
    }

    $stmt = $db->prepare($query);
    $stmt->execute();

    $products = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $products[] = $row;
    }

    echo json_encode($products);
}

function getProduct($db, $id) {
    $query = "SELECT p.*, c.name as category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($product);
}

function createProduct($db, $request) {
    try {


        // Validate required fields
        if (empty($request['name']) || empty($request['price']) || !isset($request['stock_quantity'])) {
            http_response_code(400);
            echo json_encode(array("message" => "Name, price and stock quantity are required"));
            return;
        }

        $query = "INSERT INTO products (name, description, price, stock_quantity, category_id, image_url, status)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);

        // Handle category_id - convert empty string to null
        $category_id = (!empty($request['category_id']) && $request['category_id'] !== '') ? $request['category_id'] : null;

        $success = $stmt->execute([
            $request['name'],
            $request['description'] ?? '',
            $request['price'],
            $request['stock_quantity'],
            $category_id,
            $request['image_url'] ?? '',
            $request['status'] ?? 'active'
        ]);

        if ($success) {
            $product_id = $db->lastInsertId();

            echo json_encode(array(
                "message" => "Product created successfully",
                "id" => $product_id
            ));
        } else {

            http_response_code(500);
            echo json_encode(array("message" => "Failed to create product"));
        }
    } catch (PDOException $e) {

        http_response_code(500);
        echo json_encode(array("message" => "Database error: " . $e->getMessage()));
    }
}

function updateProduct($db, $request) {
    try {


        // Validate required fields
        if (empty($request['id']) || empty($request['name']) || empty($request['price']) || !isset($request['stock_quantity'])) {
            http_response_code(400);
            echo json_encode(array("message" => "ID, name, price and stock quantity are required"));
            return;
        }

        $query = "UPDATE products SET name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ?, image_url = ?, status = ? WHERE id = ?";
        $stmt = $db->prepare($query);

        // Handle category_id - convert empty string to null
        $category_id = (!empty($request['category_id']) && $request['category_id'] !== '') ? $request['category_id'] : null;

        $success = $stmt->execute([
            $request['name'],
            $request['description'] ?? '',
            $request['price'],
            $request['stock_quantity'],
            $category_id,
            $request['image_url'] ?? '',
            $request['status'] ?? 'active',
            $request['id']
        ]);

        if ($success) {

            echo json_encode(array("message" => "Product updated successfully"));
        } else {

            http_response_code(500);
            echo json_encode(array("message" => "Failed to update product"));
        }
    } catch (PDOException $e) {

        http_response_code(500);
        echo json_encode(array("message" => "Database error: " . $e->getMessage()));
    }
}

function deleteProduct($db, $id) {
    $query = "DELETE FROM products WHERE id = ?";
    $stmt = $db->prepare($query);

    if ($stmt->execute([$id])) {
        echo json_encode(array("message" => "Product deleted successfully"));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to delete product"));
    }
}
?>
