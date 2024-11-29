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
        chuky, 
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
        $dataUpdate = [
            'room_id' => $body['room_id'],
            'chuky' => $body['chuky'],
            'songayle' => !empty($body['songayle']) ? $body['songayle'] : null, // Nếu songayle không rỗng thì cập nhật
            'tienphong' => $body['tienphong'],
            'sodiencu' => $body['sodiencu'],
            'sodienmoi' => $body['sodienmoi'],
            'tiendien' => $body['tiendien'],
            'sonuoccu' => $body['sonuoccu'],
            'sonuocmoi' => $body['sonuocmoi'],
            'tiennuoc' => $body['tiennuoc'],
            'songuoi' => $body['soluong'],
            'tienrac' => $body['tienrac'],
            'tienmang' => $body['tienmang'],
            'nocu' => !empty($body['nocu']) ? $body['nocu'] : null, // Nếu nocu không rỗng thì cập nhật
            'tongtien' => $body['tongtien'],
            'sotiendatra' => !empty($body['sotiendatra']) ? $body['sotiendatra'] : null, // Nếu sotiendatra không rỗng thì cập nhật
            'sotienconthieu' => $body['sotienconthieu'],
            'trangthaihoadon' => $body['trangthaihoadon'],
        ];

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
                                    <option data-soluong="<?php echo $item['soluong']; ?>" data-chuky="<?php echo $item['chuky']; ?>" data-giaphong="<?php echo $item['giathue']; ?>" value="<?php echo $item['id'] ?>" <?php echo (old('room_id', $old) == $item['id']) ? 'selected' : false; ?>><?php echo $item['tenphong'] ?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                        <?php echo form_error('room_id', $errors, '<span class="error">', '</span>'); ?>
                    </div>
                </div>
            </div>

            <!-- Hàng 2 -->

            <div class="row">
                <div class="col-3">
                    <div class="form-group">
                        <label for="">Số ngày lẻ <span style="color: red">*</span></label>
                        <input value="<?php echo old('songayle', $old); ?>" type="text" name="songayle" id="songayle" class="form-control">
                        <?php echo form_error('songayle', $errors, '<span class="error">', '</span>'); ?>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label for="">Chu kỳ <span style="color: red">*</span></label>
                        <input value="<?php echo old('chuky', $old); ?>" type="text" name="chuky" id="chuky" class="form-control">
                        <?php echo form_error('chuky', $errors, '<span class="error">', '</span>'); ?>
                    </div>
                </div>

                <div class="col-3">
                    <div class="form-group">
                        <label for="tienphong">Tiền Phòng</label>
                        <input value="<?php echo old('tienphong', $old); ?>" type="text" class="form-control" id="tienphong" name="tienphong">
                    </div>
                </div>
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
                    <div class="water">
                        <div class="form-group">
                            <label for="nocu">Nợ cũ</label>
                            <input value="<?php echo old('nocu', $old); ?>" type="text" class="form-control" id="nocu" name="nocu">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Hàng 4 -->
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

                <div class="col-3">
                    <div class="form-group">
                        <label for="">Tình trạng thu tiền</label>
                        <select name="trangthaihoadon" class="form-select">
                            <!-- <option value="">Chọn trạng thái</option>                                -->
                            <option value="0" <?php if ($billDetail['trangthaihoadon'] == 0) echo 'selected' ?>>Chưa thanh toán</option>
                            <option value="1" <?php if ($billDetail['trangthaihoadon'] == 1) echo 'selected' ?>>Đã thanh toán</option>
                            <option value="2" <?php if ($billDetail['trangthaihoadon'] == 2) echo 'selected' ?>>Đang nợ tiền</option>
                        </select>
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


