<?php

if (!defined('_INCODE'))
    die('Access denied...');


$data = [
    'pageTitle' => 'Thêm hợp đồng mới'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// include 'includes/add_contracts.php';
// kiểm tra nếu phòng nào có người rồi thì không hiện
$allRoom = getRaw("
    SELECT room.id, room.tenphong, room.soluong 
    FROM room 
    WHERE room.soluong = 0
    ORDER BY room.tenphong
");
$allServices = getRaw("SELECT id, tendichvu, giadichvu, donvitinh FROM services ORDER BY tendichvu ASC");

$allRoomId = getRaw("SELECT room_id FROM contract");
$allArea = getRaw("SELECT id, tenkhuvuc FROM area ORDER BY tenkhuvuc");
// Phân loại phòng theo khu vực
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
    // Gán create_at = ngaylaphopdong
    $create_at = $ngaylaphopdong ?? date("Y-m-d H:i:s");
    $ghichu = $_POST['ghichu'] ?? Null;
    $trangthaihopdong = $_POST['trangthaihopdong'] ?? null; // chưa thanh lý
    if (empty(trim($ghichu))) {
        $ghichu = 'Bỏ trống';
    }
    $sotiencoc = $_POST['sotiencoc'] ?? null;
    // Nếu `dieukhoan1` trống, gán giá trị mặc định
    $dieukhoan1 = $_POST['dieukhoan1'] ?? 'Sử dụng phòng đúng mục đích đã thoả thuận, Đảm bảo các thiết bị và sửa chữa các hư hỏng trong phòng trong khi sử dụng. Nếu không sửa chữa thì khi trả phòng, bên A sẽ trừ vào tiền đặt cọc, giá trị cụ thể được tính theo giá thị trường.';
    if (empty(trim($dieukhoan1))) {
        $dieukhoan1 = 'Sử dụng phòng đúng mục đích đã thoả thuận, Đảm bảo các thiết bị và sửa chữa các hư hỏng trong phòng trong khi sử dụng. Nếu không sửa chữa thì khi trả phòng, bên A sẽ trừ vào tiền đặt cọc, giá trị cụ thể được tính theo giá thị trường. ';
    }
    $dieukhoan2 = $_POST['dieukhoan2'] ?? 'Trả đủ tiền thuê phòng đúng kỳ hạn đã thỏa thuận, Chỉ sử dụng phòng trọ vào mục đích ở, không chứa các thiết bị gây cháy nổ, hàng cấm... cung cấp giấy tờ tùy thân để đăng ký tạm trú theo quy định, giữ gìn an ninh trật tự, nếp sống văn hóa đô thị; không tụ tập nhậu nhẹt, cờ bạc và các hành vi vi phạm pháp luật khác.';
    if (empty(trim($dieukhoan2))) {
        $dieukhoan2 = 'Trả đủ tiền thuê phòng đúng kỳ hạn đã thỏa thuận ,Chỉ sử dụng phòng trọ vào mục đích ở, không chứa các thiết bị gây cháy nổ, hàng cấm... cung cấp giấy tờ tùy thân để đăng ký tạm trú theo quy định, giữ gìn an ninh trật tự, nếp sống văn hóa đô thị; không tụ tập nhậu nhẹt, cờ bạc và các hành vi vi phạm pháp luật khác.';
    }
    $dieukhoan3 = $_POST['dieukhoan3'] ?? 'Tôn trọng quy tắc sinh hoạt công cộng, Không được tự ý cải tạo kiếm trúc phòng hoặc trang trí ảnh hưởng tới tường, cột, nền... Nếu có nhu cầu trên phải trao đổi với bên A để được thống nhất';
    if (empty(trim($dieukhoan3))) {
        $dieukhoan3 = 'Tôn trọng quy tắc sinh hoạt công cộng, Không được tự ý cải tạo kiếm trúc phòng hoặc trang trí ảnh hưởng tới tường, cột, nền... Nếu có nhu cầu trên phải trao đổi với bên A để được thống nhất';
    }

    // Lấy danh sách dịch vụ từ POST
    $services = $_POST['services'] ?? []; // Danh sách dịch vụ được gửi từ form

    // Giải mã danh sách khách thuê tạm từ JSON
    $tempCustomersData = $_POST['tempCustomersData'] ?? '[]';
    $tempCustomers = json_decode($tempCustomersData, true);

    // Kiểm tra nếu chưa nhận được khách thuê tạm thời
    if (empty($tempCustomers)) {
        setFlashData('msg', 'Bạn chưa nhập thông tin khách thuê!.');
        setFlashData('msg_type', 'err');
        redirect('?module=contract&action=add'); // Chuyển hướng lại trang thêm hợp đồng
        exit; // Ngừng xử lý
    }

    if ($room_id && $ngaylaphopdong && $ngayvao && $ngayra && $tinhtrangcoc && $create_at && $ghichu && $sotiencoc && $dieukhoan1 && $dieukhoan2 && $dieukhoan3) {
        // Lấy số lượng hiện tại và số lượng tối đa của phòng
        $roomCapacity = checkSoluongtoida($room_id); // Hàm trả về ['soluong' => ..., 'soluongtoida' => ...]
        $currentCapacity = $roomCapacity['soluong'];
        $maxCapacity = $roomCapacity['soluongtoida'];

        // Kiểm tra nếu tổng số khách sau khi thêm vượt quá sức chứa tối đa
        if ($currentCapacity + count($tempCustomers) > $maxCapacity) {
            setFlashData('msg', 'Phòng không đủ chỗ trống!');
            setFlashData('msg_type', 'err');
            redirect('?module=contract'); // Chuyển hướng về trang hợp đồng
        }
        // Thêm hợp đồng
        $contract_id = addContract($room_id, $ngaylaphopdong, $ngayvao, $ngayra, $tinhtrangcoc, $create_at, $ghichu, $sotiencoc, $dieukhoan1, $dieukhoan2, $dieukhoan3);

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

            // Kiểm tra nếu khách thuê đã tồn tại dựa trên CMND
            $tenant_id = getTenantIdByCmnd($cmnd); // Hàm này sẽ trả về tenant_id nếu tồn tại, nếu không sẽ trả về null

            if ($tenant_id) {
                // Nếu khách thuê đã tồn tại, chỉ cập nhật phòng mới mà không thay đổi phòng cũ
                // Kiểm tra và cập nhật room_id (không thay đổi phòng cũ nếu khách đã có phòng)
                $existingTenantRoom = getTenantRoomById($tenant_id); // Hàm lấy room_id của khách thuê hiện tại
                if ($existingTenantRoom != $room_id) {
                    updateTenantRoom($tenant_id, $room_id); // Cập nhật room_id cho khách thuê mà không xóa phòng cũ
                    $contract_id = addContract($room_id, $ngaylaphopdong, $ngayvao, $ngayra, $tinhtrangcoc, $create_at, $ghichu, $sotiencoc, $dieukhoan1, $dieukhoan2, $dieukhoan3);
                    foreach ($services as $services_id) {
                        linkContractService($contract_id, $services_id);
                    }
                }
            } else {
                // Nếu khách thuê chưa tồn tại, thêm vào bảng tenant và lấy tenant_id mới
                $tenant_id = addTenant($tenkhach, $ngaysinh, $gioitinh, $diachi, $room_id, $cmnd);
            }

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
            <div class="col-3">
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
                    <button type="button" class="btn btn-secondary" onclick="openPopup()"> <i class="fa fa-plus">
                        </i>Thêm khách</button>
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
                            <input type="text" placeholder="Tên khách thuê" name="tenkhach" id="" class="form-control"
                                value="<?php echo old('tenkhach', $old); ?>">
                            <?php echo form_error('tenkhach', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Ngày sinh <span style="color: red">*</span></label>
                            <input type="date" name="ngaysinh" id="" class="form-control"
                                value="<?php echo old('ngaysinh', $old); ?>">
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
                            <input type="text" placeholder="Địa chỉ" name="diachi" id="" class="form-control"
                                value="<?php echo old('diachi', $old); ?>">
                            <?php echo form_error('diachi', $errors, '<span class="error">', '</span>'); ?>
                        </div>
                        <input type="hidden" name="customer_id" id="customer_id">


                        <div class="col-12">
                            <button type="button" class="btn btn-secondary" onclick="closePopup()"> <i
                                    class="fa fa-edit"> </i> Đóng </button>
                            <button type="button" class="btn btn-secondary" onclick="addTempCustomer()">
                                <i class="fa fa-plus"></i> Thêm khách
                            </button>
                        </div>
                    </div>
                </div>
                <label for="">Danh sách khách vừa tạo</label>
                <div class="form-group">
                    <div
                        style="background-color: white; border: 0.5px solid #ccc; padding: 5px; border-radius: 5px; height: 190px; display: flex; flex-direction: column;">
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

                <form id="contractForm" action="" method="post">
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
                            <!-- <option value="2">Chưa thu</option> -->
                            <option value="1">Đã thu</option>
                        </select>
                    </div>
                    <div class="form-group" hidden>
                        <label for="">Trạng thái hợp đồng<span style="color: red">*</label>
                        <select name="trangthaihopdong" class="form-select">
                            <option value="1" selected>Chưa thanh lý</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">Số tiền cọc <span style="color: red">*</span></label>
                        <input type="text" placeholder="Nhập số tiền" name="sotiencoc" id="sotiencoc"
                            class="form-control" value="<?php echo old('sotiencoc', $old); ?>" inputmode="decimal"
                            oninput="validateNumber(this)">
                        <?php echo form_error('sotiencoc', $errors, '<span class="error">', '</span>'); ?>
                    </div>
                    <script>
                        // Hàm kiểm tra chỉ cho phép nhập số
                        function validateNumber(input) {
                            input.value = input.value.replace(/[^0-9\.]/g, ''); // Loại bỏ ký tự không phải số
                        }
                    </script>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="">Ghi chú<span style="color: red">*</span></label>
                    <textarea name="ghichu" class="form-control" rows="4"
                        style="width: 100%; height: 75px;"><?php echo htmlspecialchars(old('ghichu', $old) ?? 'Bỏ trống'); ?></textarea>
                    <?php echo form_error('ghichu', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <div class="form-group">
                    <label for=""> Điều khoản 1<span style="color: red">*</span></label>
                    <textarea name="dieukhoan1" class="form-control" rows="4"
                        style="width: 100%; height: 75px;"><?php echo htmlspecialchars(old('dieukhoan1', $old) ?? 'Sử dụng phòng đúng mục đích đã thoả thuận, Đảm bảo các thiết bị và sửa chữa các hư hỏng trong phòng trong khi sử dụng. Nếu không sửa chữa thì khi trả phòng, bên A sẽ trừ vào tiền đặt cọc, giá trị cụ thể được tính theo giá thị trường.'); ?></textarea>
                    <?php echo form_error('dieukhoan1', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <div class="form-group">
                    <label for=""> Điều khoản 2<span style="color: red">*</span></label>
                    <textarea name="dieukhoan2" class="form-control" rows="4"
                        style="width: 100%; height: 75px;"><?php echo htmlspecialchars(old('dieukhoan2', $old) ?? 'Trả đủ tiền thuê phòng đúng kỳ hạn đã thỏa thuận, Chỉ sử dụng phòng trọ vào mục đích ở, không chứa các thiết bị gây cháy nổ, hàng cấm... cung cấp giấy tờ tùy thân để đăng ký tạm trú theo quy định, giữ gìn an ninh trật tự, nếp sống văn hóa đô thị; không tụ tập nhậu nhẹt, cờ bạc và các hành vi vi phạm pháp luật khác.'); ?></textarea>
                    <?php echo form_error('dieukhoan2', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <div class="form-group">
                    <label for=""> Điều khoản 3<span style="color: red">*</span></label>
                    <textarea name="dieukhoan3" class="form-control" rows="4"
                        style="width: 100%; height: 75px;"><?php echo htmlspecialchars(old('dieukhoan3', $old) ?? 'Tôn trọng quy tắc sinh hoạt công cộng, Không được tự ý cải tạo kiếm trúc phòng hoặc trang trí ảnh hưởng tới tường, cột, nền... Nếu có nhu cầu trên phải trao đổi với bên A để được thống nhất'); ?></textarea>

                    <?php echo form_error('dieukhoan3', $errors, '<span class="error">', '</span>'); ?>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="">Dịch vụ sử dụng <span style="color: red">*</span></label>
                    <div class="checkbox-container">
                        <?php foreach ($allServices as $service) { ?>
                            <div class="checkbox-item">
                                <input type="checkbox" name="services[]"
                                    id="service-<?php echo $service['id']; ?>"
                                    value="<?php echo $service['id']; ?>" required>
                                <label for="service-<?php echo $service['id']; ?>">
                                    <option value="<?php echo $service['id']; ?>">
                                        <?php echo $service['tendichvu'] . ' - Giá: ' . number_format($service['giadichvu']) . ' VND/' . $service['donvitinh']; ?>
                                    </option>

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
                <form id="contractForm" method="post" action="">
                    <!-- Input ẩn để lưu danh sách khách thuê tạm -->
                    <input type="hidden" name="tempCustomersData" id="tempCustomersData">
                    <!-- Các input khác -->
                    <a style="margin-right: 10px" href="<?php echo getLinkAdmin('contract') ?>"
                        class="btn btn-secondary">
                        <i class="fa fa-arrow-circle-left"></i> Quay lại
                    </a>
                    <button type="button" class="btn btn-secondary" onclick="submitFormWithTempCustomers()">
                        <i class="fa fa-plus"></i> Thêm mới
                    </button>
                </form>
            </div>
        </form>
    </div>

</div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function submitFormWithTempCustomers() {
            const tempCustomersDataElem = document.getElementById('tempCustomersData');
            const contractFormElem = document.getElementById(
                'contractForm'); // Đảm bảo phần tử này có mặt trong HTML

            if (tempCustomersDataElem && contractFormElem) {
                tempCustomersDataElem.value = JSON.stringify(
                    tempCustomers); // Đảm bảo `tempCustomers` đã được khai báo và có dữ liệu
                contractFormElem.submit();
            } else {
                console.error("Không tìm thấy phần tử 'tempCustomersData' hoặc 'contractForm'.");
            }
        }

        // Gán sự kiện click cho nút
        document.querySelector('.btn.btn-secondary[onclick="submitFormWithTempCustomers()"]').onclick =
            submitFormWithTempCustomers;
    });
</script>

<script>
    // lọc phòng theo khu vực
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
                option.textContent =
                    `${room.tenphong}`; // Hiển thị tên phòng 
                roomSelect.appendChild(option);
            });
        }
    });
</script>


<script>
    function addTempCustomersToForm() {
        document.getElementById('customers_data').value = JSON.stringify(tempCustomers);
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
                        // Thiết lập các trường làm readonly để không thể chỉnh sửa
                        document.querySelector('[name="tenkhach"]').setAttribute('readonly', true);
                        document.querySelector('[name="gioitinh"]').setAttribute('disabled', true);
                        document.querySelector('[name="diachi"]').setAttribute('readonly', true);
                        document.querySelector('[name="ngaysinh"]').setAttribute('readonly', true);
                    } else {

                        // Nếu CMND/CCCD không tồn tại trong cơ sở dữ liệu, làm sạch form
                        document.querySelector('[name="tenkhach"]').value = '';
                        document.querySelector('[name="gioitinh"]').value = '';
                        document.querySelector('[name="diachi"]').value = '';
                        document.querySelector('[name="ngaysinh"]').value = '';
                        document.querySelector('[name="customer_id"]').value = '';
                        // Loại bỏ readonly để người dùng có thể nhập lại
                        document.querySelector('[name="tenkhach"]').removeAttribute('readonly');
                        document.querySelector('[name="gioitinh"]').removeAttribute('disabled');
                        document.querySelector('[name="diachi"]').removeAttribute('readonly');
                        document.querySelector('[name="ngaysinh"]').removeAttribute('readonly');
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
            alert('Bạn đã thêm thành công!.');
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