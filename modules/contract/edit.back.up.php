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
        "SELECT t.tenkhach, t.cmnd, t.ngaysinh, t.gioitinh, t.diachi
     FROM tenant t
     JOIN contract_tenant ct ON t.id = ct.tenant_id_1
     WHERE ct.contract_id_1 = :contract_id",
        ['contract_id' => $contract_id]
    );

    $tenant_info = ''; // Biến để chứa thông tin khách hàng

    // In thông tin khách hàng
    echo nl2br($tenant_info);  // nl2br để chuyển đổi ký tự xuống dòng thành <br> trong HTML
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

        // Kiểm tra điều kiện để thêm hợp đồng hoặc cập nhật hợp đồng
        if ($room_id && $ngaylaphopdong && $ngayvao && $ngayra && $tinhtrangcoc && $ghichu && $sotiencoc && $dieukhoan1 && $dieukhoan2 && $dieukhoan3) {
            // Xử lý ngày tạo hợp đồng (create_at)
            $create_at = date('Y-m-d H:i:s'); // Thời gian tạo hợp đồng

            // Nếu hợp đồng đã có, thực hiện cập nhật
            if (isset($contract_id)) {
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

                // Cập nhật các dịch vụ nếu có thay đổi
                foreach ($services as $services_id) {
                    linkContractService($contract_id, $services_id);
                }
            } else {
                // Thêm hợp đồng mới nếu chưa có contract_id
                $contract_id = addContract($room_id, $ngaylaphopdong, $ngayvao, $ngayra, $tinhtrangcoc, $create_at, $ghichu, $sotiencoc, $dieukhoan1, $dieukhoan2, $dieukhoan3);

                // Thêm các dịch vụ vào bảng contract_services
                foreach ($services as $services_id) {
                    linkContractService($contract_id, $services_id);
                }
            }

            // Thêm các khách thuê vào cơ sở dữ liệu
            foreach ($tempCustomers as $customer) {
                $tenkhach = $customer['tenkhach'];
                $ngaysinh = $customer['ngaysinh'];
                $gioitinh = $customer['gioitinh'];
                $diachi = $customer['diachi'];
                $cmnd = $customer['cmnd'];

                // Thêm khách thuê vào bảng tenant và lấy tenant_id
                $tenant_id = addTenant($tenkhach, $ngaysinh, $gioitinh, $diachi, $room_id, $cmnd);

                // Liên kết hợp đồng với khách thuê trong bảng contract_tenant
                linkContractTenant($contract_id, $tenant_id);
            }

            // Thông báo thành công
            setFlashData('msg', 'Hợp đồng, khách thuê và dịch vụ đã được thêm/cập nhật thành công!');
            setFlashData('msg_type', 'suc');
            redirect('?module=contract'); // Chuyển hướng đến trang hợp đồng
        } else {
            // Thông báo lỗi khi thiếu thông tin
            setFlashData('msg', 'Thiếu thông tin cần thiết để thêm hoặc cập nhật hợp đồng.');
            setFlashData('msg_type', 'err');
            redirect('?module=contract&action=add'); // Chuyển hướng lại trang thêm hợp đồng
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

                                    // Định dạng ngày sinh nếu có giá trị
                                    if ($ngaysinh !== 'Chưa có ngày sinh') {
                                        $date = new DateTime($ngaysinh);
                                        $ngaysinh = $date->format('d/m/Y'); // Định dạng tùy ý, ví dụ: ngày-tháng-năm
                                    }

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
                    <button type="button" class="btn btn-secondary" onclick="openPopup()"> <i class="fa fa-plus"> </i>Thêm khách</button>
                </div>
                <!-- Popup -->
                <div id="popupForm" class="popup" style="display: none;">
                    <div class="popup-content">
                        <span class="close-btn" onclick="closePopup()">&times;</span>

                        <!-- Nội dung bên trong popup -->

                        <div class="form-group">
                            <label for="">Tên khách <span style="color: red">*</span></label>
                            <input type="text" placeholder="Tên khách thuê" name="tenkhach" id="" class="form-control" value="<?php echo old('tenkhach', $old); ?>">
                            <?php echo form_error('tenkhach', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Số CMND/CCCD <span style="color: red">*</span></label>
                            <input type="text" placeholder="Số CMND/CCCD" name="cmnd" id="" class="form-control" value="<?php echo old('cmnd', $old); ?>">
                            <?php echo form_error('cmnd', $errors, '<span class="error">', '</span>'); ?>
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
                    <div style="border: 1px solid #ccc; border-radius: 5px; padding: 10px; margin-top: 10px; height: 150px; background-color: #f9f9f9;">
                        <div id="tempCustomerInfo" style="color: green;"></div>
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
                <!-- Input ẩn để lưu danh sách khách thuê tạm -->

                <form id="contractForm" method="post" action="">
                    <input type="hidden" name="tempCustomersData" id="tempCustomersData">
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
            // Gửi yêu cầu kiểm tra CMND
            fetch('includes/check_cmnd.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cmnd: cmnd
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        alert('CMND/CCCD đã tồn tại trong cơ sở dữ liệu.');
                    } else {
                        // Thêm khách thuê vào danh sách tạm
                        tempCustomers.push({
                            tenkhach,
                            ngaysinh,
                            gioitinh,
                            diachi,
                            cmnd
                        });

                        // Hiển thị danh sách khách thuê tạm
                        document.getElementById('tempCustomerInfo').innerHTML += `
                <p>${tenkhach} - ${ngaysinh} - ${gioitinh} - ${diachi} - ${cmnd}</p>
                `;

                        // Reset form input
                        document.querySelector('[name="tenkhach"]').value = '';
                        document.querySelector('[name="ngaysinh"]').value = '';
                        document.querySelector('[name="gioitinh"]').value = '';
                        document.querySelector('[name="diachi"]').value = '';
                        document.querySelector('[name="cmnd"]').value = '';
                    }
                    updateTempCustomerList();
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                });
        } else {
            alert('Vui lòng nhập đầy đủ thông tin khách thuê.');
        }
    }


    function updateTempCustomerList() {
        const customerInfoList = tempCustomers.map((customer, index) => {
            // chuyển định dạng ngày hiển thị
            const date = new Date(customer.ngaysinh);
            const day = String(date.getDate()).padStart(2, '0'); // Lấy ngày và thêm 0 nếu cần
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Lấy tháng (lưu ý: tháng bắt đầu từ 0)
            const year = date.getFullYear(); // Lấy năm

            // Tạo định dạng tùy ý, ví dụ: tháng/ngày/năm
            const formattedDate = `${day}/${month}/${year}`;

            return `<div>
            Khách ${index + 1}: ${customer.tenkhach} - ${customer.cmnd} - ${formattedDate} - ${customer.gioitinh} - ${customer.diachi}
            <button onclick="removeTempCustomer(${index})">Xóa</button>
        </div>`;
        }).join('');

        document.getElementById('tempCustomerInfo').innerHTML = customerInfoList; // Cập nhật nội dung HTML
    }


    function removeTempCustomer(index) {
        // Xoá khách hàng tại vị trí index trong mảng
        tempCustomers.splice(index, 1);
        // Cập nhật lại danh sách hiển thị sau khi xoá
        updateTempCustomerList();
    }

    function openPopup() {
        document.getElementById('popupForm').style.display = 'flex';
    }

    function closePopup() {
        document.getElementById('popupForm').style.display = 'none';
    }
</script>
<?php
layout('footer', 'admin');
?>