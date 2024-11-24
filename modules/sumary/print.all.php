<?php 
require 'vendor/autoload.php'; // Tải thư viện PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "123456";
$dbname = "datn";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Tạo file Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Hàm thêm dữ liệu vào bảng
function addDataToSheet($sheet, $header, $data, $startRow) {
    // Thêm tiêu đề
    $sheet->fromArray($header, null, 'A' . $startRow);
    $sheet->getStyle('A' . $startRow . ':' . chr(65 + count($header) - 1) . $startRow)->getFont()->setBold(true);

    // Thêm dữ liệu
    $currentRow = $startRow + 1;
    foreach ($data as $row) {
        $sheet->fromArray($row, null, 'A' . $currentRow);
        $currentRow++;
    }

    // Trả về hàng cuối cùng đã ghi
    return $currentRow + 1; // Dòng trống
}

// Truy vấn và thêm dữ liệu thống kê khoản thu
$sql = "SELECT 
            category_collect.tendanhmuc AS 'Khoản thu',
            SUM(receipt.sotien) AS 'Tổng thu (VNĐ)'
        FROM 
            receipt
        INNER JOIN 
            category_collect 
        ON 
            receipt.danhmucthu_id = category_collect.id
        GROUP BY 
            category_collect.tendanhmuc
        ORDER BY 'Tổng thu (VNĐ)' DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentRow = addDataToSheet($sheet, ['Khoản thu', 'Tổng thu (VNĐ)'], $results, 1);

// Truy vấn và thêm dữ liệu thống kê khoản chi
$sql = "SELECT 
            category_spend.tendanhmuc AS 'Khoản chi',
            SUM(payment.sotien) AS 'Tổng chi (VNĐ)'
        FROM 
            payment
        INNER JOIN 
            category_spend 
        ON 
            payment.danhmucchi_id = category_spend.id
        GROUP BY 
            category_spend.tendanhmuc
        ORDER BY 'Tổng chi (VNĐ)' DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentRow = addDataToSheet($sheet, ['Khoản chi', 'Tổng chi (VNĐ)'], $results, $currentRow);

// Truy vấn và thêm dữ liệu doanh thu theo khu vực
$sql = "SELECT tenkhuvuc AS 'Khu vực', SUM(receipt.sotien) AS 'Doanh thu (VNĐ)'
        FROM area
        JOIN area_room ON area.id = area_room.area_id
        JOIN room ON area_room.room_id = room.id
        JOIN receipt ON room.id = receipt.room_id
        WHERE receipt.ngaythu IS NOT NULL
        GROUP BY tenkhuvuc
        ORDER BY 'Doanh thu (VNĐ)' DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentRow = addDataToSheet($sheet, ['Khu vực', 'Doanh thu (VNĐ)'], $results, $currentRow);

// Truy vấn và thêm dữ liệu doanh thu theo phòng
$sql = "SELECT tenphong AS 'Tên Phòng', SUM(receipt.sotien) AS 'Doanh thu (VNĐ)'
        FROM room
        JOIN receipt ON room.id = receipt.room_id
        WHERE receipt.ngaythu IS NOT NULL
        GROUP BY tenphong
        ORDER BY 'Doanh thu (VNĐ)' DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

addDataToSheet($sheet, ['Tên Phòng', 'Doanh thu (VNĐ)'], $results, $currentRow);

// Định dạng cột tự động
foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Tạo file Excel và tải về
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="bao_cao_chi_tiet.xlsx"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
