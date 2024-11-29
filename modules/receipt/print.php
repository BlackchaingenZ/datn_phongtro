<?php
// Include thư viện TCPDF
require_once('./TCPDF-main/tcpdf.php');
$id = $_GET['id'];

// Lấy chi tiết phiếu thu từ bảng receipt
$receiptDetail = firstRaw("
    SELECT 
        receipt.id, 
        receipt.room_id, 
        room.tenphong AS tenphong, 
        receipt.sotien, 
        receipt.ghichu, 
        receipt.ngaythu, 
        receipt.phuongthuc, 
        receipt.danhmucthu_id, 
        category_collect.tendanhmuc, 
        receipt.bill_id, 
        receipt.contract_id, 
        GROUP_CONCAT(tenant.tenkhach SEPARATOR ', ') AS tenkhach
    FROM 
        receipt
    LEFT JOIN 
        category_collect 
    ON 
        receipt.danhmucthu_id = category_collect.id
    LEFT JOIN 
        room 
    ON 
        receipt.room_id = room.id
    LEFT JOIN 
        tenant 
    ON 
        room.id = tenant.room_id
    WHERE 
        receipt.id = $id
");

// Tạo một lớp con kế thừa từ TCPDF
class PDF extends TCPDF
{
    // Override phương thức Header nếu cần
    public function Header()
    {
        $this->SetFont('dejavusans', 'B', 12);
        // $this->Cell(0, 10, 'Phiếu Thu Phòng Trọ Thảo Nguyên', 0, 1, 'C');
    }

    // Override phương thức Footer nếu cần
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('dejavusans', 'I', 10);
        $this->Cell(0, 10, 'Trang ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

// Khởi tạo đối tượng PDF
$pdf = new PDF();
$pdf->SetMargins(10, 20, 10); // Cài đặt lề
$pdf->SetAutoPageBreak(TRUE, 15);

// Thêm một trang mới
$pdf->AddPage();

// Thiết lập font Unicode
$pdf->SetFont('dejavusans', '', 12);

// Nội dung HTML cho PDF
$html = '
    <div style="text-align: center;">
        <img src="./path/to/logo.png" alt="Logo" style="width: 150px;"/>
        <h2>Phiếu Thu Phòng Trọ Thảo Nguyên</h2>
        <p>Địa chỉ: 56 - Nam Pháp, Ngô Quyền, Hải Phòng</p>
        <p>Loại phiếu: <b style="color: red;">' . htmlspecialchars($receiptDetail['tendanhmuc'], ENT_QUOTES) . '</b></p>
    </div>
    <table border="1" cellpadding="5" cellspacing="0" style="width: 100%; margin-top: 20px;">
        <tr>
        <th style="width: 50px;">STT</th>
        <th style="width: 150px;">Thông tin</th>
        <th style="width: auto;">Chi tiết</th>
        </tr>
        <tr>
            <td style="text-align: center;">1</td>
            <td>Số tiền thu</td>
            <td>' . number_format($receiptDetail['sotien'], 0, ',', '.') . ' đ</td>
        </tr>
        <tr>
            <td style="text-align: center;">2</td>
            <td>Ghi chú</td>
            <td>' . htmlspecialchars($receiptDetail['ghichu'], ENT_QUOTES) . '</td>
        </tr>
        <tr>
            <td style="text-align: center;">3</td>
            <td>Ngày thu</td>
            <td>' . htmlspecialchars(getDateFormat($receiptDetail['ngaythu'], 'd-m-Y'), ENT_QUOTES) . '</td>
        </tr>
        <tr>
            <td style="text-align: center;">4</td>
            <td>Phương thức thanh toán</td>
            <td>' . ($receiptDetail['phuongthuc'] == 1 ? 'Chuyển khoản' : 'Tiền mặt') . '</td>
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
$tenphong = htmlspecialchars($receiptDetail['tenphong'], ENT_QUOTES);
$month = date('m');
$pdf->Output('PhieuThu_' . $tenphong . '_Thang' . $month . '.pdf', 'I');
