<?php

$body = getBody();
$id = $_GET['id'];

// Lấy thông tin hợp đồng
$contractDetail = firstRaw("SELECT * FROM contract WHERE id = $id");

// Lấy tenant_id từ bảng contract_tenant// Lấy danh sách khách thuê của hợp đồng

$tenantDetails = firstRaw("SELECT GROUP_CONCAT(DISTINCT CONCAT(tenant.tenkhach, ' (ID: ', tenant.id, ')') ORDER BY tenant.tenkhach ASC SEPARATOR '\n') AS danh_sach_ten_khach, 
GROUP_CONCAT( tenant.ngaysinh ORDER BY tenant.tenkhach ASC SEPARATOR '\n') AS danh_sach_ngay_sinh,
GROUP_CONCAT( tenant.cmnd ORDER BY tenant.tenkhach ASC SEPARATOR '\n') AS danh_sach_cmnd,
GROUP_CONCAT( tenant.ngaycap ORDER BY tenant.tenkhach ASC SEPARATOR '\n') AS danh_sach_ngay_cap,
GROUP_CONCAT( tenant.diachi ORDER BY tenant.tenkhach ASC SEPARATOR '\n') AS danh_sach_dia_chi
FROM contract_tenant 
INNER JOIN tenant ON contract_tenant.tenant_id_1 = tenant.id 
WHERE contract_tenant.contract_id_1 = $id");
$tenantDetail = firstRaw("SELECT tenant_id_1 FROM contract_tenant WHERE contract_id_1 = $id");
$servicesDetail = firstRaw("SELECT services_id FROM contract_services WHERE services_id = $id");
// Lấy thông tin người thuê từ bảng tenant bằng tenant_id_1
$tenantId = $tenantDetail['tenant_id_1'];
$tenantDetail = firstRaw("SELECT * FROM tenant WHERE id = $tenantId");

// Lấy thông tin phòng từ bảng room và giá thuê từ bảng cost thông qua bảng cost_room
$roomId = $contractDetail['room_id'];
$roomDetail = firstRaw("SELECT room.*, cost.giathue 
                        FROM room 
                        LEFT JOIN cost_room ON room.id = cost_room.room_id 
                        LEFT JOIN cost ON cost_room.cost_id = cost.id 
                        WHERE room.id = $roomId");

// Lấy giá thuê
$price = $roomDetail['giathue']; // Lấy giá thuê từ kết quả truy vấn
$contractDetail = firstRaw("
    SELECT 
        sotiencoc, 
        ngayvao, 
        ngayra, 
        dieukhoan1, 
        dieukhoan2, 
        dieukhoan3,
        GROUP_CONCAT(DISTINCT services.tendichvu ORDER BY services.tendichvu ASC SEPARATOR ', ') AS tendichvu,
        GROUP_CONCAT(DISTINCT services.giadichvu ORDER BY services.tendichvu ASC SEPARATOR ', ') AS giadichvu,
        GROUP_CONCAT(DISTINCT services.donvitinh ORDER BY services.tendichvu ASC SEPARATOR ', ') AS donvitinh
    FROM contract
    LEFT JOIN contract_services ON contract.id = contract_services.contract_id
    LEFT JOIN services ON contract_services.services_id = services.id
    WHERE contract.id = $id
    GROUP BY contract.id
");

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
                </p>
                <p></p>
            </div>
            <div style="text-align: center;margin: 30px 0;font-size: 12pt;">
                <p><strong style="text-transform:uppercase;">HỢP ĐỒNG CHO THUÊ PHÒNG TRỌ THẢO NGUYÊN</strong></p>
            </div>
            <div style="text-align: left;font-size: 12pt;">
                <p><strong>BÊN A : BÊN CHO THUÊ (PHÒNG TRỌ)</strong></p>
                <table style="width: 100%">
                    <tbody>
                        <tr>
                            <td colspan="2">
                                <p><strong>Họ và tên: Nguyễn Minh Thảo</strong></p>
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
                                <p>Ngày cấp: 27/08/2023</p>
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
                    </tbody>
                </table>
            </div>
            <div style="text-align: left;font-size: 12pt;">
                <p><strong>BÊN B : BÊN THUÊ (PHÒNG TRỌ)</strong></p>
                <table style="width: 100%">
                    <tbody>
                        <?php
                        // Tách các danh sách khách thuê để hiển thị
                        $tenKhachList = explode("\n", $tenantDetails['danh_sach_ten_khach']);
                        $ngaySinhList = explode("\n", $tenantDetails['danh_sach_ngay_sinh']);
                        $cmndList = explode("\n", $tenantDetails['danh_sach_cmnd']);
                        $ngayCapList = explode("\n", $tenantDetails['danh_sach_ngay_cap']);
                        $diaChiList = explode("\n", $tenantDetails['danh_sach_dia_chi']);

                        foreach ($tenKhachList as $index => $tenKhach) {
                            // Tách tên khách hàng từ chuỗi có định dạng "Tên khách hàng (ID: X)"
                            $tenKhach = explode(" (ID:", $tenKhach)[0];  // Lấy phần tên trước " (ID:"


                        ?>
                            <tr>
                                <td colspan="2">
                                    <p> <strong>Họ và tên: <?php echo $tenKhach; ?> </strong></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <p>Năm sinh: <?php
                                                    // Kiểm tra nếu có giá trị ngày hợp đồng
                                                    echo isset($ngaySinhList[$index]) && $ngaySinhList[$index] != '0000-00-00'
                                                        ? getDateFormat($ngaySinhList[$index], 'd-m-Y')
                                                        : 'Không xác định';
                                                    ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <p>CMND/CCCD: <?php echo $cmndList[$index]; ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p>Ngày cấp: <?php
                                                    // Kiểm tra nếu có giá trị ngày hợp đồng
                                                    echo isset($ngayCapList[$index]) && $ngayCapList[$index] != '0000-00-00'
                                                        ? getDateFormat($ngayCapList[$index], 'd-m-Y')
                                                        : 'Không xác định';
                                                    ?></p>
                                </td>
                                <td>
                                    <p>Nơi cấp: Công an thành phố Hải Phòng</p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <p>Thường trú: <?php echo isset($diaChiList[$index]) ? $diaChiList[$index] : ''; ?></p>
                                </td>
                            </tr>

                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div style="text-align: left;font-size: 12pt;">
                <p>Hai bên cùng thỏa thuận và đồng ý với nội dung sau :</p>
            </div>
            <div style="text-align: left;font-size: 12pt;">
                <ul style="list-style-type: circle;">
                    <li><span>Bên A đồng ý cho bên B thuê một phòng trọ thuộc địa chỉ: 56 Nam Pháp 1 Ngô Quyền,Hải Phòng</span></li>
                    <li>
                        <span>Thời hạn thuê phòng trọ là kể từ ngày
                            <?php echo getDateFormat($contractDetail['ngayvao'], 'd-m-Y'); ?>
                            đến ngày
                            <?php echo getDateFormat($contractDetail['ngayra'], 'd-m-Y'); ?>
                        </span>
                    </li>

                    <li><span>Giá tiền thuê phòng trọ là <?php echo number_format($roomDetail['giathue'], 0, ',', '.'); ?>đ</span></li>
                    <li><span>Tiền thuê phòng trọ bên B thanh toán cho bên A từ ngày 1 dương lịch hàng tháng.</span></li>
                    <li><span>Bên B đặt tiền cọc trước <?php echo number_format($contractDetail['sotiencoc'], 0, ',', '.'); ?>đ cho bên A. Tiền cọc sẽ được trả khi hết hạn hợp đồng.</span></li>
                    <li><span>Trong trường hợp bên B ngưng hợp đồng trước thời hạn thì phải chịu mất tiền cọc.</span></li>
                    <li><span>Bên A ngưng hợp đồng (lấy lại phòng trọ) trước thời hạn thì bồi thường gấp đôi số tiền bên B đã cọc.</span></li>
                </ul>
                <p><strong>Trách nhiệm bên A:</strong></p>
                <ul style="list-style-type: circle;">
                    <li><span>Giao phòng trọ, trang thiết bị trong phòng trọ cho bên B đúng ngày ký hợp đồng.</span></li>
                    <li><span>Hướng dẫn bên B chấp hành đúng các quy định của địa phương, hoàn tất mọi thủ tục giấy tờ đăng ký tạm trú cho bên B.</span></li>
                    <li>
                        <span>Cung cấp các dịch vụ theo yêu cầu bao gồm:
                            <strong>
                                <?php
                                // Tách các giá trị tendichvu, giadichvu, donvitinh thành mảng
                                $tendichvuList = explode(',', $contractDetail['tendichvu']);
                                $giadichvuList = explode(',', $contractDetail['giadichvu']);
                                $donvitinhList = explode(',', $contractDetail['donvitinh']);

                                // Duyệt và hiển thị theo định dạng "Tên dịch vụ - Giá: X VND/đơn vị"
                                $servicesDisplay = [];
                                for ($i = 0; $i < count($tendichvuList); $i++) {
                                    // Kiểm tra nếu giá trị giadichvu hợp lệ và là số
                                    if (isset($giadichvuList[$i]) && is_numeric($giadichvuList[$i]) && isset($donvitinhList[$i])) {
                                        $servicesDisplay[] = $tendichvuList[$i] . ' - Giá: ' . number_format((float)$giadichvuList[$i], 0, ',', '.') . ' VND/' . $donvitinhList[$i];
                                    }
                                }

                                // Hiển thị "Không có" nếu không có dịch vụ nào
                                if (empty($servicesDisplay)) {
                                    echo "Trống";
                                } else {
                                    // Hiển thị danh sách dịch vụ nếu có
                                    echo implode(', ', $servicesDisplay);
                                }
                                ?>
                            </strong>
                        </span>
                    </li>

                </ul>
                <p><strong>Trách nhiệm bên B:</strong></p>
                <p><strong>Điều 1:</strong> .</p>
                <ul style="list-style-type: circle;">
                    <li><span><?php echo $contractDetail['dieukhoan1']; ?>.</span></li>
                </ul>
                <p><strong>Điều 2:</strong> .</p>
                <ul style="list-style-type: circle;">
                    <li><span><?php echo $contractDetail['dieukhoan2']; ?>.</span></li>
                </ul>
                <p><strong>Điều 3:</strong> .</p>
                <ul style="list-style-type: circle;">
                    <li><span><?php echo $contractDetail['dieukhoan3']; ?>.</span></li>
                </ul>
            </div>
            <div style="font-size: 12pt; margin: 40px 0">
                <p style="text-align: right;"><i>........, Ngày...... Tháng...... năm 20.........</i></p>
                <div style="display: flex; margin-top: 1.5rem;">
                    <div style="flex: 0 0 auto;width: 50%;float: left;text-align: center">
                        <strong>BÊN A</strong><br>
                        <i>Ký và ghi rõ họ tên</i>
                        <div style="padding: 10px;height: 150px;width: 100%;text-align: center;overflow: hidden;">
                            <span></span>
                        </div>
                    </div>
                    <div style="flex: 0 0 auto;width: 50%;float: left;text-align: center">
                        <strong>BÊN B</strong><br>
                        <i>Ký và ghi rõ họ tên</i>
                        <div style="padding: 10px;height: 150px;width: 100%;text-align: center;overflow: hidden;">
                            <span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>


</html>