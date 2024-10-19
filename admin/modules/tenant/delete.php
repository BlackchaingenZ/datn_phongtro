<?php

$body = getBody();

if(!empty($body['id'])) {
    $tenantId = $body['id'];

    // Kiểm tra Id có tồn tại trong hệ thống hay không
    $tenantDetail = getRows("SELECT id FROM tenant WHERE id=$tenantId");
    $coutTenantContract = getRows("SELECT id FROM contract WHERE contract.tenant_id = $tenantId");

    if($coutTenantContract > 0) {
        setFlashData('msg', 'Khách thuê này không thể xóa vì đang ký hợp đồng');
        setFlashData('msg_type', 'err');
        redirect('admin/?module=tenant');
    }

    if($tenantDetail > 0) {
        // Thực hiện xóa
       
            $deletetenant = delete('tenant', "id=$tenantId");
            if($deletetenant) {
                setFlashData('msg', 'Xóa thông tin khách thuê thành công');
                setFlashData('msg_type', 'suc');
            }else {
                setFlashData('msg', 'Lỗi hệ thống! Vui lòng thử lại sau');
                setFlashData('msg_type', 'err');
            }
    }else {
        setFlashData('msg', 'Khách thuê không tồn tại trên hệ thống');
        setFlashData('msg_type', 'err');
    }
}

redirect('admin/?module=tenant');