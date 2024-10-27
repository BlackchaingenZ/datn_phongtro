<?php

$body = getBody();

if(!empty($body['id'])) {
    $contractId = $body['id'];

    // Kiểm tra Id có tồn tại trong hệ thống hay không
    $roomDetail = getRows("SELECT id FROM contract WHERE id=$contractId");

    if($roomDetail > 0) {
        // Thực hiện xóa
       
            $deleteRoom = delete('contract', "id=$contractId");
            if($deleteRoom) {
                setFlashData('msg', 'Xóa thông tin hợp đồng thành công');
                setFlashData('msg_type', 'suc');
            }else {
                setFlashData('msg', 'Lỗi hệ thống! Vui lòng thử lại sau');
                setFlashData('msg_type', 'err');
            }
    }else {
        setFlashData('msg', 'Hợp đồng không tồn tại trên hệ thống');
        setFlashData('msg_type', 'err');
    }
}

redirect('admin/?module=contract');