<?php

$body = getBody();

if(!empty($body['id'])) {
    $roomId = $body['id'];

    // Kiểm tra Id có tồn tại trong hệ thống hay không
    $roomDetail = getRows("SELECT id FROM room WHERE id=$roomId");
    $countTenant = getRows("SELECT id FROM tenant WHERE tenant.room_id = $roomId");

    if($countTenant > 0) {
        setFlashData('msg', 'Phòng này còn khách đang thuê nên không thể xoá');
        setFlashData('msg_type', 'err');
        redirect('admin/?module=room');

    }

    if($roomDetail > 0) {
        // Thực hiện xóa
       
            $deleteRoom = delete('room', "id=$roomId");
            if($deleteRoom) {
                setFlashData('msg', 'Xóa thông tin phòng trọ thành công');
                setFlashData('msg_type', 'suc');
            }else {
                setFlashData('msg', 'Lỗi hệ thống! Vui lòng thử lại sau');
                setFlashData('msg_type', 'err');
            }
    }else {
        setFlashData('msg', 'Phòng không tồn tại trên hệ thống');
        setFlashData('msg_type', 'err');
    }
}

redirect('admin/?module=room');