<?php  
   // Xử lý hiện dữ liệu cũ của người dùng
$body = getBody();
$id = $_GET['id'];


if(!empty($body['id'])) {
    $tenantId = $body['id'];   
    $tenantDetail  = firstRaw("SELECT anhmattruoc, anhmatsau FROM tenant WHERE id=$tenantId");
    if (!empty($tenantDetail)) {
        // Gán giá trị tenantDetail vào setFalsh
        setFlashData('tenantDetail', $tenantDetail);
    
    }else {
        redirect('?module=tenant');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
    <img src="<?php echo $tenantDetail['anhmattruoc'] ?>" alt="">
</body>
</html>