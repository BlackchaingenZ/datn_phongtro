<?php

// function addHistory($cost_room)
// {

//     $roomName = firstRaw("SELECT tenphong FROM room 
//     INNER JOIN cost_room ON cost_room.room_id = room.id
//     INNER JOIN cost ON cost_room.cost_id = cost.id
//     WHERE room.id = {$cost_room['room_id']}")['tenphong'];

//     $costName = firstRaw("SELECT giathue FROM cost 
//     INNER JOIN cost_room ON cost.id = cost_room.cost_id 
//     INNER JOIN room ON cost_room.room_id = room.id
//     WHERE room.id = {$cost_room['room_id']}")['giathue'];

//     $data = [
//         'tenphong' => $roomName,
//         'giathue' => $costName,
//         'thoigianapdung' => $cost_room['thoigianapdung'],
//         'ngayketthuc' => date('Y-m-d')
//     ];
//     insert('history', $data);
// }

$body = getBody();

if (!empty($body['room_id'])) {
    $roomId = $body['room_id'];

    // $cost_room = firstRaw("SELECT * FROM cost_room WHERE room_id = $roomId");

    // Kiểm tra xem phòng có loại giá nào không
    $countCostInRoom = getRows("SELECT COUNT(*) as count FROM cost_room WHERE room_id = $roomId");

    if ($countCostInRoom > 0) {
        // addHistory($cost_room);

        // Xóa loại giá trong phòng
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
