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
    // Tạo kết nối PDO
    $pdo = new PDO('mysql:host=localhost;dbname=datn', 'root', '123456');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// Truy vấn dữ liệu
$sql = "SELECT 
            category_collect.tendanhmuc,
            SUM(receipt.sotien) AS tong_thu
        FROM 
            receipt
        INNER JOIN 
            category_collect 
        ON 
            receipt.danhmucthu_id = category_collect.id
        GROUP BY 
            category_collect.tendanhmuc
        ORDER BY tong_thu DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tạo file Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Tiêu đề bảng
$sheet->setCellValue('A1', 'Khoản thu');
$sheet->setCellValue('B1', 'Tổng thu (VNĐ)');

// Điều chỉnh độ rộng của cột
$sheet->getColumnDimension('A')->setWidth(30); // Cột A rộng 30 ký tự
$sheet->getColumnDimension('B')->setWidth(20); // Cột B rộng 20 ký tự

// Định dạng tiêu đề (in đậm và căn trái)
$sheet->getStyle('A1:B1')->getFont()->setBold(true); // In đậm tiêu đề
$sheet->getStyle('A1:B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Căn trái tiêu đề

// Điền dữ liệu vào bảng
$rowIndex = 2;
foreach ($results as $row) {
    $sheet->setCellValue('A' . $rowIndex, $row['tendanhmuc']);
    $sheet->setCellValue('B' . $rowIndex, $row['tong_thu']);

    // Căn trái toàn bộ nội dung
    $sheet->getStyle('A' . $rowIndex . ':B' . $rowIndex)
        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

    $rowIndex++;
}

// Tạo file Excel và tải về
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="bao_cao_tong_thu.xlsx"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;
