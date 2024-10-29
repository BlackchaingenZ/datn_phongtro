<?php
if (!empty($body['id'])) {
    $contractId = $body['id'];

    // Kiểm tra Id có tồn tại trong hệ thống hay không
    $roomDetail = getRows("SELECT id FROM contract WHERE id=$contractId");

    if (!empty($roomDetail)) {
        // Kiểm tra xem phòng có người ở không
        $roomOccupied = getRows("SELECT room_id FROM contract WHERE id = $contractId AND end_date IS NULL");

        if (!empty($roomOccupied)) {
            setFlashData('msg', 'Phòng hiện đang có người ở, không thể xóa hợp đồng!');
            setFlashData('msg_type', 'err');
        } else {
            // Xóa dịch vụ liên kết với hợp đồng
            $deleteServices = delete('contract_services', "contract_id = $contractId");

            if ($deleteServices) {
                // Xóa hợp đồng
                $deleteContracts = delete('contract', "id = $contractId");

                if ($deleteContracts) {
                    // Xóa phòng liên kết với hợp đồng
                    $deleteRooms = delete('room', "id IN(SELECT room_id FROM contract WHERE id = $contractId)");

                    if ($deleteRooms) {
                        setFlashData('msg', 'Xóa hợp đồng thành công!');
                        setFlashData('msg_type', 'suc');
                    } else {
                        setFlashData('msg', 'Không thể xóa phòng liên kết với hợp đồng!');
                        setFlashData('msg_type', 'err');
                    }
                } else {
                    setFlashData('msg', 'Không thể xóa hợp đồng!');
                    setFlashData('msg_type', 'err');
                }
            } else {
                setFlashData('msg', 'Không thể xóa dịch vụ liên kết với hợp đồng!');
                setFlashData('msg_type', 'err');
            }
        }
    } else {
        setFlashData('msg', 'Hợp đồng không tồn tại trong hệ thống!');
        setFlashData('msg_type', 'err');
    }
}
