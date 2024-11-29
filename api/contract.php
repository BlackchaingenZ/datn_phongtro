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

$userId = $data['user_id'] ?? ''; // ID người dùng được gửi từ client

// Kiểm tra nếu user_id không được cung cấp
if (empty($userId)) {
    echo json_encode(['error' => 'Người dùng không xác định']);
    exit;
}

try {
    // Lấy thông tin người dùng
    $stmtUser = $pdo->prepare("SELECT room_id FROM users WHERE id = :user_id");
    $stmtUser->execute(['user_id' => $userId]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['error' => 'Không tìm thấy thông tin người dùng']);
        exit;
    }

    $roomId = $user['room_id'];

    // Lấy thông tin hợp đồng
    $stmtContract = $pdo->prepare("SELECT * FROM contract WHERE room_id = :room_id");
    $stmtContract->execute(['room_id' => $roomId]);
    $contract = $stmtContract->fetch(PDO::FETCH_ASSOC);

    if (!$contract) {
        echo json_encode(['error' => 'Không tìm thấy hợp đồng cho phòng này']);
        exit;
    }

    $contractId = $contract['id'];

    // Lấy thông tin tenant
    $stmtTenant = $pdo->prepare("
        SELECT 
            GROUP_CONCAT(DISTINCT CONCAT(tenant.tenkhach, ' (ID: ', tenant.id, ')') ORDER BY tenant.tenkhach ASC SEPARATOR '\n') AS danh_sach_ten_khach, 
            GROUP_CONCAT(tenant.ngaysinh ORDER BY tenant.tenkhach ASC SEPARATOR '\n') AS danh_sach_ngay_sinh,
            GROUP_CONCAT(tenant.cmnd ORDER BY tenant.tenkhach ASC SEPARATOR '\n') AS danh_sach_cmnd,
            GROUP_CONCAT(tenant.ngaycap ORDER BY tenant.tenkhach ASC SEPARATOR '\n') AS danh_sach_ngay_cap,
            GROUP_CONCAT(tenant.diachi ORDER BY tenant.tenkhach ASC SEPARATOR '\n') AS danh_sach_dia_chi
        FROM contract_tenant 
        INNER JOIN tenant ON contract_tenant.tenant_id_1 = tenant.id 
        WHERE contract_tenant.contract_id_1 = :contract_id
    ");
    $stmtTenant->execute(['contract_id' => $contractId]);
    $tenantDetails = $stmtTenant->fetch(PDO::FETCH_ASSOC);

    // Lấy thông tin dịch vụ của hợp đồng
    $stmtServices = $pdo->prepare("SELECT services_id FROM contract_services WHERE contract_id = :contract_id");
    $stmtServices->execute(['contract_id' => $contractId]);
    $servicesDetail = $stmtServices->fetchAll(PDO::FETCH_ASSOC);

    // Lấy thông tin phòng và giá thuê
    $stmtRoom = $pdo->prepare("
        SELECT room.*, cost.giathue 
        FROM room 
        LEFT JOIN cost_room ON room.id = cost_room.room_id 
        LEFT JOIN cost ON cost_room.cost_id = cost.id 
        WHERE room.id = :room_id
    ");
    $stmtRoom->execute(['room_id' => $roomId]);
    $roomDetail = $stmtRoom->fetch(PDO::FETCH_ASSOC);

    $price = $roomDetail['giathue'] ?? null;

    // Kết quả trả về
    echo json_encode([
        'success' => true,
        'data' => [
            'userDetail' => ['room_id' => $roomId],
            'contractDetail' => $contract,
            'tenantDetails' => $tenantDetails,
            'servicesDetail' => $servicesDetail,
            'roomDetail' => $roomDetail,
            'price' => $price,
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Đã xảy ra lỗi trong quá trình xử lý: ' . $e->getMessage()]);
}
