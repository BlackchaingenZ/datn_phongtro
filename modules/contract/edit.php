<?php

if (!defined('_INCODE'))
    die('Access denied...');


$data = [
    'pageTitle' => 'Cập nhật thông tin hợp đồng'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$allRoom = getRaw("SELECT id, tenphong, soluong FROM room ORDER BY tenphong");
$allTenant = getRaw("
    SELECT tenant.id, tenant.tenkhach, room.tenphong, contract.id AS contract_id FROM tenant 
    INNER JOIN room ON room.id = tenant.room_id 
    LEFT JOIN contract ON contract.tenant_id = tenant.id OR contract.tenant_id_2 = tenant.id 
    ORDER BY room.tenphong
");
$allServices = getRaw("SELECT * FROM services ORDER BY tendichvu ASC");
$allRoomId = getRaw("SELECT room_id FROM contract");
// Xử lý hiện dữ liệu cũ của người dùng
$body = getBody();
$id = $_GET['id'];

if (!empty($body['id'])) {
    $contractId = $body['id'];
    $contractDetail  = firstRaw("SELECT * FROM contract WHERE id=$contractId");
    if (!empty($contractDetail)) {
        // Gán giá trị contractDetail vào setFalsh
        setFlashData('contractDetail', $contractDetail);
    } else {
        redirect('?module=contract');
    }
    $contractServices = getRaw("SELECT services_id FROM contract_services WHERE contract_id = $contractId");
    $selectedServices = array_column($contractServices, 'services_id');
}
// Gán giá trị từ contractDetail vào các biến nếu có
if (!empty($contractDetail)) {
    $tinhtrangcoc = $contractDetail['tinhtrangcoc'];
    $soluongthanhvien = $contractDetail['soluongthanhvien'];
    $tenant_id = $contractDetail['tenant_id'];
    setFlashData('contractDetail', $contractDetail);
} else {
    // Nếu không có contractDetail, lấy dữ liệu cũ từ FlashData
    $contractDetail = getFlashData('old');
}

// Xử lý sửa người dùng
if (isPost()) {
    // Lấy tất cả dữ liệu trong form
    $body = getBody();
    $errors = [];  // Mảng lưu trữ các lỗi

    // Validate các trường bắt buộc như trước
    if (empty(trim($body['room_id']))) {
        $errors['room_id']['required'] = '** Bạn chưa chọn phòng lập hợp đồng!';
    }

    if (empty(trim($body['tenant_id'])) && empty(trim($body['tenant_id_2']))) {
        $errors['tenant_id']['required'] = '** Hãy chọn ít nhất 1 người thuê!';
    }

    if (empty(trim($body['ngaylaphopdong']))) {
        $errors['ngaylaphopdong']['required'] = '** Bạn chưa nhập ngày lập hợp đồng!';
    }

    if (empty(trim($body['tinhtrangcoc']))) {
        $errors['tinhtrangcoc']['required'] = '** Bạn chưa chọn tình trạng cọc!';
    }

    if (empty(trim($body['soluongthanhvien']))) {
        $errors['soluongthanhvien']['required'] = '** Bạn chưa nhập số lượng thành viên!';
    } elseif (!is_numeric($body['soluongthanhvien']) || intval($body['soluongthanhvien']) <= 0) {
        $errors['soluongthanhvien']['invalid'] = '** Số lượng thành viên phải là một số nguyên dương!';
    }

    // Kiểm tra dịch vụ
    // Xử lý dịch vụ
    $serviceIds = !empty($body['tendichvu']) ? array_map('trim', $body['tendichvu']) : [];

    if (empty($errors)) {
        delete('contract_services', "contract_id = $id");
        foreach ($serviceIds as $serviceId) {
            insert('contract_services', ['contract_id' => $id, 'services_id' => $serviceId]);
        }
    }

    // Kiểm tra mảng error
    if (empty($errors)) {
        // Không có lỗi nào, chuẩn bị dữ liệu để cập nhật
        $dataUpdate = [];

        // Chỉ cập nhật các trường có thay đổi, nếu không thì giữ nguyên
        if ($body['room_id'] !== $contractDetail['room_id']) {
            $dataUpdate['room_id'] = $body['room_id'];
        }
        if ($body['tenant_id'] !== $contractDetail['tenant_id']) {
            $dataUpdate['tenant_id'] = $body['tenant_id'];
        }
        if (empty(trim($body['tenant_id_2'])) !== empty(trim($contractDetail['tenant_id_2']))) {
            $dataUpdate['tenant_id_2'] = empty(trim($body['tenant_id_2'])) ? null : $body['tenant_id_2'];
        }
        if ($body['tinhtrangcoc'] !== $contractDetail['tinhtrangcoc']) {
            $dataUpdate['tinhtrangcoc'] = $body['tinhtrangcoc'];
        }
        if ($body['ngaylaphopdong'] !== $contractDetail['ngaylaphopdong']) {
            $dataUpdate['ngaylaphopdong'] = $body['ngaylaphopdong'];
        }
        if ($body['ngayvao'] !== $contractDetail['ngayvao']) {
            $dataUpdate['ngayvao'] = $body['ngayvao'];
        }
        if ($body['ngayra'] !== $contractDetail['ngayra']) {
            $dataUpdate['ngayra'] = $body['ngayra'];
        }
        if ($body['ghichu'] !== $contractDetail['ghichu']) {
            $dataUpdate['ghichu'] = $body['ghichu'];
        }
        if ($body['soluongthanhvien'] !== $contractDetail['soluongthanhvien']) {
            $dataUpdate['soluongthanhvien'] = intval($body['soluongthanhvien']);
        }

        // Nếu không có thay đổi nào, không cần gọi update
        if (!empty($dataUpdate)) {
            $condition = "id=$id";
            $updateStatus = update('contract', $dataUpdate, $condition);
            if ($updateStatus) {
                setFlashData('msg', 'Cập nhật thông tin hợp đồng thành công');
                setFlashData('msg_type', 'suc');
                redirect('?module=contract');
            } else {
                setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
                setFlashData('msg_type', 'err');
            }
        } else {
            // Nếu không có thay đổi nào, thông báo không có thay đổi
            setFlashData('msg', 'Không có thay đổi nào để cập nhật.');
            setFlashData('msg_type', 'info');
        }
    } else {
        // Có lỗi xảy ra
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);  // Giữ lại các trường dữ liệu hợp lệ khi nhập vào
    }

    redirect('?module=contract&action=edit&id=' . $contractId);
}


// Nếu có dữ liệu cũ, gán cho các biến
if (!empty($contractDetail)) {
    $room_id = !empty($contractDetail['room_id']) ? $contractDetail['room_id'] : '';
    $tenant_id = !empty($contractDetail['tenant_id']) ? $contractDetail['tenant_id'] : '';
    $tenant_id_2 = !empty($contractDetail['tenant_id_2']) ? $contractDetail['tenant_id_2'] : '';
    $ngaylaphopdong = !empty($contractDetail['ngaylaphopdong']) ? $contractDetail['ngaylaphopdong'] : '';
    $tinhtrangcoc = !empty($contractDetail['tinhtrangcoc']) ? $contractDetail['tinhtrangcoc'] : '';
    $soluongthanhvien = !empty($contractDetail['soluongthanhvien']) ? $contractDetail['soluongthanhvien'] : '';
    // Lấy các giá trị khác nếu cần thiết...
}

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

if (!empty($contractDetail) && empty($old)) {
    $old = $contractDetail;
}
?>
<?php
layout('navbar', 'admin', $data);
?>

<div class="container">
    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

    <div class="box-content">
        <form action="" method="post" class="row">
            <div class="col-5">

                <div class="form-group">
                    <label for="">Phòng <span style="color: red">*</span></label>
                    <select name="room_id" id="" class="form-select">
                        <option value="">Chọn phòng</option>
                        <?php
                        if (!empty($allRoom)) {
                            foreach ($allRoom as $item) {
                        ?>
                                <option value="<?php echo $item['id']; ?>"
                                    <?php echo (isset($room_id) && $room_id == $item['id']) ? 'selected' : ''; ?>>
                                    <?php echo $item['tenphong']; ?> (<?php echo $item['soluong']; ?> người)
                                </option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                    <?php echo form_error('room_id', $errors, '<span class="error">', '</span>'); ?>
                </div>



                <div class="form-group">
                    <label for="">Người thuê 1 <span style="color: red">*</span></label>
                    <select name="tenant_id" id="" class="form-select">
                        <option value="">Chọn người thuê</option>
                        <?php
                        if (!empty($allTenant)) {
                            foreach ($allTenant as $item) {
                        ?>
                                <option value="<?php echo $item['id']; ?>"
                                    <?php echo (!empty($tenant_id) && $tenant_id == $item['id']) ? 'selected' : ''; ?>>
                                    <?php echo $item['tenkhach']; ?> - <?php echo $item['tenphong']; ?>
                                </option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                    <?php echo form_error('tenant_id', $errors, '<span class="error">', '</span>'); ?>
                </div>


                <div class="form-group">
                    <label for="">Người thuê 2 <span style="color: red">*</span></label>
                    <select name="tenant_id_2" id="" class="form-select">
                        <option value="">Trống</option>
                        <?php
                        if (!empty($allTenant)) {
                            foreach ($allTenant as $item) {
                        ?>
                                <option value="<?php echo $item['id']; ?>"
                                    <?php echo (!empty($tenant_id_2) && $tenant_id_2 == $item['id']) ? 'selected' : ''; ?>>
                                    <?php echo $item['tenkhach']; ?> - Phòng: <?php echo $item['tenphong']; ?>
                                </option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                    <?php echo form_error('tenant_id_2', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Số lượng thành viên<span style="color: red">*</span></label>
                    <select name="soluongthanhvien" class="form-select">
                        <option value="">Chọn số lượng</option>
                        <option value="1" <?php echo (isset($soluongthanhvien) && $soluongthanhvien == 1) ? 'selected' : ''; ?>>1</option>
                        <option value="2" <?php echo (isset($soluongthanhvien) && $soluongthanhvien == 2) ? 'selected' : ''; ?>>2</option>
                    </select>
                    <?php echo form_error('soluongthanhvien', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Ngày lập hợp đồng <span style="color: red">*</span></label>
                    <input type="date" name="ngaylaphopdong" id="" class="form-control"
                        value="<?php echo old('ngaylaphopdong', $old); ?>">
                    <?php echo form_error('ngaylaphopdong', $errors, '<span class="error">', '</span>'); ?>
                </div>
            </div>

            <div class="col-5">
                <div class="form-group">
                    <label for="">Ngày vào ở <span style="color: red">*</span></label>
                    <input type="date" name="ngayvao" id="" class="form-control"
                        value="<?php echo old('ngayvao', $old); ?>">
                    <?php echo form_error('ngayvao', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Ngày hết hạn hợp đồng <span style="color: red">*</span></label>
                    <input type="date" name="ngayra" id="" class="form-control"
                        value="<?php echo old('ngayra', $old); ?>">
                    <?php echo form_error('ngayra', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Tình trạng cọc<span style="color: red">*</span></label>
                    <select name="tinhtrangcoc" class="form-select">
                        <option value="">Chọn trạng thái</option>
                        <option value="0" <?php echo (isset($tinhtrangcoc) && $tinhtrangcoc == 0) ? 'selected' : ''; ?>>Chưa thu tiền</option>
                        <option value="1" <?php echo (isset($tinhtrangcoc) && $tinhtrangcoc == 1) ? 'selected' : ''; ?>>Đã thu tiền</option>
                    </select>
                    <?php echo form_error('tinhtrangcoc', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <?php
                // Khởi tạo biến $tendichvuId nếu chưa được xác định
                $tendichvuId = isset($tendichvuId) ? $tendichvuId : [];
                ?>

                <div class="form-group">
                    <label for="">Dịch vụ</label><br>
                    <div class="checkbox-container">
                        <?php
                        if (!empty($allServices)) {
                            foreach ($allServices as $service) {
                        ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="tendichvu[]" value="<?php echo $service['id']; ?>"
                                        <?php echo in_array($service['id'], $selectedServices) ? 'checked' : ''; ?>>
                                    <?php echo $service['tendichvu']; ?><br>
                                </div>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </div>


                <div class="form-group">
                    <label for="">Ghi chú<span style="color: red">*</label>
                    <input type="text" placeholder="" name="ghichu" class="form-control" value="<?php echo old('ghichu', $old); ?>" style="width: 100%;height:100px">
                    <?php echo form_error('ghichu', $errors, '<span class="error">', '</span>'); ?>
                </div>

            </div>
            <div class="from-group">
                <div class="btn-row">
                    <a style="margin-right: 20px" href="<?php echo getLinkAdmin('contract') ?>"
                        class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại </a>
                    <button type="submit" class="btn btn-secondary"><i class="fa fa-edit"></i> Thêm hợp đồng</button>
                </div>
            </div>
    </div>
    </form>
</div>
</div>

<?php
layout('footer', 'admin');
