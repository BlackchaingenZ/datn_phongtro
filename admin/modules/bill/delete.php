<?php

$body = getBody();

if(!empty($body['id'])) {
    $billId = $body['id'];

    // Kiểm tra Id có tồn tại trong hệ thống hay không
    $billDetail = getRows("SELECT id FROM bill WHERE id=$billId");

    if($billDetail > 0) {
        // Thực hiện xóa
       
            $deleteRoom = delete('bill', "id=$billId");
            if($deleteRoom) {
                setFlashData('msg', 'Xóa dữ liệu hóa đơn thành công');
                setFlashData('msg_type', 'suc');
            }else {
                setFlashData('msg', 'Lỗi hệ thống! Vui lòng thử lại sau');
                setFlashData('msg_type', 'err');
            }
    }else {
        setFlashData('msg', 'Hóa đơn không tồn tại trên hệ thống');
        setFlashData('msg_type', 'err');
    }
}

redirect('admin/?module=bill');