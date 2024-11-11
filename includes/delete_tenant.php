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
if (isset($_GET['id'])) {
    $tenant_id = $_GET['id'];

    // Kết nối cơ sở dữ liệu
 // Đảm bảo rằng bạn đã thiết lập kết nối cơ sở dữ liệu

    // Thực hiện truy vấn để xóa khách hàng
    try {
        $stmt = $pdo->prepare("DELETE FROM tenant WHERE id = :tenant_id");
        $stmt->execute(['tenant_id' => $tenant_id]);

        // Điều hướng trở lại trang danh sách khách hàng
        header("Location: tenant_list.php"); // Thay tenant_list.php bằng trang hiển thị danh sách khách
        exit;
    } catch (PDOException $e) {
        echo "Lỗi khi xóa khách hàng: " . $e->getMessage();
    }
}
?>
