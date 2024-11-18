<?php
if (!defined('_INCODE')) die('Access denied...');

// Đặt tiêu đề trang
$data = [
    'pageTitle' => 'Cập nhật hợp đồng'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);
$allArea = getRaw("SELECT id, tenkhuvuc FROM area ORDER BY tenkhuvuc");
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
        "SELECT t.id,t.tenkhach, t.cmnd, t.ngaysinh, t.gioitinh, t.diachi
     FROM tenant t
     JOIN contract_tenant ct ON t.id = ct.tenant_id_1
     WHERE ct.contract_id_1 = :contract_id",
        ['contract_id' => $contract_id]
    );


    // Lấy danh sách các phòng và dịch vụ
    $allRoom = getRaw("SELECT room.id, room.tenphong FROM room ORDER BY room.tenphong");
    $allServices = getRaw("SELECT * FROM services ORDER BY tendichvu ASC");

    // Lấy danh sách dịch vụ đã chọn cho hợp đồng
    $selectedServices = getRaw("SELECT services.id FROM contract_services JOIN services ON contract_services.services_id = services.id WHERE contract_services.contract_id = $contract_id");
    $selectedServiceIds = array_column($selectedServices, 'id');

    // Nếu form được submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $room_id = $_POST['room_id'] ?? $contract['room_id'];
        $ngaylaphopdong = $_POST['ngaylaphopdong'] ?? $contract['ngaylaphopdong'];
        $ngayvao = $_POST['ngayvao'] ?? $contract['ngayvao'];
        $ngayra = $_POST['ngayra'] ?? $contract['ngayra'];
        $tinhtrangcoc = $_POST['tinhtrangcoc'] ?? $contract['tinhtrangcoc'];
        $ghichu = $_POST['ghichu'] ?? $contract['ghichu'];
        $sotiencoc = $_POST['sotiencoc'] ?? $contract['sotiencoc'];
        $dieukhoan1 = $_POST['dieukhoan1'] ?? $contract['dieukhoan1'];
        $dieukhoan2 = $_POST['dieukhoan2'] ?? $contract['dieukhoan2'];
        $dieukhoan3 = $_POST['dieukhoan3'] ?? $contract['dieukhoan3'];
        $services = $_POST['services'] ?? []; // Danh sách dịch vụ được chọn từ form
        $tempCustomersData = $_POST['tempCustomersData'] ?? '[]';
        $tempCustomers = json_decode($tempCustomersData, true);

        // Kiểm tra điều kiện để cập nhật hợp đồng
        if ($room_id && $ngaylaphopdong && $ngayvao && $ngayra && $tinhtrangcoc && $ghichu && $sotiencoc && $dieukhoan1 && $dieukhoan2 && $dieukhoan3) {
            // Nếu hợp đồng đã có, thực hiện cập nhật
            if (isset($contract_id)) {
                // Lấy danh sách khách thuê của hợp đồng
                $query = "SELECT tenant_id_1 FROM contract_tenant WHERE contract_id_1 = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$contract_id]);
                $existingTenants = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Cập nhật phòng cho từng khách thuê nếu khách chưa ở phòng mới
                foreach ($existingTenants as $tenant) {
                    $tenant_id = $tenant['tenant_id_1'];

                    // Kiểm tra xem khách đã ở phòng mới chưa
                    $stmt_check = $pdo->prepare("SELECT room_id FROM tenant WHERE id = ?");
                    $stmt_check->execute([$tenant_id]);
                    $tenant_room = $stmt_check->fetchColumn();

                    // Nếu khách chưa ở phòng mới, cập nhật phòng cho khách
                    if ($tenant_room != $room_id) {
                        // Cập nhật phòng mới cho khách thuê
                        $stmt_update_room = $pdo->prepare("UPDATE tenant SET room_id = ? WHERE id = ?");
                        $stmt_update_room->execute([$room_id, $tenant_id]);
                    }
                }

                // Cập nhật hợp đồng trong cơ sở dữ liệu
                $update = update('contract', [
                    'room_id' => $room_id,
                    'ngaylaphopdong' => $ngaylaphopdong,
                    'ngayvao' => $ngayvao,
                    'ngayra' => $ngayra,
                    'tinhtrangcoc' => $tinhtrangcoc,
                    'ghichu' => $ghichu,
                    'sotiencoc' => $sotiencoc,
                    'dieukhoan1' => $dieukhoan1,
                    'dieukhoan2' => $dieukhoan2,
                    'dieukhoan3' => $dieukhoan3,
                ], "id = $contract_id");

                // Xóa tất cả các dịch vụ đã liên kết với hợp đồng hiện tại
                $stmt_delete_services = $pdo->prepare("DELETE FROM contract_services WHERE contract_id = ?");
                $stmt_delete_services->execute([$contract_id]);


                // Cập nhật các dịch vụ nếu có thay đổi
                foreach ($services as $services_id) {
                    linkContractService($contract_id, $services_id);
                }

                // Thêm khách thuê mới vào hợp đồng, nếu có
                foreach ($tempCustomers as $customer) {
                    $tenkhach = $customer['tenkhach'];
                    $ngaysinh = $customer['ngaysinh'];
                    $gioitinh = $customer['gioitinh'];
                    $diachi = $customer['diachi'];
                    $cmnd = $customer['cmnd'];

                    // Kiểm tra khách có tồn tại trong hợp đồng chưa
                    $stmt_check = $pdo->prepare("SELECT tenant_id_1 FROM contract_tenant WHERE contract_id_1 = ? AND tenant_id_1 = ?");
                    $stmt_check->execute([$contract_id, $customer['id']]);

                    if ($stmt_check->rowCount() == 0) {
                        // Kiểm tra nếu khách thuê đã tồn tại dựa trên CMND
                        $tenant_id = getTenantIdByCmnd($cmnd); // Hàm này sẽ trả về tenant_id nếu tồn tại, nếu không sẽ trả về null

                        if ($tenant_id) {
                            // Nếu khách thuê đã tồn tại, kiểm tra và cập nhật room_id nếu cần
                            $existingTenantRoom = getTenantRoomById($tenant_id); // Hàm lấy room_id của khách thuê hiện tại
                            if ($existingTenantRoom != $room_id) {
                                updateTenantRoom($tenant_id, $room_id); // Hàm cập nhật room_id cho khách thuê
                            }
                        } else {
                            // Nếu khách thuê chưa tồn tại, thêm vào bảng tenant và lấy tenant_id mới
                            $tenant_id = addTenant($tenkhach, $ngaysinh, $gioitinh, $diachi, $room_id, $cmnd);
                        }

                        // Liên kết hợp đồng với khách thuê trong bảng contract_tenant
                        linkContractTenant($contract_id, $tenant_id);
                    }
                }
            }

            // Thông báo thành công
            setFlashData('msg', 'Hợp đồng, khách thuê và dịch vụ đã được cập nhật thành công!');
            setFlashData('msg_type', 'suc');
            redirect('?module=contract'); // Chuyển hướng đến trang hợp đồng
        } else {
            // Thông báo lỗi khi thiếu thông tin
            setFlashData('msg', 'Thiếu thông tin cần thiết để cập nhật hợp đồng.');
            setFlashData('msg_type', 'err');
            redirect('?module=contract&action=edit'); // Chuyển hướng lại trang thêm hợp đồng
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
            <div class="col-3">
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
                    <div>
                        <div id="tenantInfo" style="background-color:white; color: dark; max-height: 120px; overflow-y: auto;">
                            <ul id="tenantList">
                                <?php if (!empty($tenants)) {
                                    foreach ($tenants as $index => $tenant) {
                                        $tenkhach = !empty($tenant['tenkhach']) ? $tenant['tenkhach'] : 'Chưa có tên';
                                        $cmnd = !empty($tenant['cmnd']) ? $tenant['cmnd'] : 'Chưa có CMND';
                                        $ngaysinh = !empty($tenant['ngaysinh']) ? (new DateTime($tenant['ngaysinh']))->format('d/m/Y') : 'Chưa có ngày sinh';
                                        $gioitinh = !empty($tenant['gioitinh']) ? $tenant['gioitinh'] : 'Chưa có giới tính';
                                        $diachi = !empty($tenant['diachi']) ? $tenant['diachi'] : 'Chưa có địa chỉ';

                                        echo "<li id='tenant-{$tenant['id']}'>Khách " . ($index + 1) . ": {$tenkhach} - {$cmnd} - {$ngaysinh} - {$gioitinh} - {$diachi} 
                        <button type='button' onclick='deleteTenant(event, {$tenant['id']}, {$contract_id})'>Xóa</button></li>";
                                    }
                                } else {
                                    echo "<p>Chưa có tenant nào được thêm.</p>";
                                } ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <script>
                    // Chức năng xóa khách hàng độc lập
                    function deleteTenant(event, tenantId, contractId) {
                        // Ngừng hành động mặc định của sự kiện, ngăn chặn các sự kiện JavaScript khác
                        event.preventDefault();

                        if (confirm("Điều này sẽ xoá khách hoàn toàn,bạn có chắc chắn muốn xoá khách này không?")) {
                            // Gửi yêu cầu AJAX để xóa khách hàng
                            fetch(`includes/xoakhach.php?id=${tenantId}&contract_id=${contractId}`, {
                                    method: 'GET'
                                })
                                .then(response => response.json()) // Chuyển đổi phản hồi thành JSON
                                .then(data => {
                                    if (data.success) {
                                        // Nếu xóa thành công, loại bỏ khách khỏi danh sách
                                        document.getElementById(`tenant-${tenantId}`).remove();
                                    } else {
                                        alert("Lỗi khi xóa khách hàng: " + data.message);
                                    }
                                })
                                .catch(error => {
                                    console.error("Lỗi:", error);
                                    alert("Đã xảy ra lỗi khi kết nối với máy chủ.");
                                });
                        }
                    }
                </script>


                <div class="form-group">
                    <button type="button" class="btn btn-secondary" onclick="openPopup()"> <i class="fa fa-plus"> </i>Thêm khách</button>
                </div>
                <!-- Popup -->
                <div id="popupForm" class="popup" style="display: none;">
                    <div class="popup-content">
                        <span class="close-btn" onclick="closePopup()">&times;</span>

                        <!-- Nội dung bên trong popup -->
                        <div class="form-group">
                            <label for="cmnd">Số CMND/CCCD <span style="color: red">*</span></label>
                            <input type="text" placeholder="Số CMND/CCCD" name="cmnd" id="cmnd" class="form-control"
                                value="<?php echo old('cmnd', $old); ?>" required>
                            <?php echo form_error('cmnd', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Tên khách <span style="color: red">*</span></label>
                            <input type="text" placeholder="Tên khách thuê" name="tenkhach" id="" class="form-control" value="<?php echo old('tenkhach', $old); ?>">
                            <?php echo form_error('tenkhach', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Ngày sinh <span style="color: red">*</span></label>
                            <input type="date" name="ngaysinh" id="" class="form-control" value="<?php echo old('ngaysinh', $old); ?>">
                            <?php echo form_error('ngaysinh', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Giới tính <span style="color: red">*</span></label>
                            <select name="gioitinh" id="" class="form-select">
                                <option value="" disabled selected>Chọn giới tính</option>
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Địa chỉ <span style="color: red">*</span></label>
                            <input type="text" placeholder="Địa chỉ" name="diachi" id="" class="form-control" value="<?php echo old('diachi', $old); ?>">
                            <?php echo form_error('diachi', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="col-12">
                            <button type="button" class="btn btn-secondary" onclick="closePopup()"> <i class="fa fa-edit"> </i> Đóng </button>
                            <button type="button" class="btn btn-secondary" onclick="addTempCustomer()">
                                <i class="fa fa-plus"></i> Thêm khách
                            </button>
                        </div>
                    </div>
                </div>
                <label for="">Danh sách khách vừa tạo</label>
                <div class="form-group">
                    <!-- Khu vực để hiển thị danh sách khách tạm thời -->
                    <div style="background-color: white; border: 0.5px solid #ccc; padding: 5px; border-radius: 5px; height: 135px; display: flex; flex-direction: column;">
                        <ul style="max-height: 250px; overflow-y: auto; padding: 0; list-style-type: none;">
                            <li>
                                <div id="tempCustomerInfo" style="color: green;"></div>
                            </li>
                            <!-- Các phần tử khác sẽ được thêm vào dưới đây -->
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-3">
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
                <div class="form-group">
                    <label for="">Ngày hết hạn hợp đồng</label>
                    <input type="date" name="ngayra" class="form-control"
                        value="<?php echo $contract['ngayra']; ?>">
                </div>
                <div class="form-group">
                    <label for="">Tình trạng cọc<span style="color: red">*</span></label>
                    <select name="tinhtrangcoc" class="form-select">
                        <option value="" disabled <?php echo ($contract['tinhtrangcoc'] === null) ? 'selected' : ''; ?>>Chọn trạng thái</option>
                        <option value="2" <?php echo ($contract['tinhtrangcoc'] == 2) ? 'selected' : ''; ?>>Chưa thu</option>
                        <option value="1" <?php echo ($contract['tinhtrangcoc'] == 1) ? 'selected' : ''; ?>>Đã thu</option>
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
            <div class="col-3">
                <div class="form-group">
                    <label for=""> Điều khoản 1<span style="color: red">*</span></label>
                    <textarea name="dieukhoan1" class="form-control" rows="4" style="width: 100%; height: 152px;"><?php echo $contract['dieukhoan1']; ?></textarea>
                    <?php echo form_error('dieukhoan1', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <div class="form-group">
                    <label for=""> Điều khoản 2<span style="color: red">*</span></label>
                    <textarea name="dieukhoan2" class="form-control" rows="4" style="width: 100%; height: 152px;"><?php echo $contract['dieukhoan2']; ?></textarea>
                    <?php echo form_error('dieukhoan2', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <div class="form-group">
                    <label for=""> Điều khoản 3<span style="color: red">*</span></label>
                    <textarea name="dieukhoan3" class="form-control" rows="4" style="width: 100%; height: 152px;"><?php echo $contract['dieukhoan3']; ?></textarea>
                    <?php echo form_error('dieukhoan3', $errors, '<span class="error">', '</span>'); ?>
                </div>

            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="">Dịch vụ sử dụng <span style="color: red">*</span></label>
                    <div class="checkbox-container">
                        <?php foreach ($allServices as $service) { ?>
                            <div class="checkbox-item">
                                <!-- Chỉ cần một input checkbox và kiểm tra xem dịch vụ đã được chọn chưa -->
                                <input type="checkbox" name="services[]"
                                    id="service-<?php echo htmlspecialchars($service['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    value="<?php echo htmlspecialchars($service['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    <?php echo in_array($service['id'], $selectedServiceIds) ? 'checked' : ''; ?>>
                                <label for="service-<?php echo htmlspecialchars($service['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($service['tendichvu'], ENT_QUOTES, 'UTF-8'); ?>
                                    - Giá: <?php echo number_format($service['giadichvu']); ?> VND/<?php echo htmlspecialchars($service['donvitinh'], ENT_QUOTES, 'UTF-8'); ?>
                                </label>
                            </div>
                        <?php } ?>
                    </div>
                    <?php if (isset($errors['services'])) { ?>
                        <span class="error" style="color: red;">
                            <?php echo $errors['services']; ?>
                        </span>
                    <?php } ?>
                </div>

                <!-- Input ẩn để lưu danh sách khách thuê tạm -->

                <form id="contractForm" method="post" action="">
                    <input type="hidden" name="tempCustomersData" id="tempCustomersData">
                    <a style="margin-right: 5px" href="<?php echo getLinkAdmin('contract') ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-circle-left"></i> Quay lại
                    </a>
                    <button type="submit" class="btn btn-secondary" onclick="submitFormWithTempCustomers()">
                        <i class="fa fa-plus"></i> Cập nhật
                    </button>
            </div>
    </diV>
    </dm>
</div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function submitFormWithTempCustomers() {
            const tempCustomersDataElem = document.getElementById('tempCustomersData');
            const contractFormElem = document.getElementById('contractForm'); // Đảm bảo phần tử này có mặt trong HTML

            if (tempCustomersDataElem && contractFormElem) {
                tempCustomersDataElem.value = JSON.stringify(tempCustomers); // Đảm bảo `tempCustomers` đã được khai báo và có dữ liệu
                contractFormElem.submit();
            } else {
                console.error("Không tìm thấy phần tử 'tempCustomersData' hoặc 'contractForm'.");
            }
        }

        // Gán sự kiện click cho nút
        document.querySelector('.btn.btn-secondary[onclick="submitFormWithTempCustomers()"]').onclick = submitFormWithTempCustomers;
    });
</script>
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
    let tempCustomers = [];

    document.getElementById('cmnd').addEventListener('blur', function() {
        const cmnd = this.value.trim();
        if (cmnd) {
            const countcmnd = /^[0-9]{9}$|^[0-9]{12}$/;
            if (!countcmnd.test(cmnd)) {
                alert("CMND/CCCD phải có dạng là 9 hoặc 12 chữ số.");
                return;
            }

            fetch('includes/check_cmnd.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        cmnd: cmnd
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        if (data.hasRoom) {
                            alert("Khách này đang có phòng hoặc đang có hợp đồng rồi.");
                            // Không điền các trường thông tin khách hàng vào form
                        } else {
                            // Điền các trường thông tin khách hàng vào form
                            const {
                                tenkhach,
                                gioitinh,
                                diachi,
                                ngaysinh,
                                id
                            } = data.customer;
                            document.querySelector('[name="tenkhach"]').value = tenkhach;
                            document.querySelector('[name="gioitinh"]').value = gioitinh;
                            document.querySelector('[name="diachi"]').value = diachi;
                            document.querySelector('[name="ngaysinh"]').value = ngaysinh;
                            document.querySelector('[name="customer_id"]').value = id;
                            // alert('CMND/CCCD đã tồn tại trong cơ sở dữ liệu. Thông tin khách đã được tự động điền vào form.');
                        }
                    } else {
                        // alert('CMND/CCCD không tồn tại trong cơ sở dữ liệu.');
                        document.querySelector('[name="tenkhach"]').value = '';
                        document.querySelector('[name="gioitinh"]').value = '';
                        document.querySelector('[name="diachi"]').value = '';
                        document.querySelector('[name="ngaysinh"]').value = '';
                        document.querySelector('[name="customer_id"]').value = '';
                    }
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                });
        } else {
            alert('Vui lòng nhập CMND/CCCD');
        }
    });



    // Hàm thêm khách vào danh sách tạm
    function addTempCustomer() {
        const tenkhach = document.querySelector('[name="tenkhach"]').value;
        const ngaysinh = document.querySelector('[name="ngaysinh"]').value;
        const gioitinh = document.querySelector('[name="gioitinh"]').value;
        const diachi = document.querySelector('[name="diachi"]').value;
        const cmnd = document.querySelector('[name="cmnd"]').value;

        if (tenkhach && ngaysinh && gioitinh && diachi && cmnd) {
            // Kiểm tra tên khách phải lớn hơn 5 ký tự
            if (tenkhach.length <= 5) {
                alert("Tên khách phải lớn hơn 5 ký tự.");
                return;
            }

            // Kiểm tra định dạng CMND phải là 9 hoặc 12 chữ số
            const countcmnd = /^[0-9]{9}$|^[0-9]{12}$/;
            if (!countcmnd.test(cmnd)) {
                alert("CMND/CCCD phải có dạng là 9 hoặc 12 chữ số.");
                return;
            }

            // Kiểm tra xem CMND đã tồn tại trong danh sách tạm hay chưa
            const isDuplicate = tempCustomers.some(customer => customer.cmnd === cmnd);
            if (isDuplicate) {
                alert("CMND/CCCD đã tồn tại trong danh sách khách vừa tạo.");
                return;
            }

            // Thêm khách vào danh sách tạm
            tempCustomers.push({
                tenkhach,
                ngaysinh,
                gioitinh,
                diachi,
                cmnd
            });
            // Reset form input
            document.querySelector('[name="tenkhach"]').value = '';
            document.querySelector('[name="ngaysinh"]').value = '';
            document.querySelector('[name="gioitinh"]').value = '';
            document.querySelector('[name="diachi"]').value = '';
            document.querySelector('[name="cmnd"]').value = '';
            // Cập nhật danh sách hiển thị
            updateTempCustomerList();
        } else {
            alert('Vui lòng nhập đầy đủ thông tin khách thuê.');
        }
    }

    // Cập nhật danh sách khách tạm
    function updateTempCustomerList() {
        const customerInfoList = tempCustomers.map((customer, index) => {
            // Chuyển định dạng ngày hiển thị
            const date = new Date(customer.ngaysinh);
            const day = String(date.getDate()).padStart(2, '0'); // Lấy ngày và thêm 0 nếu cần
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Lấy tháng (lưu ý: tháng bắt đầu từ 0)
            const year = date.getFullYear(); // Lấy năm

            // Tạo định dạng ngày: tháng/ngày/năm
            const formattedDate = `${day}/${month}/${year}`;

            return `<div>
            Khách ${index + 1}: ${customer.tenkhach} - ${customer.cmnd} - ${formattedDate} - ${customer.gioitinh} - ${customer.diachi}
            <button onclick="removeTempCustomer(${index})">Xóa</button>
        </div>`;
        }).join('');

        document.getElementById('tempCustomerInfo').innerHTML = customerInfoList; // Cập nhật nội dung HTML
    }

    // Xóa khách khỏi danh sách tạm
    function removeTempCustomer(index) {
        // Xoá khách hàng tại vị trí index trong mảng
        tempCustomers.splice(index, 1);
        // Cập nhật lại danh sách hiển thị sau khi xoá
        updateTempCustomerList();
    }

    // Mở popup
    function openPopup() {
        document.getElementById('popupForm').style.display = 'flex';
    }

    // Đóng popup
    function closePopup() {
        document.getElementById('popupForm').style.display = 'none';
    }
</script>
<?php
layout('footer', 'admin');
?>