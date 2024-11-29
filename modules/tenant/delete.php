<?php

$body = getBody();

if (!empty($body['id'])) {
    $tenantId = $body['id'];

    // Kiểm tra Id của khách thuê có tồn tại trong hệ thống hay không
    $tenantDetail = getRows("SELECT id FROM tenant WHERE id=$tenantId");

    // Kiểm tra xem khách thuê có liên kết với bất kỳ hợp đồng nào thông qua bảng contract_tenant
    $checkTenantLink = getRows("SELECT contract_id_1 FROM contract_tenant WHERE tenant_id_1 = $tenantId");

    if ($checkTenantLink > 0) {
        // Nếu tồn tại liên kết, không cho phép xóa và hiển thị thông báo lỗi
        setFlashData('msg', 'Khách thuê này không thể xóa vì đang ký hợp đồng');
        setFlashData('msg_type', 'err');
        redirect('?module=tenant');
    }

    // Nếu không có liên kết với hợp đồng, tiến hành xóa khách thuê
    if ($tenantDetail > 0) {
        $deletetenant = delete('tenant', "id=$tenantId");
        if ($deletetenant) {
            setFlashData('msg', 'Xóa thông tin khách thuê thành công');
            setFlashData('msg_type', 'suc');
        } else {
            setFlashData('msg', 'Lỗi hệ thống! Vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
        }
    } else {
        setFlashData('msg', 'Khách thuê không tồn tại trên hệ thống');
        setFlashData('msg_type', 'err');
    }
}

redirect('?module=tenant');
