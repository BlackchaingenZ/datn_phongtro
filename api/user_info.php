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
    // Chấp nhận yêu cầu OPTIONS và trả về mã trạng thái 200 OK
    http_response_code(200);
    exit;
}

// Thiết lập tiêu đề trả về là JSON
header('Content-Type: application/json');

// Giả sử bạn đã kiểm tra người dùng đã đăng nhập
$userId = isLogin()['user_id'];
$userDetail = getUserInfo($userId);

// Trả về thông tin người dùng dưới dạng JSON
if ($userDetail) {
    echo json_encode($userDetail);
} else {
    echo json_encode(['error' => 'User not found']);
}
?>

