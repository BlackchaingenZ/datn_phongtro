<?php

require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\PhpSpreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

if(isset($_POST['save_excel_data'])) {
    $fileName = $_FILES['import_file']['name'];
    $file_ext = pathinfo($fileName, PATHINFO_EXTENSION);

    $allowed_ext = ['xls', 'csv', 'xlsx'];

    if(in_array($file_ext, $allowed_ext)) {
        $inputFileNamePath = $_FILES['import_file']['tmp_name'];
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileNamePath);
        $data = $spreadsheet->getActiveSheet()->toArray();
        
        $count = "0";
        foreach($data as $row) {

            if($count > 0) {
                $tenkhach = $row['0'];
                $sdt = $row['1'];
                $ngaysinh = $row['2'];
                $gioitinh = $row['3'];
                $diachi = $row['4'];
                $nghenghiep = $row['5'];
                $cmnd = $row['6'];
                $ngaycap = $row['7'];
                $zalo = $row['8'];
    
                $dataInsert = [
                    'tenkhach' => $tenkhach,
                    'sdt' => $sdt,
                    'ngaysinh' => $ngaysinh,
                    'gioitinh' => $gioitinh,
                    'diachi' => $diachi,
                    'nghenghiep' => $nghenghiep,
                    'cmnd' => $cmnd,
                    'ngaycap' => $ngaycap,
                    'zalo' => $zalo,
                ];
                
                $insertStatus = insert('tenant', $dataInsert);
                $msg = true;
            } else {
                $count = "1";
            }
        }

        if(isset($msg)) {
            setFlashData('msg', 'Import dữ liệu khách thuê thành công');
            setFlashData('msg_type', 'suc');
            redirect('/admin/?module=tenant');
        } else {
            setFlashData('msg', 'Import dữ liệu khách thuê thất bại');
            setFlashData('msg_type', 'err');
            redirect('/admin/?module=tenant');
        }

    } else {
            setFlashData('msg', 'Dữ liệu đầu vào không hợp lệ, mời chọn lại');
            setFlashData('msg_type', 'err');
            redirect('/admin/?module=tenant');
    }
}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Excel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
    <h4>Import Excel</h4>

    <form action="" method="post" enctype="multipart/form-data">
        <div class="col-6">
            <input type="file" name="import_file" class="form-control">
        </div>
        <button type="submit" name="save_excel_data" class="btn btn-primary mt-2">Import</button>
    </form>

</body>
</html>
