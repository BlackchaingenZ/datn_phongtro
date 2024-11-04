<?php

if (!defined('_INCODE'))
    die('Access denied...');


$data = [
    'pageTitle' => 'Thêm hợp đồng mới'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// include 'includes/add_contracts.php';
// kiểm tra nếu phòng nào có hợp đồng rồi thì không hiện
$allRoom = getRaw("
    SELECT room.id, room.tenphong, room.soluong 
    FROM room 
    WHERE room.id NOT IN (SELECT room_id FROM contract)
    ORDER BY room.tenphong
");

$allServices = getRaw("SELECT * FROM services ORDER BY tendichvu ASC");
$allArea = getRaw("SELECT id, tenkhuvuc FROM area ORDER BY tenkhuvuc");
$allRoomId = getRaw("SELECT room_id FROM contract");

// Phân loại phòng theo khu vực
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

if (!empty($_POST['services'])) {
    $services = implode(',', $_POST['services']); // Chuyển đổi mảng dịch vụ thành chuỗi
    // Thêm mã thêm dịch vụ vào cơ sở dữ liệu (cần có xử lý cho phần này)
}

// Lấy thông tin flash message
$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu hợp đồng từ POST
    $room_id = $_POST['room_id'] ?? null;
    $ngaylaphopdong = $_POST['ngaylaphopdong'] ?? null;
    $ngayvao = $_POST['ngayvao'] ?? null;
    $ngayra = $_POST['ngayra'] ?? null;
    $tinhtrangcoc = $_POST['tinhtrangcoc'] ?? null;
    $create_at = date("Y-m-d H:i:s") ?? null;
    $ghichu = $_POST['ghichu'] ?? null;

    // Lấy danh sách dịch vụ từ POST
    $services = $_POST['services'] ?? []; // Danh sách dịch vụ được gửi từ form

    // Giải mã danh sách khách thuê tạm từ JSON
    $tempCustomersData = $_POST['tempCustomersData'] ?? '[]';
    $tempCustomers = json_decode($tempCustomersData, true);

    if ($room_id && $ngaylaphopdong && $ngayvao && $ngayra && $tinhtrangcoc && $create_at && $ghichu) {
        // Thêm hợp đồng
        $contract_id = addContract($room_id, $ngaylaphopdong, $ngayvao, $ngayra, $tinhtrangcoc, $create_at, $ghichu);

        // Thêm từng dịch vụ vào bảng contract_services
        foreach ($services as $services_id) {
            linkContractService($contract_id, $services_id);
        }

        // Thêm từng khách thuê từ danh sách tạm vào cơ sở dữ liệu
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
        setFlashData('msg', 'Hợp đồng, khách thuê và dịch vụ đã được thêm thành công!');
        setFlashData('msg_type', 'suc');
        redirect('?module=contract'); // Chuyển hướng đến trang hợp đồng
    } else {
        // Thông báo lỗi khi thiếu thông tin
        setFlashData('msg', 'Thiếu thông tin cần thiết để thêm hợp đồng.');
        setFlashData('msg_type', 'err');
        redirect('?module=contract&action=add'); // Chuyển hướng lại trang thêm hợp đồng
    }
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
        <form id="contractForm" action="" method="post" class="row">
            <div class="col-4">
                <label for="">Danh sách khách vừa tạo</label>
                <div class="form-group">
                    <!-- Khu vực để hiển thị danh sách khách tạm thời -->
                    <div style="border: 1px solid #ccc; border-radius: 5px; padding: 10px; margin-top: 10px; height: 450px; background-color: #f9f9f9;">
                        <div id="tempCustomerInfo" style="color: green;"></div>
                    </div>

                </div>
            </div>
            <div class="col-4">
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

                <form id="contractForm" action="add_contracts.php" method="post">
                    <div class="form-group">
                        <label for="">Ngày lập hợp đồng <span style="color: red">*</span></label>
                        <input type="date" name="ngaylaphopdong" id="" class="form-control"
                            value="<?php echo old('ngaylaphopdong', $old); ?>">
                        <?php echo form_error('ngaylaphopdong', $errors, '<span class="error">', '</span>'); ?>
                    </div>
                    <div class="form-group">
                        <label for="">Ngày vào ở <span style="color: red">*</span></label>
                        <input type="date" name="ngayvao" id="" class="form-control"
                            value="<?php echo old('ngayvao', $old); ?>">
                        <?php echo form_error('ngayvao', $errors, '<span class="error">', '</span>'); ?>
                    </div>
            </div>

            <div class="col-4">
                <div class="form-group">
                    <label for="">Ngày hết hạn hợp đồng <span style="color: red">*</span></label>
                    <input type="date" name="ngayra" id="" class="form-control"
                        value="<?php echo old('ngayra', $old); ?>">
                    <?php echo form_error('ngayra', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Tình trạng cọc<span style="color: red">*</label>
                    <select name="tinhtrangcoc" class="form-select">
                        <option value="" disabled selected>Chọn trạng thái</option>
                        <option value="0">Chưa thu tiền</option>
                        <option value="1">Đã thu tiền</option>
                    </select>
                </div>
                <!-- Phần chọn dịch vụ -->
                <div class="form-group">
                    <label for="">Chọn dịch vụ <span style="color: red">*</span></label>
                    <div class="checkbox-container">
                        <?php foreach ($allServices as $service) { ?>
                            <div class="checkbox-item">
                                <input type="checkbox" name="services[]" id="service-<?php echo htmlspecialchars($service['id'], ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($service['id'], ENT_QUOTES, 'UTF-8'); ?>" required>
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

                <div class="form-group">
                    <label for="">Ghi chú<span style="color: red">*</label>
                    <input type="text" placeholder="" name="ghichu" class="form-control" value="<?php echo old('ghichu', $old); ?>" style="width: 100%;height:100px">
                    <?php echo form_error('ghichu', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <!-- Input ẩn để lưu danh sách khách thuê tạm -->

                <form id="contractForm" method="post" action="">
                    <input type="hidden" name="tempCustomersData" id="tempCustomersData">
                    <!-- Các input khác -->
                    <a style="margin-right: 20px" href="<?php echo getLinkAdmin('contract') ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-circle-left"></i> Quay lại
                    </a>
                    <button type="button" class="btn btn-secondary" onclick="submitFormWithTempCustomers()">
                        <i class="fa fa-plus"></i> Thêm hợp đồng
                    </button>
                </form>

        </form>
    </div>

    <!-- <div id="tempCustomerInfo"></div> -->
    </form>

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
    function addTempCustomersToForm() {
        document.getElementById('customers_data').value = JSON.stringify(tempCustomers);
    }
</script>
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
<script>
    let tempCustomers = [];

    function addTempCustomer() {
        const tenkhach = document.querySelector('[name="tenkhach"]').value;
        const ngaysinh = document.querySelector('[name="ngaysinh"]').value;
        const gioitinh = document.querySelector('[name="gioitinh"]').value;
        const diachi = document.querySelector('[name="diachi"]').value;
        const cmnd = document.querySelector('[name="cmnd"]').value;

        if (tenkhach && ngaysinh && gioitinh && diachi && cmnd) {
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
        const customerInfoList = tempCustomers.map((customer, index) =>
            `<div>
            Khách ${index + 1}: ${customer.tenkhach}-${customer.cmnd}-${customer.ngaysinh}-${customer.gioitinh}-${customer.diachi}
            <button onclick="removeTempCustomer(${index})">Xóa</button>
        </div>`
        ).join('');

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