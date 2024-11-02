<?php

if (!defined('_INCODE'))
    die('Access denied...');


$data = [
    'pageTitle' => 'Thêm hợp đồng mới'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

include 'includes/add_contract.php';
// kiểm tra nếu phòng nào có hợp đồng rồi thì không hiện
$allRoom = getRaw("
    SELECT room.id, room.tenphong, room.soluong 
    FROM room 
    LEFT JOIN contract ON contract.room_id = room.id
    WHERE contract.id IS NULL
    ORDER BY room.tenphong
");

$allServices = getRaw("SELECT * FROM services ORDER BY tendichvu ASC");
$allArea = getRaw("SELECT id, tenkhuvuc FROM area ORDER BY tenkhuvuc");
$allRoomId = getRaw("SELECT room_id FROM contract");
//phân loại phòng theo khu vực
$roomsByArea = [];
foreach ($allRoom as $room) {
    // Giả sử bạn có một cách để lấy số người hiện tại trong phòng, có thể từ cơ sở dữ liệu
    $soluong = getRaw("SELECT COUNT(*) AS soluong FROM room WHERE id = " . $room['id'])[0]['soluong']; // Hoặc sử dụng cách khác nếu có thông tin

    $areaIds = getRaw("SELECT area_id FROM area_room WHERE room_id = " . $room['id']);
    foreach ($areaIds as $area) {
        // Thêm thông tin số người vào mỗi phòng
        $roomsByArea[$area['area_id']][] = [
            'id' => $room['id'],
            'tenphong' => $room['tenphong'], // Giả sử bạn có trường này trong $room
            'soluong' => $soluong
        ];
    }
}


if (isPost()) {
    // Validate form
    $body = getBody();
    $errors = []; // mảng lưu trữ các lỗi

    // Validate room_id: Bắt buộc phải nhập
    if (empty(trim($body['room_id']))) {
        $errors['room_id']['required'] = '** Bạn chưa chọn phòng lập hợp đồng!';
    } else {
        $dataRoom = trim($body['room_id']);
        foreach ($allRoomId as $item) {
            if ($dataRoom == $item['room_id']) {
                $errors['room_id']['exists'] = '** Phòng này đã lập hợp đồng';
                break;
            }
        }
    }

    // Kiểm tra ngày lập hợp đồng
    if (empty(trim($body['ngaylaphopdong']))) {
        $errors['ngaylaphopdong']['required'] = '** Bạn chưa nhập ngày lập hợp đồng!';
    }

    // Kiểm tra dịch vụ
    $tendichvuId = !empty($_POST['tendichvu']) ? array_map('trim', $_POST['tendichvu']) : [];
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
        <form action="" method="post" class="row">
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

                        <div class="form-group">
                            <label for="">Số CMND/CCCD <span style="color: red">*</span></label>
                            <input type="text" placeholder="Số CMND/CCCD" name="cmnd" id="" class="form-control" value="<?php echo old('cmnd', $old); ?>">
                            <?php echo form_error('cmnd', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="col-12">
                            <button type="button" class="btn btn-secondary" onclick="closePopup()"> <i class="fa fa-edit"> </i> Đóng </button>
                            <button type="button" class="btn btn-secondary" onclick="addTempCustomer()">
                                <i class="fa fa-plus"></i> Thêm khách
                            </button>

                        </div>
                    </div>
                </div>
                <div>
                    <!-- Khu vực để hiển thị danh sách khách tạm thời -->
                    <div id="tempCustomerInfo" style="margin-top: 10px; color: green;"></div>
                </div>

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

            <div class="col-5">

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

                <?php
                // Khởi tạo biến $tendichvuId nếu chưa được xác định
                $tendichvuId = isset($tendichvuId) ? $tendichvuId : [];
                ?>
                <div class="form-group">
                    <label for="">Dịch vụ sử dụng <span style="color: red">*</span></label>
                    <div class="checkbox-container">
                        <!-- Checkbox "Chọn tất cả" -->
                        <div class="checkbox-item">
                            <input type="checkbox" id="select-all">
                            <label for="select-all">Chọn tất cả</label>
                        </div>

                        <?php
                        if (!empty($allServices)) {
                            foreach ($allServices as $item) {
                        ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" class="service-checkbox" name="tendichvu[]" value="<?php echo $item['id']; ?>"
                                        <?php echo (in_array($item['id'], (array)$tendichvuId)) ? 'checked' : ''; ?>>
                                    <?php echo $item['tendichvu']; ?>
                                </div>
                        <?php
                            }
                        }
                        ?>
                    </div>
                    <?php echo form_error('tendichvu', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <script>
                    document.getElementById('select-all').addEventListener('change', function() {
                        const checkboxes = document.querySelectorAll('.service-checkbox');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                    });
                </script>


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
                option.textContent = `${room.tenphong} (${room.soluong} người)`; // Hiển thị tên phòng và số người
                roomSelect.appendChild(option);
            });
        }
    });
