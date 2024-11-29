<?php

if (!defined('_INCODE'))
    die('Access denied...');


$data = [
    'pageTitle' => 'Cập nhật thông tin hóa đơn'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Dịch vụ
$donGiaNuoc = firstRaw("SELECT giadichvu FROM services WHERE tendichvu = 'Tiền nước'");
$dongiaDien = firstRaw("SELECT giadichvu FROM services WHERE tendichvu = 'Tiền điện'");
$dongiaRac = firstRaw("SELECT giadichvu FROM services WHERE tendichvu = 'Tiền rác'");
$dongiaWifi = firstRaw("SELECT giadichvu FROM services WHERE tendichvu = 'Tiền Wifi'");
$allTenant = getRaw("SELECT tenant.id, tenant.tenkhach, room.tenphong 
                     FROM tenant 
                     INNER JOIN contract_tenant ON contract_tenant.tenant_id_1 = tenant.id 
                     INNER JOIN contract ON contract_tenant.contract_id_1 = contract.id 
                     INNER JOIN room ON tenant.room_id = room.id 
                     ORDER BY room.tenphong");


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
    ORDER BY tenphong
");

// Xử lý hiện dữ liệu cũ của người dùng
$body = getBody();
$id = $_GET['id'];

if (!empty($body['id'])) {
    $billId = $body['id'];
    $billDetail  = firstRaw("SELECT * FROM bill WHERE id=$billId");
    if (!empty($billDetail)) {
        // Gán giá trị billDetail vào setFalsh
        setFlashData('billDetail', $billDetail);
    } else {
        redirect('?module=bill');
    }
}

// Xử lý sửa người dùng
if (isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi

    // Kiểm tra mảng error
    if (empty($errors)) {
        // Khởi tạo mảng dữ liệu update ban đầu
        // Đặt hàm removeCommas ở trên hoặc trước khi bạn sử dụng
        function removeCommas($value)
        {
            return str_replace(',', '', $value);
        }
        // Xử lý giá trị số tiền còn thiếu và tổng tiền
        $sotienconthieu = !empty($body['sotienconthieu']) ? removeCommas($body['sotienconthieu']) : null;
        $tongtien = !empty($body['tongtien']) ? removeCommas($body['tongtien']) : null;

        // Kiểm tra và cập nhật trạng thái hóa đơn
        // Kiểm tra và cập nhật trạng thái hóa đơn
        if ($tongtien !== null) {
            if ($sotienconthieu === null || $sotienconthieu == 0) {
                $body['trangthaihoadon'] = 1; // Đã thu
            } elseif ($sotienconthieu > 0 && $sotienconthieu < $tongtien) {
                $body['trangthaihoadon'] = 3; // Còn nợ
            }
        }
        // Khởi tạo mảng dữ liệu update ban đầu
        $dataUpdate = [
            'room_id' => $body['room_id'],
            'tienphong' => !empty($body['tienphong']) ? removeCommas($body['tienphong']) : null,
            'sodiencu' => !empty($body['sodiencu']) ? removeCommas($body['sodiencu']) : null,
            'sodienmoi' => !empty($body['sodienmoi']) ? removeCommas($body['sodienmoi']) : null,
            'tiendien' => !empty($body['tiendien']) ? removeCommas($body['tiendien']) : null,
            'sonuoccu' => !empty($body['sonuoccu']) ? removeCommas($body['sonuoccu']) : null,
            'sonuocmoi' => !empty($body['sonuocmoi']) ? removeCommas($body['sonuocmoi']) : null,
            'tiennuoc' => !empty($body['tiennuoc']) ? removeCommas($body['tiennuoc']) : null,
            'songuoi' => !empty($body['soluong']) ? $body['soluong'] : 0,
            'tienrac' => !empty($body['tienrac']) ? removeCommas($body['tienrac']) : null,
            'tienmang' => !empty($body['tienmang']) ? removeCommas($body['tienmang']) : null,
            'tongtien' => !empty($body['tongtien']) ? removeCommas($body['tongtien']) : null,
            'sotiendatra' => !empty($body['sotiendatra']) ? removeCommas($body['sotiendatra']) : null,
            'sotienconthieu' => !empty($body['sotienconthieu']) ? removeCommas($body['sotienconthieu']) : null,
            'trangthaihoadon' => $body['trangthaihoadon'],
        ];


        $id = (int)$_GET['id'];  // Đảm bảo id là số nguyên
        $condition = "id=$id";
        $updateStatus = update('bill', $dataUpdate, $condition);

        if ($updateStatus) {
            setFlashData('msg', 'Cập nhật thông tin hóa đơn thành công');
            setFlashData('msg_type', 'suc');
            redirect('?module=bill&action=bills');
        } else {
            setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
            setFlashData('msg_type', 'err');
        }
    } else {
        // Có lỗi xảy ra
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);  // giữ lại các trường dữ liệu hợp lê khi nhập vào
    }

    redirect('?module=bill&action=edit&id=' . $billId);
}
$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

if (!empty($billDetail) && empty($old)) {
    $old = $billDetail;
}
?>
<?php
layout('navbar', 'admin', $data);
?>

<div class="container">
    <hr />


    <div class="box-content">
        <form action="" method="post" class="row">
            <!-- hàng 1 -->
            <div class="row">
                <div class="col-5">
                    <div class="form-group">
                        <label for="">Chọn phòng lập hóa đơn <span style="color: red">*</span></label>
                        <select required name="room_id" id="room_id" class="form-select" onchange="updateTienPhong(); updateChuky(); updateSoluong()">
                            <option value="">Chọn phòng</option>
                            <?php
                            if (!empty($allRoom)) {
                                foreach ($allRoom as $item) {
                            ?>
                                    <option data-soluong="<?php echo $item['soluong']; ?>" data-giaphong="<?php echo $item['giathue']; ?>" value="<?php echo $item['id'] ?>" <?php echo (old('room_id', $old) == $item['id']) ? 'selected' : false; ?>><?php echo $item['tenphong'] ?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                        <?php echo form_error('room_id', $errors, '<span class="error">', '</span>'); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <div class="form-group">
                        <label for="tongtien">Tổng tiền</label>
                        <input value="<?php echo old('tongtien', $old); ?>" type="text" class="form-control" id="tongtien" name="tongtien">
                    </div>
                </div>

                <div class="col-3">
                    <div class="form-group">
                        <label for="tongtien">Đã thanh toán</label>
                        <input value="<?php echo old('sotiendatra', $old); ?>" type="text" class="form-control" id="sotiendatra" name="sotiendatra" oninput="formatCurrency(this)">
                    </div>
                </div>

                <div class="col-3">
                    <div class="form-group">
                        <label for="tongtien">Còn nợ</label>
                        <input value="<?php echo old('sotienconthieu', $old); ?>" type="text" class="form-control" id="sotienconthieu" name="sotienconthieu">
                    </div>
                </div>

                <!-- <div class="col-3">
                    <div class="form-group">
                        <label for="">Tình trạng thu tiền<span style="color: red">*</span></label>
                        <select name="trangthaihoadon" class="form-select">
                            <option value="" disabled <?php echo ($billDetail['trangthaihoadon'] === null) ? 'selected' : ''; ?>>Chọn trạng thái</option>
                            <option value="2" <?php echo ($billDetail['trangthaihoadon'] == 2) ? 'selected' : ''; ?>>Chưa thu</option>
                            <option value="1" <?php echo ($billDetail['trangthaihoadon'] == 1) ? 'selected' : ''; ?>>Đã thu</option>
                            <option value="3" <?php echo ($billDetail['trangthaihoadon'] == 3) ? 'selected' : ''; ?>>Còn nợ</option>
                        </select>
                    </div>
                </div> -->
            </div>

            <!-- Hàng 3 -->
            <div class="row">
                <div class="col-3">
                    <div class="water">
                        <div class="form-group">
                            <label for="sodiencu">Số điện cũ (KWh)</label>
                            <input value="<?php echo old('sodiencu', $old); ?>" type="number" min="0" id="sodiencu" class="form-control" name="sodiencu" required oninput="calculateTienDien()">
                        </div>

                        <div class="form-group">
                            <label for="sodienmoi">Số điện mới (KWh)</label>
                            <input value="<?php echo old('sodienmoi', $old); ?>" type="number" min="0" id="sodienmoi" class="form-control" name="sodienmoi" required oninput="calculateTienDien()">
                        </div>

                        <div class="form-group">
                            <label for="tiennuoc">Tiền điện</label>
                            <input value="<?php echo old('tiendien', $old); ?>" type="text" class="form-control" id="tiendien" name="tiendien">
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    <div class="water">
                        <div class="form-group">
                            <label for="sonuoccu">Số nước cũ (m/3)</label>
                            <input value="<?php echo old('sonuoccu', $old); ?>" type="number" min="0" id="sonuoccu" class="form-control" name="sonuoccu" required oninput="calculateTienNuoc()">
                        </div>

                        <div class="form-group">
                            <label for="sonuocmoi">Số nước mới (m/3)</label>
                            <input value="<?php echo old('sonuocmoi', $old); ?>" type="number" min="0" id="sonuocmoi" class="form-control" name="sonuocmoi" required oninput="calculateTienNuoc()">
                        </div>

                        <div class="form-group">
                            <label for="tiennuoc">Tiền Nước</label>
                            <input value="<?php echo old('tiennuoc', $old); ?>" type="text" class="form-control" id="tiennuoc" name="tiennuoc">
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    <div class="form-group">
                        <label for="tienphong">Tiền Phòng</label>
                        <input value="<?php echo old('tienphong', $old); ?>" type="text" class="form-control" id="tienphong" name="tienphong">
                    </div>
                    <div class="water">
                        <div class="form-group">
                            <label for="soluong">Số lượng người</label>
                            <input value="<?php echo old('songuoi', $old); ?>" type="number" min="0" id="soluongNguoi" class="form-control" name="soluong" required onchange="calculateTienRac()">
                        </div>

                        <div class="form-group">
                            <label for="tienrac">Tiền rác</label>
                            <input value="<?php echo old('tienrac', $old); ?>" type="text" class="form-control" id="tienrac" name="tienrac">
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    <div class="water">
                        <div class="form-group">
                            <label for="tienmang">Tiền Wifi</label>
                            <input value="<?php echo old('tienmang', $old); ?>" type="text" class="form-control" id="tienmang" name="tienmang">
                        </div>
                    </div>
                </div>

            </div>

            <div class="from-group">
                <div class="btn-row">
                    <a style="margin-left: 20px " href="<?php echo getLinkAdmin('bill', 'bills') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                    <button type="submit" class="btn btn-secondary btn-sm"><i class="fa fa-edit"></i> Cập nhật</button>
                </div>
            </div>
        </form>
    </div>
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
        const dongiaWifi = <?php echo $dongiaWifi['giadichvu']; ?>;

        // Kiểm tra và cập nhật tiền mạng mặc định khi trang tải
        updateTienMang(dongiaWifi);

        // Hàm cập nhật tiền mạngs
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

        function updateRoomDetails() {
            // Tính toán tiền rác & Wifi
            calculateTienRac();
            calculateTotal();
            updateTienPhong();
            updateSoluong();
        }

        function updateTienPhong() {
            const roomSelect = document.getElementById('room_id');
            const selectedOption = roomSelect.options[roomSelect.selectedIndex];
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

        // Hàm địng dạng thành YYYY-mm-dd
        function reverseDateFormat(dateString) {
            const [year, month, day] = dateString.split('-');
            return `${day}-${month}-${year}`;
        }

        // Hàm lấy tháng và năm hiện tại (trả về chuỗi 'YYYY-MM')
        function getCurrentMonthYear() {
            const currentDate = new Date();
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth() + 1; // Lưu ý: getMonth() trả về index bắt đầu từ 0
            return year + '-' + (month < 10 ? '0' : '') + month;
        }

        function updateSoluong() {
            const roomSelect = document.getElementById('room_id');
            const selectedOption = roomSelect.options[roomSelect.selectedIndex];
            const soluong = selectedOption.getAttribute('data-soluong');

            document.getElementById('soluongNguoi').value = soluong;
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
        document.getElementById('sotiendatra').addEventListener('input', function() {
            // Lấy giá trị tổng tiền từ ô nhập
            var tongtien = parseFloat(document.getElementById('tongtien').value.replace(/,/g, '')) || 0;
            // Lấy giá trị số tiền đã trả từ ô nhập
            var sotiendatra = parseFloat(document.getElementById('sotiendatra').value.replace(/,/g, '')) || 0;
            // Lấy giá trị số tiền còn thiếu hiện tại
            var sotienconthieu = parseFloat(document.getElementById('sotienconthieu').value.replace(/,/g, '')) || 0;

            // Kiểm tra nếu biến `a` chưa được gán (lần đầu tiên)
            if (typeof window.a === 'undefined') {
                // Lưu số tiền còn thiếu ban đầu vào biến `a`
                window.a = sotienconthieu;
            }

            // Kiểm tra nếu số tiền còn thiếu khác 0
            if (sotienconthieu !== 0) {
                // Tính toán số tiền còn thiếu mới
                sotienconthieu = window.a - sotiendatra;
            } else {
                // Nếu số tiền còn thiếu ban đầu là 0, tính toán bình thường
                sotienconthieu = tongtien - sotiendatra;
            }

            // Hiển thị số tiền còn thiếu mới vào ô nhập
            document.getElementById('sotienconthieu').value = numberWithCommas(sotienconthieu);
        });

        // Hàm để thêm dấu phân cách hàng nghìn
        function numberWithCommas(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function removeCommas(x) {
            return x.replace(/,/g, '');
        }

        document.getElementById('room_id').addEventListener('change', updateRoomDetails);
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
            document.getElementById('sotiendatra').value = removeCommas(document.getElementById('sotiendatra').value);
            document.getElementById('sotienconthieu').value = removeCommas(document.getElementById('sotienconthieu').value);
        });

        updateRoomDetails();
    });
</script>