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

// Truy vấn dữ liệu doanh thu theo khu vực
$sql = "SELECT tenkhuvuc AS tenkhuvuc, SUM(receipt.sotien) AS doanhthu
        FROM area
        JOIN area_room ON area.id = area_room.area_id
        JOIN room ON area_room.room_id = room.id
        JOIN receipt ON room.id = receipt.room_id
        WHERE receipt.ngaythu IS NOT NULL
        GROUP BY tenkhuvuc
        ORDER BY doanhthu DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$areaRevenue = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tạo đối tượng Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Thiết lập tiêu đề bảng
$sheet->setCellValue('A1', 'Khu vực');
$sheet->setCellValue('B1', 'Doanh thu (VNĐ)');

$sheet->getStyle('A1:B1')->getFont()->setBold(true);
$sheet->getStyle('A1:B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

// Dữ liệu bảng
$row = 2;
if (!empty($areaRevenue)) {
        foreach ($areaRevenue as $area) {
                $sheet->setCellValue('A' . $row, htmlspecialchars($area['tenkhuvuc'], ENT_QUOTES, 'UTF-8'));
                $sheet->setCellValue('B' . $row, number_format($area['doanhthu'], 0, ',', '.') . ' đ');
                $row++;
        }
} else {
        $sheet->setCellValue('A2', 'Không có dữ liệu doanh thu.');
        $sheet->mergeCells('A2:B2');
}

// Thiết lập kích thước cột
$sheet->getColumnDimension('A')->setAutoSize(true);
$sheet->getColumnDimension('B')->setAutoSize(true);

// Tạo đối tượng Writer và xuất file Excel
$writer = new Xlsx($spreadsheet);
$filename = 'bao_cao_doanh_thu_theo_khu_vuc.xlsx';

// Gửi file đến trình duyệt
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Xuất file Excel
$writer->save('php://output');
exit;
