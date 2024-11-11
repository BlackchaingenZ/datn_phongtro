<?php
// Include TCPDF library
require_once('./TCPDF-main/tcpdf.php');
$id = $_GET['id'];

// Get contract details
$contractDetail = firstRaw("SELECT * FROM contract WHERE id = $id");

// Get tenant details associated with the contract
$tenantDetails = firstRaw("SELECT 
GROUP_CONCAT(DISTINCT CONCAT(tenant.tenkhach, ' (ID: ', tenant.id, ')') ORDER BY tenant.tenkhach ASC SEPARATOR '\n') AS danh_sach_ten_khach, 
    GROUP_CONCAT(tenant.ngaysinh ORDER BY tenant.tenkhach ASC SEPARATOR '\n') AS danh_sach_ngay_sinh,
    GROUP_CONCAT(tenant.cmnd ORDER BY tenant.tenkhach ASC SEPARATOR '\n') AS danh_sach_cmnd,
    GROUP_CONCAT(tenant.ngaycap ORDER BY tenant.tenkhach ASC SEPARATOR '\n') AS danh_sach_ngay_cap,
    GROUP_CONCAT(tenant.diachi ORDER BY tenant.tenkhach ASC SEPARATOR '\n') AS danh_sach_dia_chi
FROM contract_tenant 
INNER JOIN tenant ON contract_tenant.tenant_id_1 = tenant.id 
WHERE contract_tenant.contract_id_1 = $id");

$tenantDetail = firstRaw("SELECT tenant_id_1 FROM contract_tenant WHERE contract_id_1 = $id");
$servicesDetail = firstRaw("SELECT services_id FROM contract_services WHERE services_id = $id");

// Get tenant information by tenant_id_1
$tenantId = $tenantDetail['tenant_id_1'];
$tenantDetail = firstRaw("SELECT * FROM tenant WHERE id = $tenantId");

// Get room details and rental price
$roomId = $contractDetail['room_id'];
$roomDetail = firstRaw("SELECT room.*, cost.giathue 
                        FROM room 
                        LEFT JOIN cost_room ON room.id = cost_room.room_id 
                        LEFT JOIN cost ON cost_room.cost_id = cost.id 
                        WHERE room.id = $roomId");

// Rental price
$price = $roomDetail['giathue'];
$contractDetail = firstRaw("SELECT sotiencoc, ngayvao, ngayra, dieukhoan1, dieukhoan2, dieukhoan3,
    GROUP_CONCAT(DISTINCT services.tendichvu ORDER BY services.tendichvu ASC SEPARATOR ', ') AS tendichvu 
    FROM contract 
    LEFT JOIN contract_services ON contract.id = contract_services.contract_id 
    LEFT JOIN services ON contract_services.services_id = services.id 
    WHERE contract.id = $id
    GROUP BY contract.id");

// Create a PDF class extending TCPDF
class PDF extends TCPDF
{
    public function Header()
    {
        // Add header code if needed
    }

    public function Footer()
    {
        // Add footer code if needed
    }
}

// Initialize PDF object
$pdf = new PDF();

// Set Unicode font
$pdf->SetFont('dejavusans', '', 14, '', true);

// Add a new page
$pdf->AddPage();

// Set document information
$pdf->SetCreator('Creator');
$pdf->SetAuthor('Author');
$pdf->SetTitle('Hợp đồng thuê trọ');
$pdf->SetSubject('Subject');
$pdf->SetKeywords('Keywords');

$html = '
<div class="container">
    <div style="padding: 2cm 1.5cm;">
        <div style="text-align: center">
            <p>
                <span style="font-size: 12pt;"><b>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</b></span><br>
                <b style="font-size: 12pt; text-decoration: underline">Độc lập - Tự do - Hạnh phúc</b>
            </p>
        </div>
        
        <div style="text-align: center; margin: 30px 0; font-size: 12pt;">
            <p><strong style="text-transform: uppercase;">HỢP ĐỒNG CHO THUÊ PHÒNG TRỌ THẢO NGUYÊN</strong></p>
        </div>
        
        <div style="text-align: left; font-size: 12pt;">
            <p><strong>BÊN A: BÊN CHO THUÊ (PHÒNG TRỌ)</strong></p>
            <table style="width: 100%">
                <tr><td><strong>Họ và tên: Nguyễn Minh Thảo</strong></td></tr>
                <tr><td>Năm sinh: 23/9/1972</td></tr>
                <tr><td>CMND/CCCD: 012020100322</td></tr>
                <tr><td>Ngày cấp: 27/08/2023 - Nơi cấp: Công an thành phố Hải Phòng</td></tr>
                <tr><td>Thường trú: 56 Nam Pháp 1 Ngô Quyền, Hải Phòng</td></tr>
            </table>
        </div>
        
        <div style="text-align: left; font-size: 12pt;">
            <p><strong>BÊN B: BÊN THUÊ (PHÒNG TRỌ)</strong></p>
            <table style="width: 100%"><tbody>';

$tenKhachList = explode("\n", $tenantDetails['danh_sach_ten_khach']);
$ngaySinhList = explode("\n", $tenantDetails['danh_sach_ngay_sinh']);
$cmndList = explode("\n", $tenantDetails['danh_sach_cmnd']);
$ngayCapList = explode("\n", $tenantDetails['danh_sach_ngay_cap']);
$diaChiList = explode("\n", $tenantDetails['danh_sach_dia_chi']);

foreach ($tenKhachList as $index => $tenKhach) {
    // Tách tên khách hàng từ chuỗi có định dạng "Tên khách hàng (ID: X)"
    $tenKhach = explode(" (ID:", $tenKhach)[0];  // Lấy phần tên trước " (ID:"
    $html .= "
        <tr><td><strong>Họ và tên: $tenKhach</strong></td></tr>
        <tr>
            <td>
                <p>Ngày sinh: " . (isset($ngaySinhList[$index]) && $ngaySinhList[$index] != '0000-00-00'
        ? getDateFormat($ngaySinhList[$index], 'd-m-Y')
        : 'Không xác định') . "</p>
            </td>
        </tr>
        <tr><td>CMND/CCCD: {$cmndList[$index]}</td></tr>
        <tr><td>Ngày cấp: " . (isset($ngayCapList[$index]) && $ngayCapList[$index] != '0000-00-00'
        ? getDateFormat($ngayCapList[$index], 'd-m-Y')
        : 'Không xác định') . " - Nơi cấp: Công an thành phố Hải Phòng</td></tr>
        <tr><td>Thường trú: {$diaChiList[$index]}</td></tr>";
}



$html .= '</tbody></table></div>';

$html .= '
<div style="text-align: left; font-size: 12pt;">
    <p>Hai bên cùng thỏa thuận và đồng ý với nội dung sau:</p>
    <ul style="list-style-type: circle;">
        <li>Bên A đồng ý cho bên B thuê một phòng trọ thuộc địa chỉ: 56 Nam Pháp 1 Ngô Quyền, Hải Phòng</li>
        <li>Thời hạn thuê phòng trọ là kể từ ngày ' . getDateFormat($contractDetail['ngayvao'], 'd-m-Y') . ' đến ngày ' . getDateFormat($contractDetail['ngayra'], 'd-m-Y') . '</li>
        <li>Giá tiền thuê phòng trọ là ' . number_format($roomDetail['giathue'], 0, ',', '.') . 'đ</li>
        <li>Tiền thuê phòng trọ bên B thanh toán cho bên A từ ngày 1 dương lịch hàng tháng.</li>
        <li>Bên B đặt tiền cọc trước ' . number_format($contractDetail['sotiencoc'], 0, ',', '.') . 'đ cho bên A. Tiền cọc sẽ được trả khi hết hạn hợp đồng.</li>
        <li>Trong trường hợp bên B ngưng hợp đồng trước thời hạn thì phải chịu mất tiền cọc.</li>
        <li>Bên A ngưng hợp đồng (lấy lại phòng trọ) trước thời hạn thì bồi thường gấp đôi số tiền bên B đã cọc.</li>
    </ul>
    
    <p><strong>Trách nhiệm bên A:</strong></p>
    <ul style="list-style-type: circle;">
        <li>Giao phòng trọ, trang thiết bị trong phòng trọ cho bên B đúng ngày ký hợp đồng.</li>
        <li>Hướng dẫn bên B chấp hành đúng các quy định của địa phương, hoàn tất mọi thủ tục giấy tờ đăng ký tạm trú cho bên B.</li>
        <li>Cung cấp các dịch vụ theo yêu cầu bao gồm: <strong>' . $contractDetail['tendichvu'] . '</strong>.</li>
    </ul>
    
    <p><strong>Trách nhiệm của bên B:</strong></p>
    <ul style="list-style-type: circle;">
        <li><strong>Điều 1: </strong>' . htmlspecialchars($contractDetail['dieukhoan1'], ENT_QUOTES, 'UTF-8') . '</li>
        <li><strong>Điều 2: </strong>' . htmlspecialchars($contractDetail['dieukhoan2'], ENT_QUOTES, 'UTF-8') . '</li>
        <li><strong>Điều 3: </strong>' . htmlspecialchars($contractDetail['dieukhoan3'], ENT_QUOTES, 'UTF-8') . '</li>
    </ul>
</div>

<div style="font-size: 12pt; margin: 40px 0;">
    <p style="text-align: right;"><i>........, Ngày...... Tháng...... năm 20.........</i></p>
    <table style="width: 100%; margin-top: 1.5rem;">
        <tr>
            <td style="width: 50%; text-align: center;">
                <strong>BÊN A</strong><br><i>Ký và ghi rõ họ tên</i>
            </td>
            <td style="width: 50%; text-align: center;">
                <strong>BÊN B</strong><br><i>Ký và ghi rõ họ tên</i>
            </td>
        </tr>
    </table>
</div>
</div>';


// Responsibilities section (example)
$html .= '<div><ul style="list-style-type: circle;">';

// Example responsibilities
$html .= '</ul></div></div>';

// Write HTML content to PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Output the PDF
$tenphong = $roomDetail['tenphong'];
$pdf->Output("Hợp đồng $tenphong.pdf", 'I');
