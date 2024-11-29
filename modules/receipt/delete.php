<?php

$body = getBody();

if(!empty($body['id'])) {
    $receiptId = $body['id'];

    // Kiểm tra Id có tồn tại trong hệ thống hay không
    $roomDetail = getRows("SELECT id FROM receipt WHERE id=$receiptId");

    if($roomDetail > 0) {
        // Thực hiện xóa
       
            $deleteRoom = delete('receipt', "id=$receiptId");
            if($deleteRoom) {
                setFlashData('msg', 'Xóa thông tin phiếu thu thành công');
                setFlashData('msg_type', 'suc');
            }else {
                setFlashData('msg', 'Lỗi hệ thống! Vui lòng thử lại sau');
                setFlashData('msg_type', 'err');
            }
    }else {
        setFlashData('msg', 'Phiếu thu không tồn tại trên hệ thống');
        setFlashData('msg_type', 'err');
    }
}

redirect('?module=receipt&action=receipts');