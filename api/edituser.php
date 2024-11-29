<?php
// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "123456";
$dbname = "datn";

try {
    // Tạo kết nối PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Kết nối cơ sở dữ liệu thất bại: ' . $e->getMessage()]));
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Xử lý yêu cầu OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Set the response header to indicate JSON response
header('Content-Type: application/json');

// Kiểm tra phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Phương thức không được phép
    echo json_encode(['error' => 'Phương thức không được phép']);
    exit;
}

// Get the raw POST data (JSON)
$inputData = json_decode(file_get_contents('php://input'), true);

// Check if the input data is valid JSON
if (!$inputData) {
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

// Get the data passed in (from GET parameters)
$id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$id) {
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

// Get user details from the database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $id]);
$userDetail = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userDetail) {
    echo json_encode(['error' => 'User not found']);
    exit;
}

// Handle updating user details
$body = $inputData; // The body data from the JSON request
$errors = []; // Initialize an array for errors

// Validate password (if provided)
if (!empty(trim($body['password']))) {
    if (empty(trim($body['confirm_password']))) {
        $errors['confirm_password']['required'] = 'Confirmation password is required!';
    } else {
        if (trim($body['password']) !== trim($body['confirm_password'])) {
            $errors['confirm_password']['match'] = 'Passwords do not match';
        }
    }
}

// If there are no validation errors, proceed with the update
if (empty($errors)) {
    $room_id = !empty($body['room_id']) ? $body['room_id'] : null;

    $dataUpdate = [
        'fullname' => $body['fullname'],
        'email' => $body['email'],
        'group_id' => $body['group_id'],
        'room_id' => $room_id,
        'status' => $body['status']
    ];

    // If password is provided, update it as well
    if (!empty(trim($body['password']))) {
        $dataUpdate['password'] = password_hash($body['password'], PASSWORD_DEFAULT);
    }

    // Prepare the update SQL query
    $updateQuery = "UPDATE users SET fullname = :fullname, email = :email, group_id = :group_id, room_id = :room_id, status = :status";
    
    if (isset($dataUpdate['password'])) {
        $updateQuery .= ", password = :password";
    }

    $updateQuery .= " WHERE id = :id";

    // Prepare and execute the update statement
    $stmt = $pdo->prepare($updateQuery);
    $dataUpdate['id'] = $id; // Add the user ID to the update data

    $updateStatus = $stmt->execute($dataUpdate);

    if ($updateStatus) {
        echo json_encode(['success' => 'User information updated successfully']);
    } else {
        echo json_encode(['error' => 'An error occurred while updating user information']);
    }
} else {
    // Return the errors as a response
    echo json_encode(['errors' => $errors]);
}
