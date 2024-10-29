<?php
$listAllroom = getRaw("
    SELECT room.*, area.tenkhuvuc AS tenkhuvuc, cost.giathue AS giathue,            
    GROUP_CONCAT(DISTINCT equipment.tenthietbi SEPARATOR ', ') AS tenthietbi
    FROM room
    JOIN area_room ON room.id = area_room.room_id
    JOIN area ON area_room.area_id = area.id
    JOIN cost_room ON room.id = cost_room.room_id
    JOIN cost ON cost_room.cost_id = cost_id
    JOIN equipment_room ON room.id = equipment_room.room_id
    JOIN equipment ON equipment_room.equipment_id = equipment_id
    GROUP BY room.id
");
//group by giúp trả về tất cả phòng có trong room
$dataRoom = json_encode($listAllroom);

$roomFinal = json_decode($dataRoom, true);


require_once './vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$spreadsheet->getDefaultStyle()
   ->getFont()
   ->setName('Arial')
   ->setSize(10);

$tableHead = [
   'font' => [
      'color' => [
         'rgb' => 'FFFFFF'
      ],
      'bold' => true,
      'size' => 10
   ],
   'fill' => [
      'fillType' => Fill::FILL_SOLID,
      'startColor' => [
         'rgb' => "538ED5",
      ]
   ]
];

//even row
$evenRow = [
   'fill' => [
      'fillType' => Fill::FILL_SOLID,
      'startColor' => [
         'rgb' => 'FFFFFF'
      ]
   ]
];


//odd row
$oddRow = [
   'fill' => [
      'fillType' => Fill::FILL_SOLID,
      'startColor' => [
         'rgb' => 'CCCCCC'
      ]
   ]
];


// heading 
$spreadsheet->getActiveSheet()
   ->setCellValue('A1', 'Danh sách phòng trọ');

// merge heading
$spreadsheet->getActiveSheet()->mergeCells("A1:F1");
$spreadsheet->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
$spreadsheet->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$spreadsheet->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$spreadsheet->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);


// set column with
$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(6);
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(6);
$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(50);

//header Text
$spreadsheet->getActiveSheet()
   ->setCellValue('A2', 'ID')
   ->setCellValue('B2', 'Khu vực')
   ->setCellValue('C2', 'Tên phòng')
   ->setCellValue('D2', 'Diện tích')
   ->setCellValue('E2', 'Giá thuê')
   ->setCellValue('F2', 'Giá tiền cọc')
   ->setCellValue('G2', 'Số lượng')
   ->setCellValue('H2', 'Ngày lập hóa đơn')
   ->setCellValue('I2', 'Chu kỳ')
   ->setCellValue('J2', 'Ngày vào ở')
   ->setCellValue('K2', 'Ngày hết hạn')
   ->setCellValue('L2', 'Cơ sở vật chất');

// background color
$spreadsheet->getActiveSheet()->getStyle('A2:L2')->applyFromArray($tableHead);

//
$spreadsheet->getActiveSheet()
   ->getStyle('E')
   ->getNumberFormat()
   ->setFormatCode(NumberFormat::FORMAT_NUMBER);
//FORMAT_DATE_YYYYMMDD

$spreadsheet->getActiveSheet()
   ->getStyle('F')
   ->getNumberFormat()
   ->setFormatCode(NumberFormat::FORMAT_NUMBER);

// Content
$date = time();

$row = 3;
foreach ($roomFinal as $room) {
   $spreadsheet->getActiveSheet()->setCellValue('A' . $row, $room['id']);
   $spreadsheet->getActiveSheet()->setCellValue('B' . $row, $room['tenkhuvuc']);
   $spreadsheet->getActiveSheet()->setCellValue('C' . $row, $room['tenphong']);
   $spreadsheet->getActiveSheet()->setCellValue('D' . $row, $room['dientich']);
   $spreadsheet->getActiveSheet()->setCellValue('E' . $row, $room['giathue']);
   $spreadsheet->getActiveSheet()->setCellValue('F' . $row, $room['tiencoc']);
   $spreadsheet->getActiveSheet()->setCellValue('G' . $row, $room['soluong']);
   $spreadsheet->getActiveSheet()->setCellValue('H' . $row, $room['ngaylaphd']);
   $spreadsheet->getActiveSheet()->setCellValue('I' . $row, $room['chuky']);
   $spreadsheet->getActiveSheet()->setCellValue('J' . $row, $room['ngayvao']);
   $spreadsheet->getActiveSheet()->setCellValue('K' . $row, $room['ngayra']);
   $spreadsheet->getActiveSheet()->setCellValue('L' . $row, $room['tenthietbi']);
   // set row style
   if ($row % 2 == 0) {
      $spreadsheet->getActiveSheet()->getStyle('A' . $row . ':L' . $row)->applyFromArray($evenRow);
   } else {
      $spreadsheet->getActiveSheet()->getStyle('A' . $row . ':L' . $row)->applyFromArray($oddRow);
   }

   $row++;
}

// set the autofilter
$firstRow = 2;
$lastRow = $row - 1;
$spreadsheet->getActiveSheet()->setAutoFilter("A" . $firstRow . ":L" . $lastRow);


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Danh sách phòng.xlsx"');

// $writer = IOFactory::createWriter($spreadsheet, 'Xlsx'); 
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
