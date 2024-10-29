<?php
$listAlltenant = getRaw("SELECT *, tenphong FROM tenant INNER JOIN room ON tenant.room_id = room.id");
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
            ->setCellValue('A1', 'Danh sách khách thuê');

// merge heading
$spreadsheet->getActiveSheet()->mergeCells("A1:F1");
$spreadsheet->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
$spreadsheet->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$spreadsheet->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$spreadsheet->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);


// set column with
$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(6);
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(20);
$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
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
            ->setCellValue('B2', 'Tên khách')
            ->setCellValue('C2', 'SDT')
            ->setCellValue('D2', 'Ngày sinh')
            ->setCellValue('E2', 'Giới tính')
            ->setCellValue('F2', 'Địa chỉ')
            ->setCellValue('G2', 'Nghề nghiệp')
            ->setCellValue('H2', 'CCCD/CMND')
            ->setCellValue('I2', 'Ngày cấp')
            ->setCellValue('J2', 'Phòng đang ở')
            ->setCellValue('K2', 'Ngày vào ở');

// background color
$spreadsheet->getActiveSheet()->getStyle('A2:K2')->applyFromArray($tableHead);

//
$spreadsheet->getActiveSheet()
            ->getStyle('E')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD);

            $spreadsheet->getActiveSheet()
            ->getStyle('K')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD);

$spreadsheet->getActiveSheet()
            ->getStyle('F')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER);

// Content
$date = time();

$row = 3;
foreach($tenantFinal as $item) {
      $spreadsheet->getActiveSheet()->setCellValue('A'.$row, $item['id']);
      $spreadsheet->getActiveSheet()->setCellValue('B'.$row, $item['tenkhach']);
      $spreadsheet->getActiveSheet()->setCellValue('C'.$row, $item['sdt']);
      $spreadsheet->getActiveSheet()->setCellValue('D'.$row, $item['ngaysinh']);
      $spreadsheet->getActiveSheet()->setCellValue('E'.$row, $item['gioitinh']);
      $spreadsheet->getActiveSheet()->setCellValue('F'.$row, $item['diachi']);
      $spreadsheet->getActiveSheet()->setCellValue('G'.$row, $item['nghenghiep']);
      $spreadsheet->getActiveSheet()->setCellValue('H'.$row, $item['cmnd']);
      $spreadsheet->getActiveSheet()->setCellValue('I'.$row, $item['ngaycap']);
      $spreadsheet->getActiveSheet()->setCellValue('J'.$row, $item['tenphong']);    
      $spreadsheet->getActiveSheet()->setCellValue('K'.$row, $item['ngayvao']);    

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
header('Content-Disposition: attachment;filename="Danh sách khách thuê.xlsx"');

// $writer = IOFactory::createWriter($spreadsheet, 'Xlsx'); 
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

