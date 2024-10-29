<?php
$listAlltenant = getRaw("
SELECT 
    *, 
    tenant.tenkhach AS tenkhach, 
    tenant2.tenkhach AS tenkhach_2, 
    tenphong, 
    contract.ghichu,
    cost.giathue AS giathue, -- Lấy giathue từ bảng cost
    GROUP_CONCAT(DISTINCT services.tendichvu SEPARATOR ', ') AS tendichvu,
    tiencoc, 
    chuky 
FROM contract 
INNER JOIN tenant ON tenant.id = contract.tenant_id 
LEFT JOIN tenant AS tenant2 ON tenant2.id = contract.tenant_id_2
INNER JOIN room ON room.id = contract.room_id
INNER JOIN cost_room ON cost_room.room_id = room.id -- Kết nối cost_room với room
INNER JOIN cost ON cost.id = cost_room.cost_id -- Kết nối cost với cost_room
LEFT JOIN contract_services ON contract_services.contract_id = contract.id -- Kết nối contract_services với contract
LEFT JOIN services ON services.id = contract_services.services_id -- Kết nối services với contract_services
GROUP BY contract.id
");



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
            ->setCellValue('A1', 'Danh sách hợp đồng');

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
$spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(30);
$spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(40);
//header Text
$spreadsheet->getActiveSheet()
            ->setCellValue('A2', 'ID')
            ->setCellValue('B2', 'Tên phòng')
            ->setCellValue('C2', 'Người thuê phòng')
            ->setCellValue('D2', 'Tổng thành viên')
            ->setCellValue('E2', 'Giá thuê')
            ->setCellValue('F2', 'Giá cọc')
            ->setCellValue('G2', 'Chu kỳ thu')
            ->setCellValue('H2', 'Ngày lập')
            ->setCellValue('I2', 'Ngày hết hạn')
            ->setCellValue('J2', 'Tình trạng')
            ->setCellValue('K2', 'Ghi chú')
            ->setCellValue('L2', 'Dịch vụ');
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
      $spreadsheet->getActiveSheet()->setCellValue('B'.$row, $item['tenphong']);
      $spreadsheet->getActiveSheet()->setCellValue('C' . $row, $item['tenkhach'] . "\n" . $item['tenkhach_2']);
      //cho phéo xuống dòng
      $spreadsheet->getActiveSheet()->getStyle('C' . $row)->getAlignment()->setWrapText(true);
      $spreadsheet->getActiveSheet()->setCellValue('D'.$row, $item['soluongthanhvien']);
      $spreadsheet->getActiveSheet()->setCellValue('E'.$row, $item['giathue']);
      $spreadsheet->getActiveSheet()->setCellValue('F'.$row, $item['tiencoc']);
      $spreadsheet->getActiveSheet()->setCellValue('G'.$row, $item['chuky']);
      $spreadsheet->getActiveSheet()->setCellValue('H'.$row, $item['ngaylaphopdong']);
      $spreadsheet->getActiveSheet()->setCellValue('I'.$row, $item['ngayra']);
      $spreadsheet->getActiveSheet()->setCellValue('J'.$row, $item['trangthaihopdong']);    
      $spreadsheet->getActiveSheet()->setCellValue('K'.$row, $item['ghichu']);   
      $spreadsheet->getActiveSheet()->setCellValue('L'.$row, $item['tendichvu']);   
               // set row style
             if($row % 2 == 0) {
                  $spreadsheet->getActiveSheet()->getStyle('A'.$row.':L'.$row)->applyFromArray($evenRow);
             }else {
                  $spreadsheet->getActiveSheet()->getStyle('A'.$row.':L'.$row)->applyFromArray($oddRow);
             }

             $row++;
}

// set the autofilter
$firstRow = 2;
$lastRow = $row-1;
$spreadsheet->getActiveSheet()->setAutoFilter("A".$firstRow.":L".$lastRow);


header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Danh sách hợp đồng.xlsx"');

// $writer = IOFactory::createWriter($spreadsheet, 'Xlsx'); 
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

