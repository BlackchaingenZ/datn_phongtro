<?php
$listAllroom = getRaw("
    SELECT room.*, area.tenkhuvuc AS tenkhuvuc, cost.giathue AS giathue, contract.ngayvao AS ngayvao,contract.ngayra AS ngayra,            
    GROUP_CONCAT(DISTINCT equipment.tenthietbi SEPARATOR ', ') AS tenthietbi
    FROM room
    JOIN area_room ON room.id = area_room.room_id
    JOIN area ON area_room.area_id = area.id
    JOIN cost_room ON room.id = cost_room.room_id
    JOIN cost ON cost_room.cost_id = cost_id
    JOIN equipment_room ON room.id = equipment_room.room_id
    JOIN equipment ON equipment_room.equipment_id = equipment_id
    LEFT JOIN contract ON contract.room_id = room.id
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
$spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(50);

//header Text
$spreadsheet->getActiveSheet()
   ->setCellValue('A2', 'Mã Phòng')
   ->setCellValue('B2', 'Khu vực')
   ->setCellValue('C2', 'Tên phòng')
   ->setCellValue('D2', 'Diện tích : m2')
   ->setCellValue('E2', 'Giá thuê')
   ->setCellValue('F2', 'Giá tiền cọc')
   ->setCellValue('G2', 'Số lượng')
   ->setCellValue('H2', 'Ngày vào ở')
   ->setCellValue('I2', 'Ngày hết hạn')
   ->setCellValue('J2', 'Cơ sở vật chất');

// background color
$spreadsheet->getActiveSheet()->getStyle('A2:J2')->applyFromArray($tableHead);

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
   $spreadsheet->getActiveSheet()
   ->getStyle('A' . $row)
   ->getAlignment()
   ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
   $spreadsheet->getActiveSheet()->setCellValue('B' . $row, $room['tenkhuvuc']);
   $spreadsheet->getActiveSheet()->setCellValue('C' . $row, $room['tenphong']);
   $spreadsheet->getActiveSheet()->setCellValue('D' . $row, $room['dientich']);
   // $spreadsheet->getActiveSheet()->setCellValue('D' . $row, $room['dientich']. 'm2');
   $spreadsheet->getActiveSheet()->setCellValue('E' . $row, $room['giathue']);
   $spreadsheet->getActiveSheet()->setCellValue('F' . $row, $room['tiencoc']);
   $spreadsheet->getActiveSheet()->setCellValue('G' . $row, $room['soluong']);
   $spreadsheet->getActiveSheet()->setCellValue('H' . $row, $room['ngayvao']);
   $spreadsheet->getActiveSheet()->setCellValue('I' . $row, $room['ngayra']);
   $spreadsheet->getActiveSheet()->setCellValue('J' . $row, $room['tenthietbi']);
   // set row style
   if ($row % 2 == 0) {
      $spreadsheet->getActiveSheet()->getStyle('A' . $row . ':J' . $row)->applyFromArray($evenRow);
   } else {
      $spreadsheet->getActiveSheet()->getStyle('A' . $row . ':J' . $row)->applyFromArray($oddRow);
   }

   $row++;
}

// set the autofilter
$firstRow = 2;
$lastRow = $row - 1;
$spreadsheet->getActiveSheet()->setAutoFilter("A" . $firstRow . ":J" . $lastRow);


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Danh sách phòng.xlsx"');

// $writer = IOFactory::createWriter($spreadsheet, 'Xlsx'); 
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
