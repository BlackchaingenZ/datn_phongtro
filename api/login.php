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

// Kiểm tra phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Phương thức không được phép
    echo json_encode(['error' => 'Phương thức không được phép']);
    exit;
}

// Nhận dữ liệu JSON từ yêu cầu
$data = json_decode(file_get_contents('php://input'), true);

$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

// Kiểm tra nếu email hoặc mật khẩu rỗng
if (empty($email) || empty($password)) {
    echo json_encode(['error' => 'Email và mật khẩu không được để trống']);
    exit;
}

try {
    // Truy vấn kiểm tra thông tin người dùng
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = :email AND status = 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Kiểm tra mật khẩu
        if (password_verify($password, $user['password'])) {
            echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công']);
        } else {
            echo json_encode(['error' => 'Mật khẩu không chính xác']);
        }
    } else {
        echo json_encode(['error' => 'Email chưa được kích hoạt hoặc không tồn tại']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Đã xảy ra lỗi trong quá trình xử lý']);
}
?>
