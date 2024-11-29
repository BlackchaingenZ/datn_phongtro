<?php

$body = getBody();

if (!empty($body['id'])) {
    $billId = $body['id'];

    // Kiểm tra hóa đơn có tồn tại trong hệ thống hay không
    $billDetail = getRows("SELECT id FROM bill WHERE id=$billId");

    if ($billDetail > 0) {
        // Kiểm tra xem hóa đơn có liên kết với bảng receipt không
        $receiptCount = getRows("SELECT COUNT(*) FROM receipt WHERE bill_id=$billId");

        if ($receiptCount > 0) {
            // Xóa các bản ghi liên quan trong bảng receipt
            $deleteReceipt = delete('receipt', "bill_id=$billId");

            if (!$deleteReceipt) {
                setFlashData('msg', 'Lỗi hệ thống! Không thể xóa bản ghi trong bảng receipt');
                setFlashData('msg_type', 'err');
                redirect('?module=bill&action=bills');
                exit;
            }
        }

        // Thực hiện xóa hóa đơn
        $deleteBill = delete('bill', "id=$billId");
        if ($deleteBill) {
            setFlashData('msg', 'Xóa dữ liệu hóa đơn thành công');
            setFlashData('msg_type', 'suc');
        } else {
            setFlashData('msg', 'Lỗi hệ thống! Vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
        }
    } else {
        setFlashData('msg', 'Hóa đơn không tồn tại trên hệ thống');
        setFlashData('msg_type', 'err');
    }
}

redirect('?module=bill&action=bills');
