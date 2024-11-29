<?php

if (!defined('_INCODE'))
    die('Access denied...');


$data = [
    'pageTitle' => 'Thêm phiếu chi mới'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);
$allArea = getRaw("SELECT id, tenkhuvuc FROM area ORDER BY tenkhuvuc");
$allPayment = getRaw("SELECT * FROM category_spend");
$allRoom = getRaw("
    SELECT room.id, room.tenphong, room.soluong 
    FROM room 
    WHERE room.id IN (SELECT room_id FROM contract WHERE trangthaihopdong = 1)
    ORDER BY room.tenphong
");
$roomsByArea = [];
foreach ($allRoom as $room) {

    $areaIds = getRaw("SELECT area_id FROM area_room WHERE room_id = " . $room['id']);
    foreach ($areaIds as $area) {
        // Thêm thông tin số người vào mỗi phòng theo khu vực
        $roomsByArea[$area['area_id']][] = [
            'id' => $room['id'],
            'tenphong' => $room['tenphong'],
        ];
    }
}

// Xử lý thêm hợp đồng
if (isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi

    //Valide họ tên: Bắt buộc phải nhập, >=5 ký tự
    if (empty(trim($body['danhmucchi_id']))) {
        $errors['danhmucchi_id']['required'] = '** Bạn chưa chọn danh mục chi';
    }
    if (empty(trim($body['sotien']))) {
        $errors['sotien']['required'] = '** Bạn chưa điền số tiền';
    }
    if (empty(trim($body['ngaychi']))) {
        $errors['ngaychi']['required'] = '** Bạn chưa chọn ngày chi';
    }

    // Kiểm tra mảng error
    if (empty($errors)) {
        // không có lỗi nào
        $dataInsert = [
            'danhmucchi_id' => $body['danhmucchi_id'],
            'room_id' => $body['room_id'],
            'sotien' => $body['sotien'],
            'ghichu' => $body['ghichu'],
            'ngaychi' => $body['ngaychi'],
            'phuongthuc' => $body['phuongthuc'],
        ];

        $insertStatus = insert('payment', $dataInsert);
        if ($insertStatus) {
            setFlashData('msg', 'Thêm thông tin phiếu chi mới thành công');
            setFlashData('msg_type', 'suc');
            redirect('?module=payment');
        } else {
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
            redirect('?module=payment&action=add');
        }
    } else {
        // Có lỗi xảy ra
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);  // giữ lại các trường dữ liệu hợp lê khi nhập vào
        redirect('?module=payment&action=add');
    }
}
$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');
?>
<?php
layout('navbar', 'admin', $data);
?>

<div class="container">
    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

    <div class="box-content">
        <form action="" method="post" class="row" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="col-5">
                <div class="form-group">
                    <label for="">Chọn khu vực <span style="color: red">*</span></label>
                    <select name="area_id" id="area-select" class="form-select">
                        <option value="" disabled selected>Chọn khu vực</option>
                        <?php
                        if (!empty($allArea)) {
                            foreach ($allArea as $item) {
                        ?>
                                <option value="<?php echo $item['id'] ?>"
                                    <?php echo (!empty($areaId) && $areaId == $item['id']) ? 'selected' : '' ?>>
                                    <?php echo $item['tenkhuvuc'] ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                    <?php echo form_error('area_id', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Chọn phòng lập phiếu chi <span style="color: red">*</span></label>
                    <select name="room_id" id="room-select" class="form-select">
                        <option value="" disabled selected>Chọn phòng</option>
                        <!-- Danh sách phòng sẽ được cập nhật qua JavaScript -->
                    </select>
                    <?php echo form_error('room_id', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Danh mục chi <span style="color: red">*</span></label>
                    <select name="danhmucchi_id" id="" class="form-select">
                        <option value="" disabled selected>Chọn danh mục</option>
                        <?php
                        if (!empty($allPayment)) {
                            foreach ($allPayment as $item) {
                        ?>
                                <option value="<?php echo $item['id'] ?>" <?php echo (!empty($roomId) && $roomId == $item['id']) ? 'selected' : '' ?>><?php echo $item['tendanhmuc'] ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                    <?php echo form_error('danhmucchi_id', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Ghi chú <span style="color: red">*</span></label>
                    <textarea rows="5" name="ghichu" id="" class="form-control" value=""></textarea>
                    <?php echo form_error('ghichu', $errors, '<span class="error">', '</span>'); ?>
                </div>
            </div>

            <div class="col-5">

                <div class="form-group">
                    <label for="">Số tiền <span style="color: red">*</span></label>
                    <input type="text" placeholder="Nhập số tiền thu" name="sotien" id="" class="form-control" value="<?php echo old('sotien', $old); ?>">
                    <?php echo form_error('sotien', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Ngày lập phiếu <span style="color: red">*</span></label>
                    <input type="date" name="ngaychi" id="" class="form-control" value="<?php echo old('ngaychi', $old); ?>">
                    <?php echo form_error('ngaychi', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Phương thức thanh toán</label>
                    <select name="phuongthuc" class="form-select">
                        <option value="" disabled selected>Chọn phương thức</option>
                        <option value="0">Tiền mặt</option>
                        <option value="1">Chuyển khoản</option>
                    </select>
                </div>

            </div>

            <div class="btn-row">
                <a href="<?php echo getLinkAdmin('payment', 'lists'); ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-circle-left"></i> Quay lại
                </a>
                <button type="submit" class="btn btn-secondary">
                    <i class="fa fa-edit"></i> Thêm phiếu chi
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    const roomsByArea = <?php echo json_encode($roomsByArea); ?>; // Chuyển đổi mảng PHP sang JS
    const areaSelect = document.getElementById('area-select');
    const roomSelect = document.getElementById('room-select');

    areaSelect.addEventListener('change', function() {
        const areaId = this.value;
        roomSelect.innerHTML = '<option value=""disabled selected>Chọn phòng</option>'; // Reset danh sách phòng

        if (areaId && roomsByArea[areaId]) {
            roomsByArea[areaId].forEach(room => {
                const option = document.createElement('option');
                option.value = room.id;
                option.textContent = `${room.tenphong}`; // Hiển thị tên phòng và số người
                roomSelect.appendChild(option);
            });
        }
    });
</script>
<?php
layout('footer', 'admin');
