<?php
// Include thư viện TCPDF
require_once('./TCPDF-main/tcpdf.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Lấy chi tiết phiếu chi từ bảng payment
$paymentDetail = firstRaw("
    SELECT 
        payment.id, 
        payment.room_id, 
        room.tenphong AS tenphong, 
        payment.sotien, 
        payment.ghichu, 
        payment.ngaychi, 
        payment.phuongthuc, 
        payment.danhmucchi_id, 
        category_spend.tendanhmuc
    FROM 
        payment
    LEFT JOIN 
        category_spend 
    ON 
        payment.danhmucchi_id = category_spend.id
    LEFT JOIN 
        room 
    ON 
        payment.room_id = room.id
    LEFT JOIN 
        tenant 
    ON 
        room.id = tenant.room_id
    WHERE 
        payment.id = $id
");

if (!$paymentDetail) {
    die("Không tìm thấy phiếu chi!");
}

// Tạo một lớp con kế thừa từ TCPDF
class PDF extends TCPDF
{
    public function Header()
    {
        $this->SetFont('dejavusans', 'B', 12);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('dejavusans', 'I', 10);
        $this->Cell(0, 10, 'Trang ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

// Khởi tạo đối tượng PDF
$pdf = new PDF();
$pdf->SetMargins(10, 20, 10);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 12);

// HTML nội dung
$html = '
<div style="width: 100%; text-align: center; font-family: DejaVuSans; margin-bottom: 20px;">
    <img style="width: 100px;" src="./assets/img/logomain.png" alt="Logo">
    <h2 style="margin: 10px 0;">Phiếu Chi Phòng Trọ Thảo Nguyên</h2>
    <p>Địa chỉ: 56 - Nam Pháp, Ngô Quyền, Hải Phòng</p>
</div>
<p>Loại phiếu: <b style="color: red;">' . htmlspecialchars($paymentDetail['tendanhmuc'], ENT_QUOTES, 'UTF-8') . '</b></p>
<p>Tên phòng: <b>' . htmlspecialchars($paymentDetail['tenphong'], ENT_QUOTES, 'UTF-8') . '</b></p>
<table border="1" cellpadding="5" cellspacing="0" width="100%" style="margin-top: 20px;">
    <tr>
        <th style="width: 50px;">STT</th>
        <th style="width: 150px;">Thông tin</th>
        <th style="width: auto;">Chi tiết</th>
    </tr>
    <tr>
        <td>1</td>
        <td>Số tiền chi</td>
        <td>' . number_format($paymentDetail['sotien'], 0, ',', '.') . ' đ</td>
    </tr>
    <tr>
        <td>2</td>
        <td>Ghi chú</td>
        <td>' . htmlspecialchars($paymentDetail['ghichu'], ENT_QUOTES, 'UTF-8') . '</td>
    </tr>
    <tr>
        <td>3</td>
        <td>Ngày chi</td>
        <td>' . date('d-m-Y', strtotime($paymentDetail['ngaychi'])) . '</td>
    </tr>
    <tr>
        <td>4</td>
        <td>Phương thức thanh toán</td>
        <td>' . ($paymentDetail['phuongthuc'] == 1 ? 'Chuyển khoản' : 'Tiền mặt') . '</td>
    </tr>
</table>
    <div style="font-size: 12pt; margin: 40px 0">
    <p style="text-align: right;"><i>........, Ngày...... Tháng...... năm 20.........</i></p>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
        <!-- BÊN xác nhận -->
         <div>
            <strong>Xác nhận </strong><br>
            <i>Ký và ghi rõ họ tên</i>
            <div style="padding: 10px; height: 150px; overflow: hidden;">
                <span></span>
            </div>
        </div>
    </div>
</div>
';

// Ghi nội dung HTML vào PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Tên file PDF xuất ra
$tenphong = htmlspecialchars($paymentDetail['tenphong'], ENT_QUOTES, 'UTF-8');
$month = date('m');
$pdf->Output('PhieuChi_' . $tenphong . '_Thang' . $month . '.pdf', 'I');
