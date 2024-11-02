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

function addContract($room_id, $ngaylaphopdong, $ngayvao, $ngayra, $tinhtrangcoc, $create_at, $ghichu)
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO contract (room_id, ngaylaphopdong, ngayvao, ngayra, tinhtrangcoc, create_at, ghichu) VALUES (:room_id, :ngaylaphopdong, :ngayvao, :ngayra, :tinhtrangcoc, :create_at, :ghichu)");
    $stmt->execute([
        ':room_id' => $room_id,
        ':ngaylaphopdong' => $ngaylaphopdong,
        ':ngayvao' => $ngayvao,
        ':ngayra' => $ngayra,
        ':tinhtrangcoc' => $tinhtrangcoc,
        ':create_at' => $create_at,
        ':ghichu' => $ghichu
    ]);
    return $pdo->lastInsertId(); // Trả về ID của hợp đồng vừa được tạo
}

function addTenant($tenkhach, $ngaysinh, $gioitinh, $diachi, $room_id)
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO tenant (tenkhach, ngaysinh, gioitinh, diachi, room_id) VALUES (:tenkhach, :ngaysinh, :gioitinh, :diachi, :room_id)");
    $stmt->execute([
        ':tenkhach' => $tenkhach,
        ':ngaysinh' => $ngaysinh,
        ':gioitinh' => $gioitinh,
        ':diachi' => $diachi,
        ':room_id' => $room_id
    ]);
    return $pdo->lastInsertId(); // Trả về ID của khách thuê vừa được tạo
}

function linkContractTenant($contract_id, $tenant_id)
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO contract_tenant (contract_id_1, tenant_id_1) VALUES (:contract_id, :tenant_id)");
    $stmt->execute([
        ':contract_id' => $contract_id,
        ':tenant_id' => $tenant_id
    ]);
}

function linkContractService($contract_id, $services_id)
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO contract_services (contract_id, services_id) VALUES (:contract_id, :services_id)");
    $stmt->execute([
        ':contract_id' => $contract_id,
        ':services_id' => $services_id
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu hợp đồng từ POST
    $room_id = $_POST['room_id'] ?? null;
    $ngaylaphopdong = $_POST['ngaylaphopdong'] ?? null;
    $ngayvao = $_POST['ngayvao'] ?? null;
    $ngayra = $_POST['ngayra'] ?? null;
    $tinhtrangcoc = $_POST['tinhtrangcoc'] ?? null;
    $create_at = date("Y-m-d H:i:s");
    $ghichu = $_POST['ghichu'] ?? null;

    // Lấy danh sách dịch vụ từ POST
    $services = $_POST['services'] ?? []; // Danh sách dịch vụ được gửi từ form

    // Giải mã danh sách khách thuê tạm từ JSON
    $tempCustomersData = $_POST['tempCustomersData'] ?? '[]';
    $tempCustomers = json_decode($tempCustomersData, true);

    if ($room_id && $ngaylaphopdong && $ngayvao && $ngayra && $tinhtrangcoc) {
        // Thêm hợp đồng
        $contract_id = addContract($room_id, $ngaylaphopdong, $ngayvao, $ngayra, $tinhtrangcoc, $create_at, $ghichu);

        // Thêm từng dịch vụ vào bảng contract_services
        foreach ($services as $services_id) {
            linkContractService($contract_id, $services_id);
        }

        // Thêm từng khách thuê từ danh sách tạm vào cơ sở dữ liệu
        foreach ($tempCustomers as $customer) {
            $tenkhach = $customer['tenkhach'];
            $ngaysinh = $customer['ngaysinh'];
            $gioitinh = $customer['gioitinh'];
            $diachi = $customer['diachi'];
            $cmnd = $customer['cmnd'];

            // Thêm khách thuê vào bảng tenant và lấy tenant_id
            $tenant_id = addTenant($tenkhach, $ngaysinh, $gioitinh, $diachi, $room_id);

            // Liên kết hợp đồng với khách thuê trong bảng contract_tenant
            linkContractTenant($contract_id, $tenant_id);
        }

        echo "Hợp đồng, khách thuê và dịch vụ đã được thêm thành công!";
    } else {
        echo "Thiếu thông tin cần thiết để thêm hợp đồng.";


    }
}
