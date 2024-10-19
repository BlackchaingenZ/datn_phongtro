<?php
// Include thư viện TCPDF
require_once('./TCPDF-main/tcpdf.php');
$body = getBody();
$id = $_GET['id'];

$contractDetail  = firstRaw("SELECT * FROM contract WHERE id=$id");
$tenantId = $contractDetail['tenant_id'];
$roomId = $contractDetail['room_id'];

$tenantDetail = firstRaw("SELECT * FROM tenant WHERE id = $tenantId");
$roomtDetail = firstRaw("SELECT * FROM room WHERE id = $roomId");

// Tạo một lớp con kế thừa từ TCPDF
class PDF extends TCPDF {
    // Override phương thức Header nếu cần
    public function Header() {
        // Thêm code Header ở đây nếu cần
    }
    
    // Override phương thức Footer nếu cần
    public function Footer() {
        // Thêm code Footer ở đây nếu cần
    }
}

// Khởi tạo đối tượng PDF
$pdf = new PDF();

// Thiết lập font Unicode
$pdf->SetFont('dejavusans', '', 14, '', true);

// Thêm một trang mới
$pdf->AddPage();

// Thiết lập thông tin tài liệu
$pdf->SetCreator('Creator');
$pdf->SetAuthor('Author');
$pdf->SetTitle('Hợp đồng thuê trọ');
$pdf->SetSubject('Subject');
$pdf->SetKeywords('Keywords');

// HTML content
$html = '
<div class="container">
        
           
        <p style="text-align: center">
            <span style="font-size: 12pt;"><b>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</b></span><br>
            <b style="font-size: 12pt;">Độc lập - Tự do - Hạnh phúc</b>
        </p>
        
         <p><strong style="text-transform:uppercase; text-align: center">HỢP ĐỒNG CHO THUÊ PHÒNG TRỌ THẢO NGUYÊN</strong></p>

        <div style="text-align: left;font-size: 12pt;">
         <p><strong>BÊN A : BÊN CHO THUÊ (PHÒNG TRỌ)</strong></p>
                    <table style="width: 100%">
                        <tbody><tr>
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
                                <p>Ngày cấp: 12/08/2022</p>
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
                        <tbody><tr>
                            <td colspan="2">
                            <p>Họ và tên: '.$tenantDetail['tenkhach'].' </p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"> 
                                <p>Năm sinh: '.$tenantDetail['ngaysinh'].'  </p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"> 
                                <p>CMND/CCCD: '.$tenantDetail['cmnd'].'  </p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>Ngày cấp: '.$tenantDetail['ngaycap'].'  </p>
                            </td>
                            <td>
                                <p>Nơi cấp: Công an thành phố Hải Phòng  </p>
                            </td>
                        </tr>
             
                        <tr>
                            <td colspan="2">
                                <p>Thường trú: '.$tenantDetail['diachi'].' </p>
                            </td>
                        </tr>
                    </tbody></table>
        </div>
        
         <p style="font-size: 12px">Hai bên cùng thỏa thuận và đồng ý với nội dung sau :</p>
       
        <div style="text-align: left;font-size: 12pt;">
          <p><strong>Điều 1:</strong></p>
                    <ul style="list-style-type: circle;">
                        <li><span>Bên A đồng ý cho bên B thuê một phòng trọ thuộc địa chỉ: 56 Nam Pháp 1 Ngô Quyền,Hải Phòng </span></li>
                        <li><span>Thời hạn thuê phòng trọ là kể từ ngày '.$contractDetail['ngayvao'].' đến '.$contractDetail['ngayra'].'  </span></li>
                    </ul>
                    <p><strong>Điều 2:</strong></p>
                    <ul style="list-style-type: circle;">
                        <li><span>Giá tiền thuê phòng trọ là ' . number_format($roomtDetail['giathue'], 0, ',', '.') . ' đ (Bằng chữ: Một triệu năm trăm ngàn đồng)</span></li>
                        <li><span>Tiền thuê phòng trọ bên B thanh toán cho bên A từ ngày 30  dương lịch hàng tháng.</span></li>
                        <li><span>Bên B đặt tiền thế chân trước ' . number_format($roomtDetail['tiencoc'], 0, ',', '.') . ' đ (Bằng chữ : Bảy trăm ngàn đồng) cho bên A. Tiền thế chân sẽ được trả
                        </span></li><li><span>Bên B ngưng hợp đồng trước thời hạn thì phải chịu mất tiền thế chân.</span></li>
                        <li><span>Bên A ngưng hợp đồng (lấy lại phòng trọ) trước thời hạn thì bồi thường gấp đôi số tiền bên B đã thế chân.</span></li>
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
        <p style="text-align: right;"><i>........, Ngày...... Tháng...... năm 20.........</i></p>
';

// Ghi nội dung HTML vào PDF
$pdf->writeHTML($html, true, false, true, false, '');

$tenphong = $roomtDetail['tenphong'];

// Đóng và xuất PDF ra trình duyệt để tải xuống
$pdf->Output('Hợp đồng '.$tenphong . '.pdf', 'I');

