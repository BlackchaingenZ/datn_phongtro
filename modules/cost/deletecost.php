<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Xóa toàn bộ thông tin bảng giá'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Xử lý xóa toàn bộ thông tin bảng giá
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Kiểm tra xem id có tồn tại trong bảng cost hay không
    $checkCost = getRow("SELECT id FROM cost WHERE id = $id");

    if ($checkCost) {
        // Kiểm tra xem loại giá này đã liên kết với phòng nào chưa
        $checkLinkedRoom = getRow("SELECT room_id FROM cost_room WHERE cost_id = $id");

        if ($checkLinkedRoom) {
            // Nếu loại giá này đã được liên kết với phòng
            setFlashData('msg', ' Không thể xoá vì loại giá này đang có phòng sử dụng');
            setFlashData('msg_type', 'err');
            redirect('?module=cost&action=costroom');
        } else {
            // Xóa bản ghi trong bảng cost nếu không liên kết với phòng nào
            $deleteStatus = delete('cost', "id = $id");

            if ($deleteStatus) {
                setFlashData('msg', 'Xóa thông tin bảng giá thành công');
                setFlashData('msg_type', 'suc');
                redirect('?module=cost&action=costroom');
            } else {
                setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
                setFlashData('msg_type', 'err');
                redirect('?module=cost&action=costroom');
            }
        }
    } else {
        // Không tìm thấy bản ghi cần xóa
        setFlashData('msg', 'Không tìm thấy thông tin bảng giá!');
        setFlashData('msg_type', 'err');
        redirect('?module=cost&action=costroom');
    }
} else {
    // Không có id được truyền vào
    setFlashData('msg', 'Liên kết không hợp lệ!');
    setFlashData('msg_type', 'err');
    redirect('?module=cost&action=costroom');
}

?>
