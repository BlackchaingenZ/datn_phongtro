<?php
// Include thư viện TCPDF
require_once('./TCPDF-main/tcpdf.php');
$id = $_GET['id'];

// Truy vấn cơ sở dữ liệu
$billDetail = firstRaw("SELECT * FROM bill WHERE id=$id");
$date = firstRaw("SELECT MONTH(create_at) AS month, YEAR(create_at) AS year FROM bill WHERE id=$id");
$roomId = $billDetail['room_id'];

$roomDetail = firstRaw(" 
    SELECT r.*, c.giathue 
    FROM room r
    JOIN cost_room cr ON r.id = cr.room_id
    JOIN cost c ON cr.cost_id = c.id
    WHERE r.id = $roomId
");

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
$pdf->SetTitle('Hóa đơn tiền phòng');
$pdf->SetSubject('Subject');
$pdf->SetKeywords('Keywords');

// HTML content
$html = '
<body  style="padding: 0; display: flex; justify-content: center;font-family: DejaVu Sans, Tahoma, Geneva, Verdana, sans-serif;">
    <div class="bill-content" style="width: 60%; height: auto; background: #fff; text-align: center; line-height: 1.2;">
        <h2 style="font-size: 20px; margin: 10px 0;">Hóa đơn tiền thuê phòng</h2>
        <h3 style="margin-top: 10px; font-size: 12px;">Tháng ' . $date['month'] . '/' . $date['year'] . '</h3>
        <p style="font-size: 12px;">Địa chỉ: 56 - Nam Pháp 1, Ngô Quyền, Hải Phòng</p>
        <p style="font-size: 12px;">Mã hóa đơn: <b style="color: red; font-size: 16px">' . $billDetail['mahoadon'] . '</b></p>
        <p style="font-size: 12px; text-align: start">Đơn vị: <b>' . $roomDetail['tenphong'] . '</b></p>

        <table border="1" cellspacing="0" width="100%" cellpadding="10" style="text-align: start;">
            <tr>
                <td width="25%"><b>Khoản thu</b></td>
                <td width="50%"><b>Chi tiết</b></td>
                <td width="25%"><b>Thành tiền</b></td>
            </tr>
            <tr>
                <td style="font-size: 10px;"><b>Tiền phòng</b></td>
                <td style="font-size: 12px;">30 ngày x ' . number_format($roomDetail['giathue'], 0, ',', '.') . ' đ</td>
                <td style="font-size: 12px;"><b>' . number_format($roomDetail['giathue'], 0, ',', '.') . ' đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 10px;"><b>Tiền điện (KWh)</b></td>
                <td style="font-size: 12px;">(Số cũ: ' . $billDetail['sodiencu'] . ' - Số mới: ' . $billDetail['sodienmoi'] . ') x 4.000đ</td>
                <td style="font-size: 12px;"><b>' . number_format($billDetail['tiendien'], 0, ',', '.') . ' đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 10px;"><b>Tiền nước</b></td>
                <td style="font-size: 12px;">(Số cũ: ' . $billDetail['sonuoccu'] . ' - Số mới: ' . $billDetail['sonuocmoi'] . ') x 20.000đ</td>
                <td style="font-size: 12px;"><b>' . number_format($billDetail['tiennuoc'], 0, ',', '.') . ' đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 10px;"><b>Tiền rác (người)</b></td>
                <td style="font-size: 12px;">' . $billDetail['songuoi'] . ' người x 10.000đ</td>
                <td style="font-size: 12px;"><b>' . number_format($billDetail['tienrac'], 0, ',', '.') . ' đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 10px;"><b>Tiền Wifi</b></td>
                <td style="font-size: 12px;">' . $billDetail['chuky'] . ' tháng x 50.000đ</td>
                <td style="font-size: 12px;"><b>' . number_format($billDetail['tienmang'], 0, ',', '.') . ' đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 10px;"><b>Nợ cũ</b></td>
                <td style="font-size: 12px;"><b>' . number_format($billDetail['nocu'], 0, ',', '.') . ' đ</b></td>
                <td style="font-size: 12px;"><b>' . number_format($billDetail['nocu'], 0, ',', '.') . ' đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 10px;"><b>Tổng tiền</b></td>
                <td colspan="2" style="text-align: right; font-size: 12px; color: #dc3545;"><b>' . number_format($billDetail['tongtien'], 0, ',', '.') . ' đ</b></td>
            </tr>
            <tr>
                <td style="font-size: 10px;"><b>Thanh toán</b></td>
                <td colspan="2">
                    <div>
                        <img style="width: 80px; height: 80px;" src="https://jeju.com.vn/wp-content/uploads/2020/05/vnpay-qr-23-06-2020-2.jpg" alt="">
                        <p style="font-size: 10px;"><i><b>Nội dung thanh toán: </b></i><strong style="color: red">Mã hóa đơn</strong></p>                           
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
';

// Ghi nội dung HTML vào PDF
$pdf->writeHTML($html, true, false, true, false, '');

$tenphong = $roomDetail['tenphong'];
$month = $date['month'];
// Đóng và xuất PDF ra trình duyệt để tải xuống
$pdf->Output('Tháng' . $month . ' - ' . $tenphong . '.pdf', 'I');
?>
