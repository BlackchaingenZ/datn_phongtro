<?php

$body = getBody();

if (!empty($body['id'])) {
    $equipmentId = $body['id'];

    // Kiểm tra thiết bị có tồn tại trong hệ thống hay không
    $equipmentDetail = getRows("SELECT id FROM equipment WHERE id=$equipmentId");
    $countUsage = getRows("SELECT id FROM equipment_room WHERE equipment_id = $equipmentId");

    if ($countUsage > 0) {
        setFlashData('msg', 'Thiết bị này đang được sử dụng trong phòng nên không thể xoá');
        setFlashData('msg_type', 'err');
        redirect('?module=equipment&action=listequipment');
    }

    if ($equipmentDetail > 0) {
        // Thực hiện xóa
        $deleteEquipment = delete('equipment', "id=$equipmentId");
        if ($deleteEquipment) {
            setFlashData('msg', 'Xóa thiết bị thành công');
            setFlashData('msg_type', 'suc');
        } else {
            setFlashData('msg', 'Lỗi hệ thống! Vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
        }
    } else {
        setFlashData('msg', 'Thiết bị không tồn tại trên hệ thống');
        setFlashData('msg_type', 'err');
    }
}

redirect('?module=equipment&action=listequipment');
