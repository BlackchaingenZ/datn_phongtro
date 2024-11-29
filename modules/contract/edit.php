<?php
if (!defined('_INCODE')) die('Access denied...');

// Đặt tiêu đề trang
$data = [
    'pageTitle' => 'Cập nhật hợp đồng'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// kiểm tra nếu phòng nào có hợp đồng rồi thì không hiện
$allRoom = getRaw("
    SELECT room.id, room.tenphong, room.soluong 
    FROM room 
    WHERE room.id NOT IN (SELECT room_id FROM contract)
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
// Lấy ID hợp đồng từ URL
$contract_id = $_GET['id'] ?? null;

if ($contract_id) {
    // Lấy thông tin hợp đồng hiện tại từ cơ sở dữ liệu
    $contract = getRow("SELECT * FROM contract WHERE id = $contract_id");

    // Lấy danh sách các phòng, dịch vụ và khu vực
    $allRoom = getRaw("SELECT room.id, room.tenphong FROM room ORDER BY room.tenphong");
    $allServices = getRaw("SELECT * FROM services ORDER BY tendichvu ASC");
    $allArea = getRaw("SELECT id, tenkhuvuc FROM area ORDER BY tenkhuvuc");

    // Lấy danh sách dịch vụ đã chọn cho hợp đồng
    $selectedServices = getRaw("SELECT services.id FROM contract_services JOIN services ON contract_services.services_id = services.id WHERE contract_services.contract_id = $contract_id");
    $selectedServiceIds = array_column($selectedServices, 'id');

    // Lấy danh sách các phòng, dịch vụ và khu vực
    $allServices = getRaw("SELECT * FROM services ORDER BY tendichvu ASC");
    $allArea = getRaw("SELECT id, tenkhuvuc FROM area ORDER BY tenkhuvuc");
    // Nếu form được submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $room_id = $_POST['room_id'] ?? $contract['room_id'];
        $ngaylaphopdong = $_POST['ngaylaphopdong'] ?? $contract['ngaylaphopdong'];
        $ngayvao = $_POST['ngayvao'] ?? $contract['ngayvao'];
        $ngayra = $_POST['ngayra'] ?? $contract['ngayra'];
        $tinhtrangcoc = $_POST['tinhtrangcoc'] ?? $contract['tinhtrangcoc'];
        $ghichu = $_POST['ghichu'] ?? $contract['ghichu'];
        $services = $_POST['services'] ?? []; // Danh sách dịch vụ được chọn từ form

        // Cập nhật hợp đồng trong cơ sở dữ liệu
        $update = update('contract', [
            'room_id' => $room_id,
            'ngaylaphopdong' => $ngaylaphopdong,
            'ngayvao' => $ngayvao,
            'ngayra' => $ngayra,
            'tinhtrangcoc' => $tinhtrangcoc,
            'ghichu' => $ghichu
        ], "id = $contract_id");

        // Xóa các dịch vụ cũ và thêm các dịch vụ mới liên quan đến hợp đồng
        delete('contract_services', "contract_id = $contract_id");
        foreach ($services as $services_id) {
            linkContractService($contract_id, $services_id);
        }

        // Thông báo cập nhật thành công
        setFlashData('msg', 'Hợp đồng đã được cập nhật thành công!');
        setFlashData('msg_type', 'suc');
        redirect('?module=contract'); // Chuyển hướng đến trang danh sách hợp đồng
    }
} else {
    // Chuyển hướng về danh sách hợp đồng nếu không có ID hợp đồng
    redirect('?module=contract');
}
$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');
?>
<?php
layout('navbar', 'admin', $data);
?>
<!-- Giao diện form cập nhật hợp đồng -->
<div class="container">
    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>
    <div class="box-content">
        <form id="contractForm" action="" method="post" class="row">
            <div class="col-5">
                <div class="form-group">
                    <label for="">Chọn khu vực <span style="color: red">*</span></label>
                    <select name="area_id" id="area-select" class="form-select">
                        <option value="" disabled <?php echo empty($contract['area_id']) ? 'selected' : ''; ?>>Chọn khu vực</option>
                        <?php
                        if (!empty($allArea)) {
                            foreach ($allArea as $item) {
                        ?>
                                <option value="<?php echo $item['id']; ?>"
                                    <?php echo (isset($contract['area_id']) && $contract['area_id'] == $item['id']) ? 'selected' : ''; ?>>
                                    <?php echo $item['tenkhuvuc']; ?>
                                </option>
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
                        <option value="" disabled <?php echo empty($contract['room_id']) ? 'selected' : ''; ?>>Chọn phòng</option>
                        <?php
                        // Nếu hợp đồng đã tồn tại, hiển thị phòng đã chọn trước đó
                        // Hiển thị tên phòng đã chọn thay vì id
                        $selectedRoom = getRaw("SELECT tenphong FROM room WHERE id = " . $contract['room_id']);
                        echo '<option value="' . $contract['room_id'] . '" selected>' . htmlspecialchars($selectedRoom[0]['tenphong'], ENT_QUOTES, 'UTF-8') . ' (Đã chọn)</option>';


                        // Hiển thị danh sách phòng theo khu vực đã chọn
                        if (!empty($roomsByArea)) {
                            $areaId = $contract['area_id'] ?? null; // Lấy khu vực từ hợp đồng
                            if (isset($roomsByArea[$areaId])) {
                                foreach ($roomsByArea[$areaId] as $room) {
                                    echo '<option value="' . $room['id'] . '"' . ($room['id'] == $contract['room_id'] ? ' selected' : '') . '>' . htmlspecialchars($room['tenphong'], ENT_QUOTES, 'UTF-8') . ' đang ở (' . $room['soluong'] . ' người)</option>';
                                }
                            }
                        }
                        ?>
                    </select>
                    <?php echo form_error('room_id', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <div class="form-group">
                    <label for="">Ngày lập hợp đồng</label>
                    <input type="date" name="ngaylaphopdong" class="form-control"
                        value="<?php echo $contract['ngaylaphopdong']; ?>">
                </div>
                <div class="form-group">
                    <label for="">Ngày vào ở</label>
                    <input type="date" name="ngayvao" class="form-control"
                        value="<?php echo $contract['ngayvao']; ?>">
                </div>

            </div>

            <div class="col-5">

                <div class="form-group">
                    <label for="">Ngày hết hạn hợp đồng</label>
                    <input type="date" name="ngayra" class="form-control"
                        value="<?php echo $contract['ngayra']; ?>">
                </div>

                <div class="form-group">
                    <label for="">Ghi chú</label>
                    <textarea name="ghichu" class="form-control"><?php echo $contract['ghichu']; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="">Chọn dịch vụ <span style="color: red">*</span></label>
                    <div class="checkbox-container">
                        <?php foreach ($allServices as $service) { ?>
                            <div class="checkbox-item">
                                <input type="checkbox" name="services[]" id="service-<?php echo htmlspecialchars($service['id'], ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($service['id'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo in_array($service['id'], $selectedServiceIds) ? 'checked' : ''; ?>>
                                <label for="service-<?php echo htmlspecialchars($service['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($service['tendichvu'], ENT_QUOTES, 'UTF-8'); ?>
                                </label>
                            </div>
                        <?php } ?>
                    </div>
                    <?php if (isset($errors['services'])) { ?>
                        <span class="error" style="color: red;"><?php echo htmlspecialchars($errors['services'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php } ?>
                </div>

                <a style="margin-right: 20px" href="<?php echo getLinkAdmin('contract') ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-circle-left"></i> Quay lại
                </a>
                <button type="submit" class="btn btn-secondary">
                    <i class="fa fa-plus"></i> Cập nhật hợp đồng
                </button>
            </div>
    </diV>
    </form>
</div>
</div>
<script>
    // Chuyển đổi mảng PHP sang JS
    const roomsByArea = <?php echo json_encode($roomsByArea); ?>;
    const areaSelect = document.getElementById('area-select');
    const roomSelect = document.getElementById('room-select');

    // Hàm để thiết lập danh sách phòng theo khu vực
    function updateRoomSelect(areaId) {
        roomSelect.innerHTML = '<option value="" disabled selected>Chọn phòng</option>'; // Reset danh sách phòng

        if (areaId && roomsByArea[areaId]) {
            roomsByArea[areaId].forEach(room => {
                const option = document.createElement('option');
                option.value = room.id;
                option.textContent = `${room.tenphong}`; // Hiển thị tên phòng và số người

                roomSelect.appendChild(option);
            });
        }

        // Nếu có dữ liệu hợp đồng đã lưu, chọn phòng đã lưu
        if (savedContract && savedContract.room_id) {
            roomSelect.value = savedContract.room_id; // Chọn phòng đã lưu
        }
    }

    // Lắng nghe sự kiện thay đổi khu vực
    areaSelect.addEventListener('change', function() {
        const areaId = this.value;
        updateRoomSelect(areaId);
    });

    // Khởi tạo danh sách phòng nếu khu vực đã được chọn trước đó
    if (areaSelect.value) {
        updateRoomSelect(areaSelect.value);
    }
</script>

<?php
layout('footer', 'admin');
?>