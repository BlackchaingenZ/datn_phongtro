<?php

$body = getBody();

if (!empty($body['id'])) {
    $roomId = $body['id'];

    // Kiểm tra xem phòng có hợp đồng đang liên kết không
    $checkContractInRoom = getRaw("SELECT id FROM contract WHERE room_id = $roomId");

    if ($checkContractInRoom) {
        // Nếu có hợp đồng liên kết, thông báo không thể xóa
        setFlashData('msg', 'Phòng đang có hợp đồng, không thể xóa!');
        setFlashData('msg_type', 'err');
    } else {
        // Kiểm tra xem phòng có tenant liên kết không
        $checkTenantInRoom = getRaw("SELECT room_id FROM tenant WHERE room_id = $roomId");

        if ($checkTenantInRoom) {
            setFlashData('msg', 'Phòng đang có người ở, không thể xóa!');
            setFlashData('msg_type', 'err');
        } else {
            // Xóa các thiết bị liên kết với phòng trọ
            $deleteEquipment = delete('equipment_room', "room_id = $roomId");

            if ($deleteEquipment) {
                // Xóa các khu vực liên kết với phòng trọ
                $deleteArea = delete('area_room', "room_id = $roomId");

                if ($deleteArea) {
                    // Xóa các giá thuê liên kết với phòng trọ
                    $deleteCost = delete('cost_room', "room_id = $roomId");

                    if ($deleteCost) {
                        // Xóa phòng trọ
                        $checkDeleteRoom = delete('room', "id = $roomId");

                        if ($checkDeleteRoom) {
                            setFlashData('msg', 'Xóa thông tin phòng trọ thành công!');
                            setFlashData('msg_type', 'suc');
                        } else {
                            setFlashData('msg', 'Không thể xóa phòng trọ!');
                            setFlashData('msg_type', 'err');
                        }
                    } else {
                        setFlashData('msg', 'Không thể xóa các giá thuê liên kết!');
                        setFlashData('msg_type', 'err');
                    }
                } else {
                    setFlashData('msg', 'Không thể xóa các khu vực liên kết!');
                    setFlashData('msg_type', 'err');
                }
            } else {
                setFlashData('msg', 'Không thể xóa các thiết bị liên kết!');
                setFlashData('msg_type', 'err');
            }
        }
    }
}
redirect('?module=room');