<?php
layout('footer', 'admin');
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dongiaNuoc = <?php echo $donGiaNuoc['giadichvu']; ?>;
        const dongiaDien = <?php echo $dongiaDien['giadichvu']; ?>;
        const dongiaRac = <?php echo $dongiaRac['giadichvu']; ?>;
        const dongiaWifi = <?php echo $dongiaWifi['giadichvu']; ?>;

        function updateRoomDetails() {
            // Tính toán tiền rác & Wifi
            calculateTienRac();
            calculateTienMang();
            calculateTotal();
            updateTienPhong();
            firstMonth();
            updateSoluong();
        }

        function updateTienPhong() {
            const roomSelect = document.getElementById('room_id');
            const selectedOption = roomSelect.options[roomSelect.selectedIndex];
            const giaPhong = parseFloat(selectedOption.getAttribute('data-giaphong')) || 0;
            const sothang = parseFloat(document.getElementById('chuky').value) || 0;
            const songayle = parseFloat(document.getElementById('songayle').value) || 0;

            // Tính toán tiền phòng
            var formattedThang = giaPhong * sothang;
            var formattedSongayle = (giaPhong / 30) * songayle;
            var tienphong = formattedThang + formattedSongayle;

            // Định dạng số tiền với dấu phân cách hàng nghìn
            document.getElementById('tienphong').value = numberWithCommas(tienphong);
            calculateTotal();
        }
        // Nếu là tháng đầu tiên vào ở thì thông báo 
        function firstMonth() {
            const roomSelect = document.getElementById('room_id');
            const selectedOption = roomSelect.options[roomSelect.selectedIndex];

            const ngayVaoPhong = selectedOption.getAttribute('data-ngayvao'); // Lấy ngày vào của phòng được chọn
            const currentMonthYear = getCurrentMonthYear(); // Lấy tháng và năm hiện tại

            // Trích xuất tháng và năm từ ngày vào của phòng
            const [namPhong, thangPhong] = ngayVaoPhong.split('-');

            // Trích xuất tháng và năm từ thời gian hiện tại
            const [namHienTai, thangHienTai] = currentMonthYear.split('-');

            // Kiểm tra xem tháng và năm của ngày vào có trùng khớp với tháng và năm hiện tại không
            if (namPhong === namHienTai && thangPhong === thangHienTai) {
                alert('Đây là tháng đầu tiên vào ở của phòng này, tính tiền phòng theo số ngày lẻ nha - ngày vào là: ' + reverseDateFormat(ngayVaoPhong))
            }
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

        function updateChuky() {
            const roomSelect = document.getElementById('room_id');
            const selectedOption = roomSelect.options[roomSelect.selectedIndex];
            const chuky = selectedOption.getAttribute('data-chuky');

            document.getElementById('chuky').value = chuky;
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
            const tiennuoc = (sonuocmoi - sonuoccu) * dongiaNuoc;
            document.getElementById('tiennuoc').value = numberWithCommas(tiennuoc);
            calculateTotal();
        }

        function calculateTienDien() {
            const sodiencu = parseFloat(document.getElementById('sodiencu').value) || 0;
            const sodienmoi = parseFloat(document.getElementById('sodienmoi').value) || 0;
            const tiendien = (sodienmoi - sodiencu) * dongiaDien;
            document.getElementById('tiendien').value = numberWithCommas(tiendien);
            calculateTotal();
        }

        function calculateTienRac() {
            const chuky = parseFloat(document.getElementById('chuky').value) || 1; // Chu kỳ mặc định là 1 tháng nếu không có giá trị
            const soluongNguoi = parseFloat(document.getElementById('soluongNguoi').value) || 1;
            const tienrac = soluongNguoi * dongiaRac * chuky;
            document.getElementById('tienrac').value = numberWithCommas(tienrac);
            calculateTotal();
        }

        function calculateTienMang() {
            const chuky = parseFloat(document.getElementById('chuky').value) || null; // Chu kỳ mặc định là 1 tháng nếu không có giá trị
            const songayle = parseFloat(document.getElementById('songayle').value) || 0;
            const tienmangThang = chuky * dongiaWifi;
            const tienmangNgayle = (dongiaWifi / 30) * songayle;
            let tienmang = Math.ceil(tienmangThang + tienmangNgayle);
            document.getElementById('tienmang').value = numberWithCommas(tienmang);
            calculateTotal();
        }

        function calculateTotal() {
            const tienphong = parseFloat(document.getElementById('tienphong').value.replace(/,/g, '').replace(' đ', '')) || 0;
            const tiendien = parseFloat(document.getElementById('tiendien').value.replace(/,/g, '').replace(' đ', '')) || 0;
            const tiennuoc = parseFloat(document.getElementById('tiennuoc').value.replace(/,/g, '').replace(' đ', '')) || 0;
            const tienrac = parseFloat(document.getElementById('tienrac').value.replace(/,/g, '').replace(' đ', '')) || 0;
            const tienmang = parseFloat(document.getElementById('tienmang').value.replace(/,/g, '').replace(' đ', '')) || 0;
            const nocu = parseFloat(document.getElementById('nocu').value.replace(/,/g, '').replace(' đ', '')) || 0;

            const tongtien = tienphong + tiendien + tiennuoc + tienrac + tienmang + nocu;
            document.getElementById('tongtien').value = numberWithCommas(tongtien);
        }

        document.getElementById('sotiendatra').addEventListener('input', function() {
            // Lấy giá trị tổng tiền từ ô nhập
            var tongtien = parseFloat(document.getElementById('tongtien').value.replace(/,/g, '').replace(' đ', '')) || 0;
            // Lấy giá trị số tiền đã trả từ ô nhập
            var sotiendatra = parseFloat(document.getElementById('sotiendatra').value.replace(/,/g, '').replace(' đ', '')) || 0;
            // Tính toán số tiền còn nợ
            var sotienconthieu = tongtien - sotiendatra;
            // Hiển thị số tiền còn nợ
            document.getElementById('sotienconthieu').value = numberWithCommas(sotienconthieu);
        });


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
        document.getElementById('chuky').addEventListener('input', function() {
            calculateTienMang();
            updateTienPhong();
            calculateTienRac();
        });
        document.getElementById('songayle').addEventListener('input', calculateTienMang);
        document.getElementById('songayle').addEventListener('input', updateTienPhong);
        document.getElementById('nocu').addEventListener('input', calculateTotal);

        document.querySelector('form').addEventListener('submit', function(e) {
            document.getElementById('tienphong').value = removeCommas(document.getElementById('tienphong').value);
            document.getElementById('tiendien').value = removeCommas(document.getElementById('tiendien').value);
            document.getElementById('tiennuoc').value = removeCommas(document.getElementById('tiennuoc').value);
            document.getElementById('tienrac').value = removeCommas(document.getElementById('tienrac').value);
            document.getElementById('tienmang').value = removeCommas(document.getElementById('tienmang').value);
            document.getElementById('nocu').value = removeCommas(document.getElementById('nocu').value);
            document.getElementById('tongtien').value = removeCommas(document.getElementById('tongtien').value);
            document.getElementById('sotiendatra').value = removeCommas(document.getElementById('sotiendatra').value);
            document.getElementById('sotienconthieu').value = removeCommas(document.getElementById('sotienconthieu').value);
        });

        updateRoomDetails();
    });
</script>