<?php
// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "123456";
$dbname = "datn";

try {
    // Tạo kết nối PDO
    $pdo = new PDO('mysql:host=localhost;dbname=datn', 'root', '123456');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Nhận dữ liệu JSON từ yêu cầu POST
$data = file_get_contents("php://input");
$customers = json_decode($data, true); // Chuyển đổi JSON thành mảng PHP

$newCustomerIds = []; // Khởi tạo mảng để lưu các ID khách hàng mới tạo

if ($customers && is_array($customers)) {
    // Chuẩn bị câu lệnh SQL để chèn dữ liệu, bao gồm room_id
    $stmt = $pdo->prepare("INSERT INTO tenant (tenkhach, cmnd, ngaysinh, gioitinh, diachi, room_id) VALUES (:tenkhach, :cmnd, :ngaysinh, :gioitinh, :diachi, :room_id)");

    foreach ($customers as $customer) {
        // Liên kết giá trị
        $stmt->bindParam(':tenkhach', $customer['tenkhach']);
        $stmt->bindParam(':cmnd', $customer['cmnd']);
        $stmt->bindParam(':ngaysinh', $customer['ngaysinh']);
        $stmt->bindParam(':gioitinh', $customer['gioitinh']);
        $stmt->bindParam(':diachi', $customer['diachi']);
        $stmt->bindParam(':room_id', $customer['roomId']); // Thêm dòng này để liên kết room_id

        // Thực thi câu lệnh
        $stmt->execute();

        // Lấy ID của khách hàng vừa thêm và thêm vào mảng
        $newCustomerIds[] = $pdo->lastInsertId();
    }

    // Lưu danh sách ID vào session
    session_start();
    $_SESSION['newCustomerIds'] = $newCustomerIds;

    echo json_encode(["status" => "success", "message" => "Đã thêm thành công khách hàng.", "ids" => $newCustomerIds]);
} else {
    echo json_encode(["status" => "error", "message" => "Không có dữ liệu khách hàng hoặc định dạng không hợp lệ."]);
}

// Đóng kết nối
$pdo = null;
?>

