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
        getCategories($db);
        break;
    case 'POST':
        createCategory($db);
        break;
    case 'PUT':
        updateCategory($db);
        break;
    case 'DELETE':
        deleteCategory($db);
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
}

function getCategories($db) {
    try {
        $query = "SELECT c.id, c.name, c.description, c.created_at,
                         (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as product_count
                  FROM categories c
                  ORDER BY c.id ASC";
        $stmt = $db->prepare($query);
        $stmt->execute();

        $categories = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $row;
        }

        echo json_encode($categories);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Database error: " . $e->getMessage()));
    }
}

function createCategory($db) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['name'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Category name is required"));
        return;
    }
    
    try {
        $query = "INSERT INTO categories (name, description) VALUES (?, ?)";
        $stmt = $db->prepare($query);

        if ($stmt->execute([$data['name'], $data['description'] ?? ''])) {
            $category_id = $db->lastInsertId();
            echo json_encode(array(
                "message" => "Category created successfully",
                "id" => $category_id
            ));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Failed to create category"));
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Database error: " . $e->getMessage()));
    }
}

function updateCategory($db) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['id']) || empty($data['name'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Category ID and name are required"));
        return;
    }
    
    try {
        $query = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
        $stmt = $db->prepare($query);

        if ($stmt->execute([$data['name'], $data['description'] ?? '', $data['id']])) {
            echo json_encode(array("message" => "Category updated successfully"));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Failed to update category"));
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Database error: " . $e->getMessage()));
    }
}

function deleteCategory($db) {
    $id = $_GET['id'] ?? null;
    
    if (empty($id)) {
        http_response_code(400);
        echo json_encode(array("message" => "Category ID is required"));
        return;
    }
    
    try {
        $db->beginTransaction();

        // Update products to remove category reference
        $updateQuery = "UPDATE products SET category_id = NULL WHERE category_id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$id]);

        // Delete the category
        $deleteQuery = "DELETE FROM categories WHERE id = ?";
        $deleteStmt = $db->prepare($deleteQuery);

        if ($deleteStmt->execute([$id])) {
            $db->commit();
            echo json_encode(array("message" => "Category deleted successfully"));
        } else {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(array("message" => "Failed to delete category"));
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Database error: " . $e->getMessage()));
    }
}
?>
