<?php  
   // Xử lý hiện dữ liệu cũ của người dùng
$body = getBody();
$id = $_GET['id'];


if(!empty($body['id'])) {
    $billId = $body['id'];   
    $billDetail  = firstRaw("SELECT img_sodienmoi FROM bill WHERE id=$billId");
    if (!empty($billDetail)) {
        // Gán giá trị billDetail vào setFalsh
        setFlashData('billDetail', $billDetail);
    
    }else {
        redirect('?module=bill');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉ số điện</title>
    <style>

        body  {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: #eee;
        }

        img {
            width: 70%;
            height: 80%;
        }
    </style>
</head>
<body>
    <img src="<?php echo $billDetail['img_sodienmoi'] ?>" alt="">
</body>
</html>