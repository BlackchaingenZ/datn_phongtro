<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "123456";
$dbname = "datn";

try {
    // Kết nối PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Trả về lỗi kết nối
    echo json_encode(['success' => false, 'message' => "Kết nối thất bại: " . $e->getMessage()]);
    exit();
}

if (isset($_GET['id']) && isset($_GET['contract_id'])) {
    $tenant_id = $_GET['id'];
    $contract_id = $_GET['contract_id'];

    try {
        $pdo->beginTransaction();

        // Xóa khách hàng khỏi hợp đồng
        $stmt = $pdo->prepare("DELETE FROM contract_tenant WHERE tenant_id_1 = :tenant_id AND contract_id_1 = :contract_id");
        $stmt->execute(['tenant_id' => $tenant_id, 'contract_id' => $contract_id]);

        // Xóa khách hàng khỏi bảng tenant
        $stmt = $pdo->prepare("DELETE FROM tenant WHERE id = :tenant_id");
        $stmt->execute(['tenant_id' => $tenant_id]);

        $pdo->commit();
        // Trả về kết quả thành công
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Xử lý lỗi khi xóa và trả về thông báo lỗi
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => "Lỗi khi xoá khách hàng: " . $e->getMessage()]);
    }
} else {
    // Yêu cầu không hợp lệ
    echo json_encode(['success' => false, 'message' => "Yêu cầu không hợp lệ."]);
}
?>
