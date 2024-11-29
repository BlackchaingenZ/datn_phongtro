<?php
$listAlltenant = getRaw("SELECT room.tenphong, bill.id, bill.mahoadon, tienphong, tiendien, tiennuoc, tienrac, tienmang, thang, tongtien, bill.create_at FROM bill INNER JOIN room ON room.id = bill.room_id");

// print_r($listAlltenant); die;
$dataTenant = json_encode($listAlltenant);

$tenantFinal = json_decode($dataTenant,true);


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
      'bold'=> true,
      'size'=> 10
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
            ->setCellValue('A1', 'Danh sách hóa đơn');

// merge heading
$spreadsheet->getActiveSheet()->mergeCells("A1:F1");
$spreadsheet->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
$spreadsheet->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$spreadsheet->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$spreadsheet->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);


// set column with
$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(6);
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(15);

//header Text
$spreadsheet->getActiveSheet()
            ->setCellValue('A2', 'ID')
            ->setCellValue('B2', 'Mã hoá đơn')
            ->setCellValue('C2', 'Tên phòng')
            ->setCellValue('D2', 'Tiền phòng')
            ->setCellValue('E2', 'Tiền điện')
            ->setCellValue('F2', 'Tiền nước')
            ->setCellValue('G2', 'Tiền rác')
            ->setCellValue('H2', 'Tiền Wifi')
            ->setCellValue('I2', 'Tháng')
            ->setCellValue('J2', 'Tổng cộng')
            ->setCellValue('K2', 'Ngày lập hóa đơn');

// background color
$spreadsheet->getActiveSheet()->getStyle('A2:K2')->applyFromArray($tableHead);

//
$spreadsheet->getActiveSheet()
            ->getStyle('E')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER);

$spreadsheet->getActiveSheet()
            ->getStyle('F')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER);

// Content
$date = time();

$row = 3;
foreach($tenantFinal as $item) {
      $spreadsheet->getActiveSheet()->setCellValue('A'.$row, $item['id']);
      $spreadsheet->getActiveSheet()->setCellValue('B'.$row, $item['mahoadon']);
      $spreadsheet->getActiveSheet()->setCellValue('C'.$row, $item['tenphong']);
      $spreadsheet->getActiveSheet()->setCellValue('D'.$row, $item['tienphong']);
      $spreadsheet->getActiveSheet()->setCellValue('E'.$row, $item['tiendien']);
      $spreadsheet->getActiveSheet()->setCellValue('F'.$row, $item['tiennuoc']);
      $spreadsheet->getActiveSheet()->setCellValue('G'.$row, $item['tienrac']);
      $spreadsheet->getActiveSheet()->setCellValue('H'.$row, $item['tienmang']);
      $spreadsheet->getActiveSheet()->setCellValue('I'.$row, $item['thang']);
      $spreadsheet->getActiveSheet()->setCellValue('J'.$row, $item['tongtien']);
      $spreadsheet->getActiveSheet()->setCellValue('K'.$row, $item['create_at']);    

               // set row style
             if($row % 2 == 0) {
                  $spreadsheet->getActiveSheet()->getStyle('A'.$row.':K'.$row)->applyFromArray($evenRow);
             }else {
                  $spreadsheet->getActiveSheet()->getStyle('A'.$row.':K'.$row)->applyFromArray($oddRow);
             }

             $row++;
}

// set the autofilter
$firstRow = 2;
$lastRow = $row-1;
$spreadsheet->getActiveSheet()->setAutoFilter("A".$firstRow.":K".$lastRow);


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Danh sách hóa đơn.xlsx"');

// $writer = IOFactory::createWriter($spreadsheet, 'Xlsx'); 
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

