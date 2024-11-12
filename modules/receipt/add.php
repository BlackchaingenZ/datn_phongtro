<?php

if (!defined('_INCODE'))
    die('Access denied...');


$data = [
    'pageTitle' => 'Thêm phiếu thu mới'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);
$allArea = getRaw("SELECT id, tenkhuvuc FROM area ORDER BY tenkhuvuc");
$allCollect = getRaw("SELECT * FROM category_collect");
$allRoom = getRaw("
    SELECT room.id, room.tenphong, room.soluong 
    FROM room 
    WHERE room.id IN (SELECT room_id FROM contract)
    ORDER BY room.tenphong
");
$roomsByArea = [];
foreach ($allRoom as $room) {
    // Lấy số lượng người hiện tại trong phòng từ bảng tenant
    $soluong = getRaw("SELECT COUNT(*) AS soluong FROM tenant WHERE room_id = " . $room['id'])[0]['soluong'];

    $areaIds = getRaw("SELECT area_id FROM area_room WHERE room_id = " . $room['id']);
    foreach ($areaIds as $area) {
        // Thêm thông tin số người vào mỗi phòng theo khu vực
        $roomsByArea[$area['area_id']][] = [
            'id' => $room['id'],
            'tenphong' => $room['tenphong'],
            'soluong' => $soluong
        ];
    }
}
// Xử lý thêm hợp đồng
if (isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi

    //Valide họ tên: Bắt buộc phải nhập, >=5 ký tự
    if (empty(trim($body['room_id']))) {
        $errors['room_id']['required'] = '** Bạn chưa chọn phòng lập phiếu thu';
    }
    if (empty(trim($body['danhmucthu_id']))) {
        $errors['danhmucthu_id']['required'] = '** Bạn chưa chọn danh mục để lập phiếu';
    }
    if (empty(trim($body['sotien']))) {
        $errors['sotien']['required'] = '** Bạn chưa điền số tiền thu';
    }
    if (empty(trim($body['ngaythu']))) {
        $errors['ngaythu']['required'] = '** Bạn chưa chọn ngày thu';
    }

    // Kiểm tra mảng error
    if (empty($errors)) {
        // không có lỗi nào
        $dataInsert = [
            'danhmucthu_id' => $body['danhmucthu_id'],
            'room_id' => $body['room_id'],
            'sotien' => $body['sotien'],
            'ghichu' => $body['ghichu'],
            'ngaythu' => $body['ngaythu'],
            'phuongthuc' => $body['phuongthuc'],
            // 'create_at' => date('Y-m-d H:i:s'),
        ];

        $insertStatus = insert('receipt', $dataInsert);
        if ($insertStatus) {
            setFlashData('msg', 'Thêm thông tin phiếu thu mới thành công');
            setFlashData('msg_type', 'suc');
            redirect('?module=receipt');
        } else {
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
            redirect('?module=receipt&action=add');
        }
    } else {
        // Có lỗi xảy ra
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);  // giữ lại các trường dữ liệu hợp lê khi nhập vào
        redirect('?module=receipt&action=add');
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
                    <label for="">Danh mục thu <span style="color: red">*</span></label>
                    <select name="danhmucthu_id" id="" class="form-select">
                        <option value="">Chọn danh mục</option>
                        <?php
                        if (!empty($allCollect)) {
                            foreach ($allCollect as $item) {
                        ?>
                                <option value="<?php echo $item['id'] ?>" <?php echo (!empty($roomId) && $roomId == $item['id']) ? 'selected' : '' ?>><?php echo $item['tendanhmuc'] ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                    <?php echo form_error('danhmucthu_id', $errors, '<span class="error">', '</span>'); ?>
                </div>

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
                    <label for="">Chọn phòng lập hợp đồng <span style="color: red">*</span></label>
                    <select name="room_id" id="room-select" class="form-select">
                        <option value="" disabled selected>Chọn phòng</option>
                        <!-- Danh sách phòng sẽ được cập nhật qua JavaScript -->
                    </select>
                    <?php echo form_error('room_id', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Nội dung thanh toán <span style="color: red">*</span></label>
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
                    <label for="">Ngày thu <span style="color: red">*</span></label>
                    <input type="date" name="ngaythu" id="" class="form-control" value="<?php echo old('ngaythu', $old); ?>">
                    <?php echo form_error('ngaythu', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Phương thức thanh toán</label>
                    <select name="phuongthuc" class="form-select">
                        <option value="">Chọn phương thức</option>
                        <option value="0">Tiền mặt</option>
                        <option value="1">Chuyển khoản</option>
                    </select>
                </div>

            </div>
            <div class="from-group">
                <div class="btn-row">
                    <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Thêm phiếu thu</button>
                    <a style="margin-left: 20px " href="<?php echo getLinkAdmin('receipt') ?>" class="btn btn-success"><i class="fa fa-forward"></i></a>
                </div>
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
                option.textContent = `${room.tenphong} đang ở (${room.soluong} người)`; // Hiển thị tên phòng và số người
                roomSelect.appendChild(option);
            });
        }
    });
</script>

<?php
layout('footer', 'admin');
