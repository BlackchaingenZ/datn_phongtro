<?php

$body = getBody();

if(!empty($body['id'])) {
    $paymentId = $body['id'];

    // Kiểm tra Id có tồn tại trong hệ thống hay không
    $paymentDetail = getRows("SELECT id FROM payment WHERE id=$paymentId");

    if($paymentDetail > 0) {
        // Thực hiện xóa
       
            $deletePayment = delete('payment', "id=$paymentId");
            if($deletePayment) {
                setFlashData('msg', 'Xóa thông tin phiếu chi thành công');
                setFlashData('msg_type', 'suc');
            }else {
                setFlashData('msg', 'Lỗi hệ thống! Vui lòng thử lại sau');
                setFlashData('msg_type', 'err');
            }
    }else {
        setFlashData('msg', 'Phiếu chi không tồn tại trên hệ thống');
        setFlashData('msg_type', 'err');
    }
}

redirect('?module=payment');