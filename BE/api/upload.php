<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once 'config.php';

$database = new Database();
$db = $database->getConnection();

// Create uploads directory if not exists
$uploadDir = dirname(__DIR__) . '/uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Create subdirectories
$productDir = $uploadDir . 'products/';

if (!file_exists($productDir)) {
    mkdir($productDir, 0777, true);
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        handleUpload();
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
}

function handleUpload() {
    global $uploadDir, $productDir, $db;
    
    try {
        // Check if file was uploaded
        if (!isset($_FILES['file'])) {
            http_response_code(400);
            echo json_encode(array("message" => "No file uploaded"));
            return;
        }
        
        $file = $_FILES['file'];
        $uploadType = $_POST['type'] ?? 'product'; // Only 'product' supported
        
        // Validate file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode(array("message" => "Invalid file type. Only JPEG, PNG, GIF, WebP allowed"));
            return;
        }
        
        // Check file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            http_response_code(400);
            echo json_encode(array("message" => "File too large. Maximum size is 5MB"));
            return;
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        
        // Only support product uploads
        $targetDir = $productDir;
        $targetPath = $targetDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Generate URL
            $baseUrl = 'http://localhost/ecommerce-project/BE/uploads/';
            $fileUrl = $baseUrl . 'products/' . $filename;
            
            echo json_encode(array(
                "message" => "File uploaded successfully",
                "filename" => $filename,
                "url" => $fileUrl,
                "type" => $uploadType
            ));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Failed to upload file"));
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Upload error: " . $e->getMessage()));
    }
}

// Function to delete file
function deleteFile($filename) {
    global $productDir;

    $filePath = $productDir . $filename;

    if (file_exists($filePath)) {
        return unlink($filePath);
    }

    return false;
}
?>
