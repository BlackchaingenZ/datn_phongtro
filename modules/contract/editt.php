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
    // Lấy thông tin hợp đồng
    $contract = getRow("SELECT * FROM contract WHERE id = $contract_id");

    // Lấy tất cả thông tin khách hàng liên kết với hợp đồng qua contract_tenant
    $tenants = getAll(
        "SELECT t.tenkhach, t.cmnd, t.ngaysinh, t.gioitinh, t.diachi
     FROM tenant t
     JOIN contract_tenant ct ON t.id = ct.tenant_id_1
     WHERE ct.contract_id_1 = :contract_id",
        ['contract_id' => $contract_id]
    );

    $tenant_info = ''; // Biến để chứa thông tin khách hàng

    // In thông tin khách hàng
    echo nl2br($tenant_info);  // nl2br để chuyển đổi ký tự xuống dòng thành <br> trong HTML
    // Lấy danh sách các phòng, dịch vụ và khu vực
    $allRoom = getRaw("SELECT room.id, room.tenphong FROM room ORDER BY room.tenphong");
    $allServices = getRaw("SELECT * FROM services ORDER BY tendichvu ASC");
    $allArea = getRaw("SELECT id, tenkhuvuc FROM area ORDER BY tenkhuvuc");

    // Lấy danh sách dịch vụ đã chọn cho hợp đồng
    $selectedServices = getRaw("SELECT services.id FROM contract_services JOIN services ON contract_services.services_id = services.id WHERE contract_services.contract_id = $contract_id");
    $selectedServiceIds = array_column($selectedServices, 'id');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Kiểm tra và lưu thông tin hợp đồng
        $area_id = $_POST['area_id'];
        $room_id = $_POST['room_id'];
        $ngaylaphopdong = $_POST['ngaylaphopdong'];
        $ngayvao = $_POST['ngayvao'];
        $ngayra = $_POST['ngayra'];
        $tinhtrangcoc = $_POST['tinhtrangcoc'];
        $sotiencoc = $_POST['sotiencoc'];
        $ghichu = $_POST['ghichu'];
        $dieukhoan1 = $_POST['dieukhoan1'];
        $dieukhoan2 = $_POST['dieukhoan2'];
        $dieukhoan3 = $_POST['dieukhoan3'];

        // Cập nhật thông tin hợp đồng
        $updateContract = "UPDATE contract SET 
            area_id = ?, room_id = ?, ngaylaphopdong = ?, ngayvao = ?, ngayra = ?, 
            tinhtrangcoc = ?, sotiencoc = ?, ghichu = ?, dieukhoan1 = ?, dieukhoan2 = ?, dieukhoan3 = ?
            WHERE id = ?";
        $stmt = $pdo->prepare($updateContract);
        $stmt->execute([
            $area_id,
            $room_id,
            $ngaylaphopdong,
            $ngayvao,
            $ngayra,
            $tinhtrangcoc,
            $sotiencoc,
            $ghichu,
            $dieukhoan1,
            $dieukhoan2,
            $dieukhoan3,
            $contract_id
        ]);

        // Cập nhật thông tin khách thuê, kiểm tra các trường hợp đã thay đổi
        if (!empty($_POST['tenkhach'])) {
            $tenkhach = $_POST['tenkhach'];
            $cmnd = $_POST['cmnd'];
            $ngaysinh = $_POST['ngaysinh'];
            $gioitinh = $_POST['gioitinh'];
            $diachi = $_POST['diachi'];

            // Cập nhật hoặc thêm khách thuê mới
            $insertTenant = "INSERT INTO tenant (tenkhach, cmnd, ngaysinh, gioitinh, diachi, room_id) 
                             VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($insertTenant);
            $stmt->execute([$tenkhach, $cmnd, $ngaysinh, $gioitinh, $diachi, $room_id]);
        }
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
<!-- Giao diện form cập nhật hợp đồng -->
<div class="container">
    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>
    <div class="box-content">
        <form id="contractForm" action="" method="post" class="row">
            <div class="col-4">
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
                    <label for="">Danh sách khách đã thêm</label>
                    <div style="border: 1px solid #ccc; border-radius: 5px; padding: 10px; margin-top: 10px; height: 150px; background-color: #f9f9f9;">
                        <div id="tenantInfo" style="color: green;">
                            <?php
                            // Kiểm tra và hiển thị thông tin tenant
                            if (!empty($tenants)) {
                                foreach ($tenants as $index => $tenant) {
                                    // Kiểm tra sự tồn tại và giá trị của các trường
                                    $tenkhach = !empty($tenant['tenkhach']) ? $tenant['tenkhach'] : 'Chưa có tên';
                                    $cmnd = !empty($tenant['cmnd']) ? $tenant['cmnd'] : 'Chưa có CMND';
                                    $ngaysinh = !empty($tenant['ngaysinh']) ? $tenant['ngaysinh'] : 'Chưa có ngày sinh';
                                    $gioitinh = !empty($tenant['gioitinh']) ? $tenant['gioitinh'] : 'Chưa có giới tính';
                                    $diachi = !empty($tenant['diachi']) ? $tenant['diachi'] : 'Chưa có địa chỉ';
                                    $tenant_id = !empty($tenant['id']) ? $tenant['id'] : 'Không có ID';

                                    // Hiển thị thông tin khách hàng và nút xóa
                                    echo "<div>
                        Khách " . ($index + 1) . ": {$tenkhach} - {$cmnd} - {$ngaysinh} - {$gioitinh} - {$diachi}

                    </div>";
                                }
                            } else {
                                echo "<p>Chưa có tenant nào được thêm.</p>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="">Ngày lập hợp đồng</label>
                    <input type="date" name="ngaylaphopdong" class="form-control"
                        value="<?php echo $contract['ngaylaphopdong']; ?>">
                </div>
            </div>
            <div class="col-4">
                <div class="form-group">
                    <label for="">Ngày vào ở</label>
                    <input type="date" name="ngayvao" class="form-control"
                        value="<?php echo $contract['ngayvao']; ?>">
                </div>
                <div class="form-group">
                    <label for="">Ngày hết hạn hợp đồng</label>
                    <input type="date" name="ngayra" class="form-control"
                        value="<?php echo $contract['ngayra']; ?>">
                </div>
                <div class="form-group">
                    <label for="">Tình trạng cọc<span style="color: red">*</span></label>
                    <select name="tinhtrangcoc" class="form-select">
                        <option value="" disabled <?php echo ($contract['tinhtrangcoc'] === null) ? 'selected' : ''; ?>>Chọn trạng thái</option>
                        <option value="0" <?php echo ($contract['tinhtrangcoc'] == 0) ? 'selected' : ''; ?>>Chưa thu tiền</option>
                        <option value="1" <?php echo ($contract['tinhtrangcoc'] == 1) ? 'selected' : ''; ?>>Đã thu tiền</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="">Số tiền cọc <span style="color: red">*</span></label>
                    <input type="text" placeholder="Nhập số tiền" name="sotiencoc" id="sotiencoc" class="form-control"
                        value="<?php echo $contract['sotiencoc']; ?>">
                    <?php echo form_error('sotiencoc', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Ghi chú</label>
                    <textarea name="ghichu" class="form-control"><?php echo $contract['ghichu']; ?></textarea>
                </div>

            </div>

            <div class="col-4">
                <div class="form-group">
                    <label for=""> Điều khoản 1<span style="color: red">*</span></label>
                    <textarea name="dieukhoan1" class="form-control" rows="4" style="width: 100%; height: 82px;"><?php echo $contract['dieukhoan1']; ?>"></textarea>
                    <?php echo form_error('dieukhoan1', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <div class="form-group">
                    <label for=""> Điều khoản 2<span style="color: red">*</span></label>
                    <textarea name="dieukhoan2" class="form-control" rows="4" style="width: 100%; height: 82px;"><?php echo $contract['dieukhoan2']; ?>"></textarea>
                    <?php echo form_error('dieukhoan2', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <div class="form-group">
                    <label for=""> Điều khoản 3<span style="color: red">*</span></label>
                    <textarea name="dieukhoan3" class="form-control" rows="4" style="width: 100%; height: 82px;"><?php echo $contract['dieukhoan3']; ?>"></textarea>
                    <?php echo form_error('dieukhoan3', $errors, '<span class="error">', '</span>'); ?>
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
                <button type="submit" class="btn btn-secondary" onclick="submitFormWithTempCustomers()">
                    <i class="fa fa-plus"></i> Cập nhật hợp đồng
                </button>
            </div>
    </diV>
    </dm>
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
<script>
    let tenants = <?php echo json_encode($tenants); ?>;
    let editIndex = null;

    function openPopup() {
        document.getElementById('popupForm').style.display = 'block';
    }

    function closePopup() {
        document.getElementById('popupForm').style.display = 'none';
        clearForm();
        editIndex = null;
    }

    function editTenant(index) {
        openPopup();
        editIndex = index;
        const tenant = tenants[index];
        document.getElementById('tenkhach').value = tenant.tenkhach;
        document.getElementById('cmnd').value = tenant.cmnd;
        document.getElementById('ngaysinh').value = tenant.ngaysinh;
        document.getElementById('gioitinh').value = tenant.gioitinh;
        document.getElementById('diachi').value = tenant.diachi;
    }

    function saveTenant() {
        const tenkhach = document.getElementById('tenkhach').value;
        const cmnd = document.getElementById('cmnd').value;
        const ngaysinh = document.getElementById('ngaysinh').value;
        const gioitinh = document.getElementById('gioitinh').value;
        const diachi = document.getElementById('diachi').value;

        if (editIndex !== null) {
            tenants[editIndex] = {
                tenkhach,
                cmnd,
                ngaysinh,
                gioitinh,
                diachi
            };
        } else {
            tenants.push({
                tenkhach,
                cmnd,
                ngaysinh,
                gioitinh,
                diachi
            });
        }
        closePopup();
        updateTenantList();
    }

    function clearForm() {
        document.getElementById('tenkhach').value = '';
        document.getElementById('cmnd').value = '';
        document.getElementById('ngaysinh').value = '';
        document.getElementById('gioitinh').value = '';
        document.getElementById('diachi').value = '';
    }

    function updateTenantList() {
        const tenantInfoDiv = document.getElementById('tenantInfo');
        tenantInfoDiv.innerHTML = '';
        tenants.forEach((tenant, index) => {
            tenantInfoDiv.innerHTML += `<div><strong>Tên:</strong> ${tenant.tenkhach} - <strong>CMND:</strong> ${tenant.cmnd} -<strong> Ngày sinh:</strong> ${tenant.ngaysinh} - <strong> Địa chỉ:</strong> ${tenant.diachi} <button onclick="editTenant(${index})">Chỉnh sửa</button></div>`;
        });
    }
</script>

<?php
layout('footer', 'admin');
?>