</script>
<script>
    let tempCustomers = []; // Mảng lưu thông tin nhiều khách hàng tạm thời

    function addTempCustomer() {
        // Lấy thông tin từ popup
        const tenkhach = document.querySelector('input[name="tenkhach"]').value;
        const cmnd = document.querySelector('input[name="cmnd"]').value;
        const ngaysinh = document.querySelector('input[name="ngaysinh"]').value;
        const gioitinh = document.querySelector('select[name="gioitinh"]').value;
        const diachi = document.querySelector('input[name="diachi"]').value;

        // Lấy room_id từ select
        const roomId = document.querySelector('select[name="room_id"]').value; // Lấy giá trị từ select

        // Kiểm tra xem có trường nào bị bỏ trống không
        if (!tenkhach || !cmnd || !ngaysinh || !gioitinh || !diachi || !roomId) {
            alert("Vui lòng điền đầy đủ thông tin.");
            return;
        }

        // Kiểm tra khách hàng đã tồn tại dựa trên số CMND/CCCD
        const isCustomerExists = tempCustomers.some(customer => customer.cmnd === cmnd);
        if (isCustomerExists) {
            alert("Khách hàng này đã được thêm trước đó.");
            return;
        }

        // Tạo đối tượng khách hàng mới và thêm vào mảng
        const newCustomer = {
            tenkhach,
            cmnd,
            ngaysinh,
            gioitinh,
            diachi,
            roomId // Thêm room_id vào đối tượng khách hàng mới
        };
        tempCustomers.push(newCustomer);

        // Cập nhật danh sách khách hàng trên form chính
        updateTempCustomerList();

        // Đóng popup
        closePopup();
    }

    function updateTempCustomerList() {
        const customerInfoList = tempCustomers.map((customer, index) =>
            <div>
            Khách ${index + 1}: ${customer.tenkhach}, CMND/CCCD: ${customer.cmnd}, Ngày sinh: ${customer.ngaysinh}, Giới tính: ${customer.gioitinh}, Địa chỉ: ${customer.diachi}, Room ID: ${customer.roomId}
        </div>
        ).join('');

        document.getElementById('tempCustomerInfo').innerHTML = customerInfoList; // Cập nhật nội dung HTML
        // Gửi danh sách khách hàng vào cơ sở dữ liệu
        sendCustomersToDatabase(tempCustomers);
    }

    function sendCustomersToDatabase(customers) {
        fetch('includes/add_customer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(customers) // Chuyển đổi mảng khách hàng thành chuỗi JSON
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json(); // Chuyển đổi phản hồi từ server thành JSON
            })
            .then(data => {
                console.log('Dữ liệu đã được gửi thành công:', data);
                // Thực hiện các hành động cần thiết sau khi gửi thành công
            })
            .catch(error => {
                console.error('Có lỗi xảy ra khi gửi dữ liệu:', error);
            });
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
