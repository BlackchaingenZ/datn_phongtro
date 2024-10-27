<?php
function addContract($dataInsert, $services_ids)
{
    try {
        // Kết nối cơ sở dữ liệu
        $pdo = new PDO('mysql:host=localhost;dbname=datn', 'root', '123456');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Bắt đầu giao dịch
        $pdo->beginTransaction();

        // Kiểm tra xem hợp đồng cho phòng đã tồn tại chưa
        $stmt = $pdo->prepare("SELECT id FROM contract WHERE room_id = :room_id AND tenant_id = :tenant_id");
        $stmt->execute(['room_id' => $dataInsert['room_id'], 'tenant_id' => $dataInsert['tenant_id']]);
        $existingContract = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingContract) {
            // Nếu hợp đồng đã tồn tại, lấy ID hợp đồng
            $contract_id = $existingContract['id'];
        } else {
            // Nếu không có hợp đồng, tạo hợp đồng mới
            $stmt = $pdo->prepare("INSERT INTO contract (room_id, tenant_id, tenant_id_2, tinhtrangcoc,soluongthanhvien, ngaylaphopdong, ngayvao, ngayra, create_at, ghichu )
             VALUES (:room_id, :tenant_id, :tenant_id_2, :tinhtrangcoc, :soluongthanhvien, :ngaylaphopdong, :ngayvao, :ngayra, :create_at, :ghichu )");
            $stmt->execute($dataInsert);
            $contract_id = $pdo->lastInsertId(); // Lấy ID của hợp đồng mới
        }

        // Kiểm tra và chuyển đổi $services_ids sang mảng nếu là chuỗi
        if (is_string($services_ids)) {
            $services_ids_array = explode(',', $services_ids);
        } elseif (is_array($services_ids)) {
            $services_ids_array = $services_ids; // Nếu đã là mảng, giữ nguyên
        } else {
            $services_ids_array = []; // Nếu không phải chuỗi hay mảng, khởi tạo mảng rỗng
        }

        // Gộp các dịch vụ thành một chuỗi
        $service_names = [];
        foreach ($services_ids_array as $service_id) {
            $stmt = $pdo->prepare("SELECT tendichvu FROM services WHERE id = :service_id");
            $stmt->execute(['service_id' => trim($service_id)]);
            $service = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($service) {
                $service_names[] = $service['tendichvu'];
            }

            // Thêm dịch vụ vào hợp đồng
            $serviceInsert = [
                'contract_id' => $contract_id,
                'services_id' => trim($service_id),
            ];
            $stmt = $pdo->prepare("INSERT INTO contract_services (contract_id, services_id) VALUES (:contract_id, :services_id)");
            $stmt->execute($serviceInsert);
        }

        // Gộp tên dịch vụ thành một chuỗi
        $all_service_names = implode(', ', $service_names);

        // Có thể cập nhật bảng hợp đồng với tên dịch vụ
        // $stmt = $pdo->prepare("UPDATE contract SET service_names = :service_names WHERE id = :contract_id");
        // $stmt->execute(['service_names' => $all_service_names, 'contract_id' => $contract_id]);

        // Cam kết giao dịch
        $pdo->commit();
        return ['success' => true, 'contract_id' => $contract_id, 'service_names' => $all_service_names];
    } catch (Exception $e) {
        // Nếu có lỗi, hủy giao dịch
        $pdo->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

?>
