<?php

$body = getBody();

if (!empty($body['room_id'])) {
    $roomId = $body['room_id'];

    // Kiểm tra xem phòng có thiết bị nào không
    $countEquipmentInRoom = getRows("SELECT COUNT(*) as count FROM equipment_room WHERE room_id = $roomId");

    if ($countEquipmentInRoom> 0) {
        // Nếu có thiết bị trong phòng, tiến hành xóa toàn bộ thiết bị
        $deleteEquipmentInRoom = delete('equipment_room', "room_id = $roomId");
        if ($deleteEquipmentInRoom) {
            setFlashData('msg', 'Đã xóa toàn bộ thiết bị trong phòng');
            setFlashData('msg_type', 'suc');
        } else {
            setFlashData('msg', 'Lỗi hệ thống! Vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
        }
    } else {
        // Nếu phòng không có thiết bị nào
        setFlashData('msg', 'Phòng này không có thiết bị nào');
        setFlashData('msg_type', 'err');
    }
}

redirect('?module=equipment&action=listdistribute');
?>
