<?php

$body = getBody();

if (!empty($body['room_id'])) {
    $roomId = $body['room_id'];

    // Kiểm tra xem phòng có loại giá nào không
    $countCostInRoom = getRows("SELECT COUNT(*) as count FROM cost_room WHERE room_id = $roomId");

    if ($countCostInRoom> 0) {
        // Nếu có thiết bị trong phòng, tiến hành xóa toàn bộ thiết bị
        $deleteCostInRoom = delete('cost_room', "room_id = $roomId");
        if ($deleteCostInRoom) {
            setFlashData('msg', 'Đã xóa loại giá trong phòng');
            setFlashData('msg_type', 'suc');
        } else {
            setFlashData('msg', 'Lỗi hệ thống! Vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
        }
    } else {
        // Nếu phòng không có loại giá nào
        setFlashData('msg', 'Không có loại giá nào đang áp dụng trong phòng này');
        setFlashData('msg_type', 'err');
    }
}

redirect('?module=cost&action=applyroom');
?>
