<?php

$body = getBody();

if (!empty($body['id'])) {
    $serviceId = $body['id'];

    // Kiểm tra Id có tồn tại trong hệ thống hay không
    $serviceDetail = getRows("SELECT id FROM services WHERE id=$serviceId");

    if ($serviceDetail > 0) {
        // Kiểm tra xem dịch vụ có liên kết với hợp đồng nào không
        $contractServiceCheck = getRows("SELECT contract_id FROM contract_services WHERE services_id=$serviceId");

        if ($contractServiceCheck > 0) {
            // Xóa bản ghi trung gian trong contract_services
            $deleteContractService = delete('contract_services', "services_id=$serviceId");
            if (!$deleteContractService) {
                setFlashData('msg', 'Lỗi khi xóa bản ghi liên kết hợp đồng. Vui lòng thử lại sau');
                setFlashData('msg_type', 'err');
                redirect('?module=services');
                exit; // Dừng thực thi tiếp theo
            }
        }

        // Thực hiện xóa dịch vụ
        $deleteService = delete('services', "id=$serviceId");
        if ($deleteService) {
            setFlashData('msg', 'Xóa dịch vụ khách hàng thành công');
            setFlashData('msg_type', 'suc');
        } else {
            setFlashData('msg', 'Lỗi hệ thống! Vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
        }
    } else {
        setFlashData('msg', 'Dịch vụ không tồn tại trên hệ thống');
        setFlashData('msg_type', 'err');
    }
}

redirect('?module=services');
