<?php

$body = getBody();

if (!empty($body['room_id'])) {
    $roomId = $body['room_id'];

    // Kiểm tra xem phòng có khu vực nào không
    $countAreaInRoom = getRows("SELECT COUNT(*) as area FROM area_room WHERE room_id = $roomId");

    if ($countAreaInRoom > 0) {
        // Nếu có khu vực trong phòng, tiến hành xóa toàn bộ 
        $deleteAreaInRoom = delete('area_room', "room_id = $roomId");
        if ($deleteAreaInRoom) {
            setFlashData('msg', 'Đã xóa khu vực của phòng');
            setFlashData('msg_type', 'suc');
        } else {
            setFlashData('msg', 'Lỗi hệ thống! Vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
        }
    } else {
        // Nếu phòng không có khu vực nào
        setFlashData('msg', 'Không có loại giá nào đang áp dụng trong phòng này');
        setFlashData('msg_type', 'err');
    }
}

redirect('?module=area&action=applyarea');
