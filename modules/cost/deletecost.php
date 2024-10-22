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
        // Xóa bản ghi trong bảng cost
        $deleteStatus = delete('cost', "id = $id"); // Giả định bạn có hàm delete để xóa dữ liệu trong bảng cost

        if ($deleteStatus) {
            setFlashData('msg', 'Xóa thông tin bảng giá thành công');
            setFlashData('msg_type', 'suc');
            redirect('?module=cost&action=costroom'); // Điều hướng đến danh sách danh mục chi phí sau khi xóa
        } else {
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
            redirect('?module=cost&action=costroom');
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
