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

// Verify token
function verifyToken($headers) {
    if (!isset($headers['Authorization'])) {
        error_log("No Authorization header found");
        return false;
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $decoded = json_decode(base64_decode($token), true);

    if (!$decoded) {
        error_log("Failed to decode token");
        return false;
    }

    if ($decoded['exp'] < time()) {
        error_log("Token expired");
        return false;
    }

    error_log("Token verified for user: " . $decoded['user_id']);
    return $decoded;
}

$headers = getallheaders();
$user = verifyToken($headers);

if (!$user) {
    http_response_code(401);
    echo json_encode(array("message" => "Unauthorized"));
    exit();
}

switch($method) {
    case 'GET':
        getUserProfile($db, $user['user_id']);
        break;
    case 'PUT':
        updateUserProfile($db, $user['user_id'], $request);
        break;
    case 'POST':
        if (isset($request['action'])) {
            switch($request['action']) {
                case 'change_password':
                    changePassword($db, $user['user_id'], $request);
                    break;
                case 'upload_avatar':
                    uploadAvatar($db, $user['user_id'], $request);
                    break;
            }
        }
        break;
}

function getUserProfile($db, $user_id) {
    // First try with new columns, fallback to basic columns if they don't exist
    try {
        $query = "SELECT id, username, email, full_name, phone, address, avatar_url, birth_date, gender, created_at, last_login FROM users WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "User not found"));
        }
    } catch (PDOException $e) {
        // Fallback to basic columns if new columns don't exist
        $query = "SELECT id, username, email, full_name, phone, address, created_at FROM users WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            // Add default values for missing columns
            $user['avatar_url'] = null;
            $user['birth_date'] = null;
            $user['gender'] = null;
            $user['last_login'] = null;
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "User not found"));
        }
    }
}

function updateUserProfile($db, $user_id, $request) {
    // Validate required fields
    if (empty($request['full_name']) || empty($request['email'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Full name and email are required"));
        return;
    }

    // Check if email is already taken by another user
    $query = "SELECT id FROM users WHERE email = ? AND id != ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $request['email']);
    $stmt->bindParam(2, $user_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(array("message" => "Email already exists"));
        return;
    }

    // Try to update with new columns, fallback to basic columns if they don't exist
    try {
        $query = "UPDATE users SET
                    full_name = ?,
                    email = ?,
                    phone = ?,
                    address = ?,
                    birth_date = ?,
                    gender = ?,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE id = ?";

        $stmt = $db->prepare($query);

        $success = $stmt->execute([
            $request['full_name'],
            $request['email'],
            $request['phone'] ?? null,
            $request['address'] ?? null,
            $request['birth_date'] ?? null,
            $request['gender'] ?? null,
            $user_id
        ]);

        if ($success) {
            getUserProfile($db, $user_id);
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Update failed"));
        }
    } catch (PDOException $e) {
        // Fallback to basic columns update
        $query = "UPDATE users SET
                    full_name = ?,
                    email = ?,
                    phone = ?,
                    address = ?,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE id = ?";

        $stmt = $db->prepare($query);

        $success = $stmt->execute([
            $request['full_name'],
            $request['email'],
            $request['phone'] ?? null,
            $request['address'] ?? null,
            $user_id
        ]);

        if ($success) {
            getUserProfile($db, $user_id);
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Update failed: " . $e->getMessage()));
        }
    }
}

function changePassword($db, $user_id, $request) {
    if (empty($request['current_password']) || empty($request['new_password'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Current password and new password are required"));
        return;
    }
    
    // Verify current password
    $query = "SELECT password FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!password_verify($request['current_password'], $row['password'])) {
            http_response_code(400);
            echo json_encode(array("message" => "Current password is incorrect"));
            return;
        }
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "User not found"));
        return;
    }
    
    // Update password
    $query = "UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $db->prepare($query);
    
    $hashed_password = password_hash($request['new_password'], PASSWORD_DEFAULT);
    $stmt->bindParam(1, $hashed_password);
    $stmt->bindParam(2, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(array("message" => "Password changed successfully"));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Password change failed"));
    }
}

function uploadAvatar($db, $user_id, $request) {
    if (empty($request['avatar_url'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Avatar URL is required"));
        return;
    }

    // Try to update avatar_url column, fallback if column doesn't exist
    try {
        $query = "UPDATE users SET avatar_url = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $request['avatar_url']);
        $stmt->bindParam(2, $user_id);

        if ($stmt->execute()) {
            echo json_encode(array(
                "message" => "Avatar updated successfully",
                "avatar_url" => $request['avatar_url']
            ));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Avatar update failed"));
        }
    } catch (PDOException $e) {
        // If avatar_url column doesn't exist, add it first
        try {
            $alterQuery = "ALTER TABLE users ADD COLUMN avatar_url VARCHAR(500) NULL";
            $db->exec($alterQuery);

            // Now try the update again
            $query = "UPDATE users SET avatar_url = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $request['avatar_url']);
            $stmt->bindParam(2, $user_id);

            if ($stmt->execute()) {
                echo json_encode(array(
                    "message" => "Avatar updated successfully",
                    "avatar_url" => $request['avatar_url']
                ));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => "Avatar update failed"));
            }
        } catch (PDOException $e2) {
            http_response_code(500);
            echo json_encode(array("message" => "Avatar update failed: Database schema issue"));
        }
    }
}
?>
