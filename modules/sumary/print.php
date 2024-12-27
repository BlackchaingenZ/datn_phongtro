<?php
require 'vendor/autoload.php'; // Tải thư viện PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Hàm thêm dữ liệu vào bảng
function addDataToSheet($sheet, $header, $data, $startRow)
{
    // Thêm tiêu đề
    $sheet->fromArray($header, null, 'A' . $startRow);
    $sheet->getStyle('A' . $startRow . ':' . chr(65 + count($header) - 1) . $startRow)->getFont()->setBold(true);

    // Thêm dữ liệu
    $currentRow = $startRow + 1;
    foreach ($data as $row) {
        foreach ($row as $key => $value) {
            // Kiểm tra nếu cột chứa số tiền
            if (is_numeric($value)) {
                $row[$key] = number_format($value, 0, ',', '.') . ' VNĐ';
            }
        }
        $sheet->fromArray($row, null, 'A' . $currentRow);
        $currentRow++;
    }

    // Trả về hàng cuối cùng đã ghi
    return $currentRow;
}


// Khởi tạo file Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Truy vấn dữ liệu và thêm vào bảng
function queryAndAddData($pdo, $sheet, $sql, $params, $header, $startRow, $title)
{
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Thêm tiêu đề bảng
    $sheet->setCellValue('A' . $startRow, $title);
    $sheet->mergeCells('A' . $startRow . ':B' . $startRow);
    $sheet->getStyle('A' . $startRow)->getFont()->setBold(true);
    $sheet->getStyle('A' . $startRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Thêm dữ liệu
    return addDataToSheet($sheet, $header, $results, $startRow + 1);
}

// Biến lưu tham số tìm kiếm
$params = [];
if (!empty($month)) $params[':month'] = $month;
if (!empty($year)) $params[':year'] = $year;

// Truy vấn và thêm dữ liệu
$currentRow = 1;

// Phòng chưa thu
$sql_chuathu = "
    SELECT room.tenphong AS 'Tên phòng', bill.tongtien AS 'Tổng tiền', area.tenkhuvuc AS 'Khu vực'
    FROM room
    INNER JOIN bill ON room.id = bill.room_id
    LEFT JOIN receipt ON bill.id = receipt.bill_id
    LEFT JOIN area_room ON room.id = area_room.room_id
    LEFT JOIN area ON area_room.area_id= area.id

    WHERE receipt.bill_id IS NULL
";
if (!empty($month)) $sql_chuathu .= " AND MONTH(bill.create_at) = :month";
if (!empty($year)) $sql_chuathu .= " AND YEAR(bill.create_at) = :year";
$sql_chuathu .= " ORDER BY room.tenphong ASC";

$currentRow = queryAndAddData($pdo, $sheet, $sql_chuathu, $params, ['Tên phòng', 'Tổng tiền', 'Khu vực'], $currentRow, 'Danh sách phòng chưa thu');

// Phòng đã thu
$sql_dathu = "
    SELECT room.tenphong AS 'Tên phòng', bill.sotiendatra AS 'Số tiền đã trả', area.tenkhuvuc AS 'Khu vực'
    FROM room
    INNER JOIN bill ON room.id = bill.room_id
    LEFT JOIN area_room ON room.id = area_room.room_id
    LEFT JOIN area ON area_room.area_id = area.id
    WHERE bill.trangthaihoadon = 1
";
if (!empty($month)) $sql_dathu .= " AND MONTH(bill.create_at) = :month";
if (!empty($year)) $sql_dathu .= " AND YEAR(bill.create_at) = :year";
$sql_dathu .= " ORDER BY room.tenphong ASC";

$currentRow = queryAndAddData($pdo, $sheet, $sql_dathu, $params, ['Tên phòng', 'Số tiền đã trả', 'Khu vực'], $currentRow, 'Danh sách phòng đã thu');

// Phòng còn nợ
$sql_conno = "
    SELECT room.tenphong AS 'Tên phòng', bill.sotienconthieu AS 'Số tiền còn thiếu',area.tenkhuvuc AS 'Khu vực'
    FROM room
    INNER JOIN bill ON room.id = bill.room_id
    LEFT JOIN area_room ON room.id = area_room.room_id
    LEFT JOIN area ON area_room.area_id =area.id
    WHERE bill.trangthaihoadon = 3
";
if (!empty($month)) $sql_conno .= " AND MONTH(bill.create_at) = :month";
if (!empty($year)) $sql_conno .= " AND YEAR(bill.create_at) = :year";
$sql_conno .= " ORDER BY room.tenphong ASC";

$currentRow = queryAndAddData($pdo, $sheet, $sql_conno, $params, ['Tên phòng', 'Số tiền còn thiếu', 'Khu vực'], $currentRow, 'Danh sách phòng còn nợ');

// Định dạng cột tự động
foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Tạo file Excel và tải về
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="bao_cao_thu_chi.xlsx"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
