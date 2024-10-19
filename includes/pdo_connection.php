<?php
// Thông tin kết nối cơ sở dữ liệu
$host = 'localhost'; // Tên host
$dbname = 'datn'; // Tên cơ sở dữ liệu
$username = 'root'; // Tên người dùng MySQL
$password = '123456'; // Mật khẩu MySQL

try {
    // Tạo kết nối PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Thiết lập chế độ lỗi PDO thành Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Xử lý lỗi khi kết nối thất bại
    die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
}
?>
