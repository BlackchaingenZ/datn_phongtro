<?php
    
    $userId = isLogin()['user_id'];
    $userDetail = getUserInfo($userId); 
    $roomId  = $userDetail['room_id'];

    $allTenant = getRaw("SELECT * FROM tenant WHERE room_id = $roomId");
    $contractDetail  = firstRaw("SELECT * FROM contract WHERE contract.room_id = $roomId");
    $tenantId = $contractDetail['tenant_id'];
    
    $tenantDetail = firstRaw("SELECT * FROM tenant WHERE id = $tenantId");
    $roomtDetail = firstRaw("SELECT * FROM room WHERE id = $roomId");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
       ul {
            line-height: 1.5;
       }

        body {
            line-height: 1.2;
            background: #eee;
        }

        .container {
            background: #fff;
            margin: 30px 300px;
            padding: 0 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="padding: 2cm 1.5cm 2cm 1.5cm;">
            <div style="text-align: center">
        <p style="text-align: center">
            <span style="font-size: 12pt;"><b>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</b></span><br>
            <b style="font-size: 12pt;text-decoration: underline">Độc lập - Tự do - Hạnh phúc</b>
        </p><p>
        </p></div>
        <div style="text-align: center;margin: 30px 0;font-size: 12pt;">
         <p><strong style="text-transform:uppercase;">HỢP ĐỒNG CHO THUÊ PHÒNG TRỌ THẢO NGUYÊN</strong></p>
        </div>
        <div style="text-align: left;font-size: 12pt;">
         <p><strong>BÊN A : BÊN CHO THUÊ (PHÒNG TRỌ)</strong></p>
                    <table style="width: 100%">
                        <tbody>
                        <tr>
                            <td colspan="2">
                                <p>Họ và tên: Nguyễn Minh Thảo</p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"> 
                                <p>Năm sinh: 23/9/1972</p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <p>CMND/CCCD: 012020100322</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>Ngày cấp: 27/08/2023 </p>
                            </td>
                            <td>
                                <p>Nơi cấp: Công an thành phố Hải Phòng</p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <p>Thường trú: 56 Nam Pháp 1 Ngô Quyền,Hải Phòng</p>
                            </td>
                        </tr>
                    </tbody></table>
        </div>
        <div style="text-align: left;font-size: 12pt;">
         <p><strong>BÊN B : BÊN THUÊ (PHÒNG TRỌ)</strong></p>
                    <table style="width: 100%">
                        <tbody>
                            
                        <tr><b>Người đại diện là:</b></tr>
                        <tr>
                            <td colspan="2">
                                <p>Họ và tên: <?php echo $tenantDetail['tenkhach'] ?> </p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"> 
                                <p>Năm sinh: <?php echo $tenantDetail['ngaysinh'] ?>  </p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"> 
                                <p>CMND/CCCD: <?php echo $tenantDetail['cmnd'] ?>  </p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>Ngày cấp: <?php echo $tenantDetail['ngaycap'] ?>   </p>
                            </td>
                            <td>
                                <p>Nơi cấp: Công an thành phố Hải Phòng  </p>
                            </td>
                        </tr>
             
                        <tr>
                            <td colspan="2">
                                <p>Thường trú: <?php echo $tenantDetail['diachi'] ?>  </p>
                            </td>
                        </tr>
                    </tbody></table>
                    <b>Các thành viên của phòng bao gồm:</b> <br />
                    <br/>
                    <?php foreach($allTenant as $tenant) { ?>
                        <span><?php echo $tenant['tenkhach'] ?> - CCCD: <?php echo $tenant['cmnd'] ?></span> <br />
                    <?php } ?>
        </div>
        <div style="text-align: left;font-size: 12pt;">
         <p>Hai bên cùng thỏa thuận và đồng ý với nội dung sau :</p>
        </div>
        <div style="text-align: left;font-size: 12pt;">
          <p><strong>Điều 1:</strong></p>
                    <ul style="list-style-type: circle;">
                        <li> <span>Bên A đồng ý cho bên B thuê một phòng trọ thuộc địa chỉ: 56 Nam Pháp 1 Ngô Quyền,Hải Phòng </span></li>
                        <li><span>Thời hạn thuê phòng trọ là kể từ ngày <?php echo $contractDetail['ngayvao'] ?> đến ngày <?php echo $contractDetail['ngayra'] ?>  </span></li>
                    </ul>
                    <p><strong>Điều 2:</strong></p>
                    <ul style="list-style-type: circle;">
                        <li><span>Giá tiền thuê phòng trọ là <?php echo number_format($roomtDetail['giathue'], 0, ',', '.') ?>đ </span></li>
                        <li><span>Tiền thuê phòng trọ bên B thanh toán cho bên A từ ngày 25 dương lịch hàng tháng.</span></li>
                        <li><span>Bên B đặt tiền cọc trước <?php echo number_format($roomtDetail['tiencoc'], 0, ',', '.') ?>đ (Bằng chữ : Một triệu năm trăm ngàn đồng) cho bên A. Tiền cọc sẽ được trả khi hết hạn hợp đồng.
                        </span></li><li><span>Trong trường hợp bên B ngưng hợp đồng trước thời hạn thì phải chịu mất tiền cọc.</span></li>
                        <li><span>Bên A ngưng hợp đồng (lấy lại phòng trọ) trước thời hạn thì bồi thường gấp đôi số tiền bên B đã cọc.</span></li>
                    </ul>
                    <p><strong>Điều 3:</strong> Trách nhiệm bên A.</p>
                    <ul style="list-style-type: circle;">
                        <li><span>Giao phòng trọ, trang thiết bị trong phòng trọ cho bên B đúng ngày ký hợp đồng.</span></li>
                        <li><span>Hướng dẫn bên B chấp hành đúng các quy định của địa phương, hoàn tất mọi thủ tục giấy tờ đăng ký tạm trú cho bên B.</span></li>
                    </ul>
                    <p><strong>Điều 4:</strong> Trách nhiệm bên B.</p>
                    <ul style="list-style-type: circle;">
                        <li><span>Trả tiền thuê phòng trọ hàng tháng theo hợp đồng.</span></li>
                        <li><span>Sử dụng đúng mục đích thuê nhà, khi cần sữa chữa, cải tạo theo yêu cầu sử dụng riêng phải được sự đồng ý của bên A.</span></li>
                        <li><span>Đồ đạt trang thiết bị trong phòng trọ phải có trách nhiệm bảo quản cẩn thận không làm hư hỏng mất mát.</span></li>
                    </ul>
                    <p><strong>Điều 5:</strong> Điều khoản chung.</p>
                    <ul style="list-style-type: circle;">
                        <li><span>Bên A và bên B thực hiện đúng các điều khoản ghi trong hợp đồng.</span></li>
                        <li><span>Trường hợp có tranh chấp hoặc một bên vi phạm hợp đồng thì hai bên cùng nhau bàn bạc giải quyết, nếu không giải quyết được thì yêu cầu
                        </span></li><li><span>Hợp đồng được lập thành 02 bản có giá trị ngang nhau, mỗi bên giữ 01 bản</span></li>
                    </ul>
        </div>
        <div style="font-size: 12pt; margin: 40px 0">
        <p style="text-align: right;"><i>........, Ngày...... Tháng...... năm 20.........</i></p>
        <div style="display: flex; margin-top: 1.5rem;">
            <div style="flex: 0 0 auto;width: 50%;float: left;text-align: center">
                <strong>BÊN A </strong><br>
                <i>Ký và ghi rõ họ tên</i>
                <div style="padding: 10px;
                height: 150px;
                width: 100%;
                text-align: center;
                overflow: hidden;">
                                        <span></span>
                                </div>
                                <div>
                        Nguyễn Minh Thảo
                    </div>
                        </div>
            <div style="flex: 0 0 auto;width: 50%;float: left;text-align: center">
                <strong>BÊN B </strong><br>
                <i>Ký và ghi rõ họ tên</i>
                <div style="padding: 10px;
                height: 150px;
                width: 100%;
                text-align: center;
                overflow: hidden;">
                                        <span></span>
                                </div>
                                <div>
                                <?php echo $tenantDetail['tenkhach'] ?>
                    </div>
                        </div>
        </div>
        </div>
         </div>
    </div>
</body>
</html>