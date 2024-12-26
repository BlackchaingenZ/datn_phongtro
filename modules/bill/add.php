<?php

if (!defined('_INCODE'))
    die('Access denied...');

$data = [
    'pageTitle' => 'Thêm hóa đơn mới'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Dịch vụ
$donGiaNuoc = firstRaw("SELECT giadichvu FROM services WHERE tendichvu = 'Tiền nước'");
$dongiaDien = firstRaw("SELECT giadichvu FROM services WHERE tendichvu = 'Tiền điện'");
$dongiaRac = firstRaw("SELECT giadichvu FROM services WHERE tendichvu = 'Tiền rác'");
$dongiaWifi = firstRaw("SELECT giadichvu FROM services WHERE tendichvu = 'Tiền Wifi'");

$allRoom = getRaw("
    SELECT 
       room.id, 
        tenphong, 
        cost.giathue, 
        soluong,  
        room.ngayvao 
    FROM room 
    INNER JOIN contract ON contract.room_id = room.id
    INNER JOIN cost_room ON cost_room.room_id = room.id
    INNER JOIN cost ON cost.id = cost_room.cost_id
    WHERE contract.trangthaihopdong = 1
    ORDER BY tenphong
");

$allArea = getRaw("SELECT id, tenkhuvuc FROM area ORDER BY tenkhuvuc");
$roomsByArea = [];

foreach ($allRoom as $room) {
    // Lấy số lượng người hiện tại trong phòng từ bảng tenant co phòng nhưng trangthaihopdong=1(chưa thanh lý)
    $soluong = getRaw("
    SELECT COUNT(*) AS soluong
    FROM tenant t
    JOIN contract_tenant ct ON t.id = ct.tenant_id_1
    JOIN contract c ON ct.contract_id_1 = c.id
    WHERE t.room_id = " . $room['id'] . " AND c.trangthaihopdong = 1
")[0]['soluong'];


    // Truy vấn để lấy giá thuê từ bảng cost_room và cost
    $costData = getRaw(
        "
        SELECT c.giathue
        FROM cost_room cr
        JOIN cost c ON cr.cost_id = c.id
        WHERE cr.room_id = " . $room['id']
    );

    // Kiểm tra nếu có giá thuê, nếu không thì gán giá mặc định là 0
    $giaPhong = isset($costData[0]['giathue']) ? $costData[0]['giathue'] : 0;

    // Lấy thông tin khu vực của phòng
    $areaIds = getRaw("SELECT area_id FROM area_room WHERE room_id = " . $room['id']);
    foreach ($areaIds as $area) {
        // Thêm thông tin số người và giá thuê vào mỗi phòng theo khu vực
        $roomsByArea[$area['area_id']][] = [
            'id' => $room['id'],
            'tenphong' => $room['tenphong'],
            'soluong' => $soluong,
            'giathue' => $giaPhong, // Thêm giá thuê vào mảng
        ];
    }
}

// Xử lý thêm người dùng
if (isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi
    if (empty(trim($body['room_id']))) {
        $errors['room_id']['required'] = 'Bạn chưa chọn phòng!';
    }
    if (empty(trim($body['thang']))) {
        $errors['thang']['required'] = 'Bạn chưa chọn tháng!';
    }
    if (empty(trim($body['create_at']))) {
        $errors['create_at']['required'] = 'Bạn chưa chọn ngày!';
    }
    // Kiểm tra mảng error
    if (empty($errors)) {
        // không có lỗi nào
        // Xử lý giá trị cho các trường cần kiểu dữ liệu số
        $tienphong = isset($body['tienphong']) ? str_replace(',', '', $body['tienphong']) : 0;  // Loại bỏ dấu phẩy trong giá trị tiền phòng
        $tienphong = floatval($tienphong);  // Chuyển sang kiểu số thực (float)

        $tiendien = isset($body['tiendien']) ? str_replace(',', '', $body['tiendien']) : 0;
        $tiendien = floatval($tiendien);

        $tiennuoc = isset($body['tiennuoc']) ? str_replace(',', '', $body['tiennuoc']) : 0;
        $tiennuoc = floatval($tiennuoc);

        $tienrac = isset($body['tienrac']) ? str_replace(',', '', $body['tienrac']) : 0;
        $tienrac = floatval($tienrac);

        $tienmang = isset($body['tienmang']) ? str_replace(',', '', $body['tienmang']) : 0;
        $tienmang = floatval($tienmang);

        $tongtien = isset($body['tongtien']) ? str_replace(',', '', $body['tongtien']) : 0;
        $tongtien = floatval($tongtien);

        $sotienconthieu = isset($body['tongtien']) ? str_replace(',', '', $body['tongtien']) : 0;
        $sotienconthieu = floatval($sotienconthieu);

        // Kiểm tra các giá trị chuỗi và số khác, ví dụ: room_id, mahoadon, v.v.
        $dataInsert = [
            'room_id' => isset($body['room_id']) ? $body['room_id'] : 0,
            'mahoadon' => generateInvoiceCode(),
            'tienphong' => $tienphong,
            'sodiencu' => isset($body['sodiencu']) ? $body['sodiencu'] : 0,
            'sodienmoi' => isset($body['sodienmoi']) ? $body['sodienmoi'] : 0,
            'img_sodienmoi' => isset($body['img_sodienmoi']) ? $body['img_sodienmoi'] : '',
            'tiendien' => $tiendien,
            'sonuoccu' => isset($body['sonuoccu']) ? $body['sonuoccu'] : 0,
            'sonuocmoi' => isset($body['sonuocmoi']) ? $body['sonuocmoi'] : 0,
            'img_sonuocmoi' => isset($body['img_sonuocmoi']) ? $body['img_sonuocmoi'] : '',
            'tiennuoc' => $tiennuoc,
            'songuoi' => isset($body['soluong']) ? $body['soluong'] : 0,
            'tienrac' => $tienrac,
            'tienmang' => $tienmang,
            'tongtien' => $tongtien,
            'sotienconthieu' => $sotienconthieu,
            // 'create_at' => date('Y-m-d H:i:s'),  // Lấy thời gian hiện tại
            'trangthaihoadon' => isset($body['trangthaihoadon']) ? $body['trangthaihoadon'] : '',
            'thang' => isset($body['thang']) ? $body['thang'] : '',
            'create_at' => isset($body['create_at']) ? $body['create_at'] : '',
        ];


        $insertStatus = insert('bill', $dataInsert);
        if ($insertStatus) {
            setFlashData('msg', 'Thêm thông tin hóa đơn thành công');
            setFlashData('msg_type', 'suc');
            redirect('?module=bill&action=bills');
        } else {
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
            redirect('?module=bill&action=add');
        }
    } else {
        // Có lỗi xảy ra
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);  // giữ lại các trường dữ liệu hợp lê khi nhập vào
        redirect('?module=bill&action=add');
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

    <div class="box-content2">
        <form action="" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

            <!-- hàng 1 -->
            <div class="row">
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
                        <label for="">Chọn phòng lập hoá đơn <span style="color: red">*</span></label>
                        <select name="room_id" id="room-select" class="form-select">
                            <option value="" disabled selected>Chọn phòng</option>
                            <!-- Danh sách phòng sẽ được cập nhật qua JavaScript -->
                        </select>
                        <?php echo form_error('room_id', $errors, '<span class="error">', '</span>'); ?>
                    </div>
                </div>
            </div>

            <!-- Hàng 2 -->
            <div class="row">
                <div class="col-3">
                    <div class="form-group">
                        <label for="thang">Chọn tháng<span style="color: red">*</span></label>
                        <select name="thang" id="thang" class="form-select">
                            <option value="" disabled selected>Chọn tháng</option>
                            <option value="1">Tháng 1</option>
                            <option value="2">Tháng 2</option>
                            <option value="3">Tháng 3</option>
                            <option value="4">Tháng 4</option>
                            <option value="5">Tháng 5</option>
                            <option value="6">Tháng 6</option>
                            <option value="7">Tháng 7</option>
                            <option value="8">Tháng 8</option>
                            <option value="9">Tháng 9</option>
                            <option value="10">Tháng 10</option>
                            <option value="11">Tháng 11</option>
                            <option value="12">Tháng 12</option>
                        </select>
                        <?php echo form_error('thang', $errors, '<span class="error">', '</span>'); ?>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="">Ngày lập <span style="color: red">*</span></label>
                        <input type="date" name="create_at" id="" class="form-control"
                            value="<?php echo old('create_at', $old); ?>">
                        <?php echo form_error('create_at', $errors, '<span class="error">', '</span>'); ?>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="tienphong">Tiền Phòng</label>
                        <input type="text" class="form-control" id="tienphong" name="tienphong">
                    </div>
                </div>

            </div>

            <!-- Hàng 3 -->
            <div class="row">
                <div class="col-3">
                    <div class="water">
                        <div class="form-group">
                            <label for="sodiencu">Số điện cũ (KWh)</label>
                            <input type="number" min="0" id="sodiencu" class="form-control" name="sodiencu" required oninput="calculateTienDien()">
                        </div>

                        <div class="form-group">
                            <label for="sodienmoi">Số điện mới (KWh)</label>
                            <input type="number" min="0" id="sodienmoi" class="form-control" name="sodienmoi" required oninput="calculateTienDien()">
                        </div>

                        <div class="form-group">
                            <label for="name">Ảnh <span style="color: red">*</span></label>
                            <div class="row ckfinder-group">
                                <div class="col-10">
                                    <input type="text" placeholder="Ảnh chỉ số điện mới" name="img_sodienmoi" id="name" class="form-control image-render" value="<?php echo old('img_sodienmoi', $old); ?>">
                                </div>
                                <div class="col-1">
                                    <button type="button" class="btn btn-primary btn-sm choose-image"><i class="fa fa-upload"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tiennuoc">Tiền điện (4000đ/1KWh)</label>
                            <input type="text" class="form-control" id="tiendien" name="tiendien">
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    <div class="water">
                        <div class="form-group">
                            <label for="sonuoccu">Số nước cũ (m/3)</label>
                            <input type="number" min="0" id="sonuoccu" class="form-control" name="sonuoccu" required oninput="calculateTienNuoc()">
                        </div>

                        <div class="form-group">
                            <label for="sonuocmoi">Số nước mới (m/3)</label>
                            <input type="number" min="0" id="sonuocmoi" class="form-control" name="sonuocmoi" required oninput="calculateTienNuoc()">
                        </div>

                        <div class="form-group">
                            <label for="name">Ảnh <span style="color: red">*</span></label>
                            <div class="row ckfinder-group">
                                <div class="col-10">
                                    <input type="text" placeholder="Ảnh chỉ số nước mới" name="img_sonuocmoi" id="name" class="form-control image-render" value="<?php echo old('img_sonuocmoi', $old); ?>">
                                </div>
                                <div class="col-1">
                                    <button type="button" class="btn btn-primary btn-sm choose-image"><i class="fa fa-upload"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tiennuoc">Tiền Nước (20000đ/1m3)</label>
                            <input type="text" class="form-control" id="tiennuoc" name="tiennuoc">
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    <div class="water">
                        <div class="form-group">
                            <label for="soluong">Số lượng người</label>
                            <input type="text" min="0" id="soluongNguoi" class="form-control" name="soluong" required onchange="calculateTienRac()">
                        </div>

                        <div class="form-group">
                            <label for="tienrac">Tiền rác (10.000đ/1người)</label>
                            <input type="text" class="form-control" id="tienrac" name="tienrac">
                        </div>
                    </div>
                    <div class="water">
                        <div class="form-group">
                            <label for="tienmang">Tiền Wifi (50.000đ/1tháng)</label>
                            <input type="text" class="form-control" id="tienmang" name="tienmang">
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    <div class="form-group" hidden>
                        <label for="">Tình trạng thu tiền<span style="color: red">*</label>
                        <select name="trangthaihoadon" class="form-select">
                            <option value="" disabled selected>Chọn trạng thái</option>
                            <option value="2" selected>Chưa thu</option>
                        </select>
                    </div>
                </div>

            </div>

            <!-- Hàng 4 -->
            <div class="row">
                <div class="col-5">
                    <div class="form-group">
                        <label for="tongtien">Tổng tiền</label>
                        <input type="text" class="form-control" id="tongtien" name="tongtien">
                    </div>
                </div>
            </div>
            <div class="from-group" style="margin-top: 20px">
                <div class="btn-row">
                    <a style="margin-right: 5px" href="<?php echo getLinkAdmin('bill', 'bills') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                    <button type="submit" class="btn btn-secondary btn-sm"><i class="fa fa-plus"></i> Thêm hóa đơn</button>
                </div>
            </div>
        </form>

    </div>
</div>


<?php
layout('footer', 'admin');
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dongiaNuoc = <?php echo $donGiaNuoc['giadichvu']; ?>;
        const dongiaDien = <?php echo $dongiaDien['giadichvu']; ?>;
        const dongiaRac = <?php echo $dongiaRac['giadichvu']; ?>;

        const roomsByArea = <?php echo json_encode($roomsByArea); ?>;
        const areaSelect = document.getElementById('area-select');
        const roomSelect = document.getElementById('room-select');

        if (!areaSelect || !roomSelect) {
            console.error("Không tìm thấy phần tử select khu vực hoặc phòng.");
            return;
        }

        areaSelect.addEventListener('change', function() {
            const areaId = this.value;
            roomSelect.innerHTML = '<option value="" disabled selected>Chọn phòng</option>';

            if (areaId && roomsByArea[areaId]) {
                roomsByArea[areaId].forEach(room => {
                    const option = document.createElement('option');
                    option.value = room.id;
                    option.textContent = `${room.tenphong} đang ở (${room.soluong} người)`;

                    // Lấy giá thuê từ dữ liệu phòng
                    const giaPhong = room.giathue || 0; // Nếu không có giá thuê, gán giá trị mặc định là 0
                    option.dataset.tienPhong = giaPhong;
                    option.dataset.soluong = room.soluong;
                    option.dataset.cs = room.cs;

                    roomSelect.appendChild(option);
                });
            }
        });
        roomSelect.addEventListener('change', function() {
            // Lấy giá trị từ PHP
            const dongiaWifi = <?php echo $dongiaWifi['giadichvu']; ?>;

            // Kiểm tra và cập nhật tiền mạng mặc định khi trang tải
            updateTienMang(dongiaWifi);

            // Hàm cập nhật tiền mạng
            function updateTienMang(dongiaWifi) {
                // Kiểm tra xem có giá trị hay không
                if (dongiaWifi !== undefined && dongiaWifi !== null) {
                    document.getElementById('tienmang').value = numberWithCommas(dongiaWifi);
                }
            }

            // Hàm định dạng số với dấu phẩy
            function numberWithCommas(x) {
                return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }


            const selectedRoom = roomSelect.options[roomSelect.selectedIndex];

            if (selectedRoom) {
                console.log("giaPhong dataset value:", selectedRoom.dataset.tienPhong);

                // Kiểm tra lại xem giaPhong có phải là một số hợp lệ hay không
                const giaPhong = parseFloat(selectedRoom.dataset.tienPhong);

                // Thêm kiểm tra nếu giaPhong là NaN hoặc <= 0
                if (isNaN(giaPhong) || giaPhong <= 0) {
                    console.error("giaPhong is NaN or less than or equal to 0");
                    document.getElementById('tienphong').value = "0"; // Đặt lại giá trị tiền phòng về 0
                    return; // Dừng lại nếu giaPhong không hợp lệ
                }

                // Nếu giaPhong hợp lệ, gọi hàm updateTienPhong
                updateTienPhong(giaPhong);
                updateSoluong(parseInt(selectedRoom.dataset.soluong, 10));
                updateCSD(selectedRoom.dataset.cs);
            }
        });

        function updateTienPhong(giaPhong) {

            // Kiểm tra giaPhong hợp lệ trước khi tính toán
            if (isNaN(giaPhong) || giaPhong <= 0) {
                console.error("giaPhong is NaN or less than or equal to 0");
                document.getElementById('tienphong').value = "0";
                return;
            }
            // Đặt giá tiền phòng trực tiếp (bỏ qua công thức tính toán)
            document.getElementById('tienphong').value = numberWithCommas(giaPhong);

            calculateTotal();
        }

        function updateCSD(cs) {

        }

        function calculateTienNuoc() {
            const sonuoccu = parseFloat(document.getElementById('sonuoccu').value) || 0;
            const sonuocmoi = parseFloat(document.getElementById('sonuocmoi').value) || 0;
            const tiennuoc = Math.round((sonuocmoi - sonuoccu) * dongiaNuoc);
            document.getElementById('tiennuoc').value = numberWithCommas(tiennuoc);
            calculateTotal();
        }

        function calculateTienDien() {
            const sodiencu = parseFloat(document.getElementById('sodiencu').value) || 0;
            const sodienmoi = parseFloat(document.getElementById('sodienmoi').value) || 0;
            const tiendien = Math.round((sodienmoi - sodiencu) * dongiaDien);
            document.getElementById('tiendien').value = numberWithCommas(tiendien);
            calculateTotal();
        }

        function calculateTienRac() {
            const soluongNguoi = parseFloat(document.getElementById('soluongNguoi').value) || 1;
            const tienrac = Math.round(soluongNguoi * dongiaRac);
            document.getElementById('tienrac').value = numberWithCommas(tienrac);
            calculateTotal();
        }

        function calculateTotal() {
            const tienphong = parseFloat(document.getElementById('tienphong').value.replace(/,/g, '')) || 0;
            const tiendien = parseFloat(document.getElementById('tiendien').value.replace(/,/g, '')) || 0;
            const tiennuoc = parseFloat(document.getElementById('tiennuoc').value.replace(/,/g, '')) || 0;
            const tienrac = parseFloat(document.getElementById('tienrac').value.replace(/,/g, '')) || 0;
            const tienmang = parseFloat(document.getElementById('tienmang').value.replace(/,/g, '')) || 0;
            const tongtien = Math.round(tienphong + tiendien + tiennuoc + tienrac + tienmang);
            document.getElementById('tongtien').value = numberWithCommas(tongtien);
        }

        function numberWithCommas(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function removeCommas(x) {
            return x.replace(/,/g, '');
        }

        document.getElementById('sonuoccu').addEventListener('input', calculateTienNuoc);
        document.getElementById('sonuocmoi').addEventListener('input', calculateTienNuoc);
        document.getElementById('sodiencu').addEventListener('input', calculateTienDien);
        document.getElementById('sodienmoi').addEventListener('input', calculateTienDien);
        document.getElementById('soluongNguoi').addEventListener('input', calculateTienRac);
        document.getElementById('tienmang').addEventListener('input', calculateTienMang);
        document.querySelector('form').addEventListener('submit', function(e) {
            document.getElementById('tienphong').value = removeCommas(document.getElementById('tienphong').value);
            document.getElementById('tiendien').value = removeCommas(document.getElementById('tiendien').value);
            document.getElementById('tiennuoc').value = removeCommas(document.getElementById('tiennuoc').value);
            document.getElementById('tienrac').value = removeCommas(document.getElementById('tienrac').value);
            document.getElementById('tienmang').value = removeCommas(document.getElementById('tienmang').value);
            document.getElementById('tongtien').value = removeCommas(document.getElementById('tongtien').value);
        });
        // Hàm địng dạng thành YYYY-mm-dd
        function reverseDateFormat(dateString) {
            const [year, month, day] = dateString.split('-');
            return `${year}-${month}-${day}`;
        }

        // Hàm lấy tháng và năm hiện tại (trả về chuỗi 'YYYY-MM')
        function getCurrentMonthYear() {
            const currentDate = new Date();
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth() + 1; // Lưu ý: getMonth() trả về index bắt đầu từ 0
            return year + '-' + (month < 10 ? '0' : '') + month;
        }
    });
</script>