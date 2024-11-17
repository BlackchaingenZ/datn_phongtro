<?php

$body = getBody();

if (!empty($body['id'])) {
    $contractId = $body['id'];

    // Kiểm tra Id có tồn tại trong hệ thống hay không
    $roomDetail = getRows("SELECT id FROM contract WHERE id=$contractId");

    // Kiểm tra xem hợp đồng có liên kết với contract_tenant không
    $checkTenantLink = getRows("SELECT id FROM contract_tenant WHERE contract_id_1 = $contractId");

    if (!empty($checkTenantLink)) {
        // Xóa bản ghi liên kết với contract_tenant
        $deleteTenants = delete('contract_tenant', "contract_id_1 = $contractId");

        if (!$deleteTenants) {
            setFlashData('msg', 'Không thể xóa liên kết với tenant!');
            setFlashData('msg_type', 'err');
            return; // Dừng lại nếu không xóa được
        }
    }

    // Kiểm tra xem hợp đồng có liên kết với receipt không
    $checkReceiptLink = getRows("SELECT id FROM receipt WHERE contract_id = $contractId");

    if (!empty($checkReceiptLink)) {
        // Xóa bản ghi liên kết với receipt
        $deleteReceipts = delete('receipt', "contract_id = $contractId");

        if (!$deleteReceipts) {
            setFlashData('msg', 'Không thể xóa liên kết với receipt!');
            setFlashData('msg_type', 'err');
            return; // Dừng lại nếu không xóa được
        }
    }

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


redirect('?module=contract');
