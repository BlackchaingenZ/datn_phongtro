<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Xóa toàn bộ thông tin khu vực'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Xử lý xóa toàn bộ thông tin khu vực
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Kiểm tra xem id có tồn tại trong bảng area hay không
    $checkArea = getRow("SELECT id FROM area WHERE id = $id");

    if ($checkArea) {
        // Kiểm tra xem khu vực này có phòng nào liên kết hay không
        $checkLinkedRoom = getRow("SELECT room_id FROM area_room WHERE area_id = $id");

        if ($checkLinkedRoom) {
            // Nếu khu vực này đã được liên kết với phòng
            setFlashData('msg', 'Không thể xóa vì đang chứa phòng nào đó!');
            setFlashData('msg_type', 'err');
            redirect('?module=area&action=listarea');
        } else {
            // Xóa bản ghi trong bảng area nếu không liên kết với phòng nào
            $deleteStatus = delete('area', "id = $id");

            if ($deleteStatus) {
                setFlashData('msg', 'Xóa thông tin khu vực thành công');
                setFlashData('msg_type', 'suc');
                redirect('?module=area&action=listarea');
            } else {
                setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
                setFlashData('msg_type', 'err');
                redirect('?module=area&action=listarea');
            }
        }
    } else {
        // Không tìm thấy bản ghi cần xóa
        setFlashData('msg', 'Không tìm thấy thông tin khu vực!');
        setFlashData('msg_type', 'err');
        redirect('?module=area&action=applyarea');
    }
} else {
    // Không có id được truyền vào
    setFlashData('msg', 'Liên kết không hợp lệ!');
    setFlashData('msg_type', 'err');
    redirect('?module=area&action=listarea');
}

?>
