<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Quản lý dịch vụ'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$currentMonthYear = date('Y-m');

// Xử lý lọc dữ liệu
$filter = '';
$datebill = $currentMonthYear; // Thiết lập giá trị mặc định
if (isGet()) {
    $body = getBody('get');

    // Xử lý lọc theo từ khóa
    if (!empty($body['datebill'])) {
        $datebill = $body['datebill'];
    }
}

if (!empty($filter) && strpos($filter, 'WHERE') >= 0) {
    $operator = 'AND';
} else {
    $operator = 'WHERE';
}

$filter .= " $operator bill.create_at LIKE '%$datebill%'";

$allService = getRaw("SELECT * FROM services");
$listAllBill = getRaw("SELECT *, bill.id, room.tenphong FROM bill 
INNER JOIN room ON bill.room_id = room.id  $filter ORDER BY bill.create_at DESC ");

// Xử lý Thêm/Sửa dịch vụ

if (isPost()) {
    // Lấy dữ liệu từ form
    $body = getBody();
    $errors = []; // Mảng lưu trữ các lỗi

    // Nếu không có lỗi, tiến hành lưu vào CSDL
    if (empty($errors)) {
        $tendichvu = trim($body['tendichvu']);
        $donvitinh = trim($body['donvitinh']);
        $giadichvu = trim($body['giadichvu']);

        if (!empty($body['id'])) {
            // Cập nhật
            $id = $body['id'];
            $dataUpdate = [
                'tendichvu' => $tendichvu,
                'donvitinh' => $donvitinh,
                'giadichvu' => $giadichvu,
            ];
            $updateStatus = update('services', $dataUpdate, "id=$id");

            if ($updateStatus) {
                setFlashData('msg', 'Cập nhật dịch vụ thành công');
                setFlashData('msg_type', 'suc');
                redirect('?module=services');
            } else {
                setFlashData('msg', 'Cập nhật thất bại');
                setFlashData('msg_type', 'err');
            }
        } else {
            // Thêm mới
            $dataInsert = [
                'tendichvu' => $tendichvu,
                'donvitinh' => $donvitinh,
                'giadichvu' => $giadichvu,
            ];
            $insertStatus = insert('services', $dataInsert);

            if ($insertStatus) {
                setFlashData('msg', 'Thêm dịch vụ thành công');
                setFlashData('msg_type', 'suc');
                redirect('?module=services');
            } else {
                setFlashData('msg', 'Thêm dịch vụ thất bại');
                setFlashData('msg_type', 'err');
            }
        }
    } else {
        // Nếu có lỗi, lưu lại mảng lỗi và dữ liệu nhập vào
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body); // Lưu lại dữ liệu để hiển thị lại trong form
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

<div class="container-fluid">
    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

    <!-- Modal Thêm/Sửa Dịch Vụ -->
    <div id="serviceModal" class="modal">
        <div class="modal-content">
            <h4 id="modalTitle" style="margin: 20px 0">Thêm dịch vụ mới</h4>
            <hr />
            <form id="serviceForm" method="post">
                <input type="hidden" name="id" id="serviceId" value="<?php echo isset($body['id']) ? $body['id'] : ''; ?>">

                <div class="form-group">
                    <label for="tendichvu">Tên dịch vụ <span style="color: red">*</span></label>
                    <input type="text" placeholder="Tên dịch vụ" name="tendichvu" id="tendichvu" class="form-control" value="<?php echo isset($body['tendichvu']) ? htmlspecialchars($body['tendichvu']) : ''; ?>">
                    <span id="tendichvuError" class="error" style="color: red;"></span>
                </div>

                <div class="form-group">
                    <label for="donvitinh">Đơn vị tính <span style="color: red">*</span></label>
                    <input type="text" placeholder="Đơn vị tính" name="donvitinh" id="donvitinh" class="form-control" value="<?php echo isset($body['donvitinh']) ? htmlspecialchars($body['donvitinh']) : ''; ?>">
                    <span id="donvitinhError" class="error" style="color: red;"></span>
                </div>

                <div class="form-group">
                    <label for="giadichvu">Giá dịch vụ <span style="color: red">*</span></label>
                    <input type="text" placeholder="Giá dịch vụ" name="giadichvu" id="giadichvu" class="form-control" value="<?php echo isset($body['giadichvu']) ? htmlspecialchars($body['giadichvu']) : ''; ?>">
                    <span id="giadichvuError" class="error" style="color: red;"></span>
                </div>

                <div class="form-group">
                    <div class="btn-row">
                        <button style="margin-right: 10px" type="submit" class="btn btn-secondary"><i class="fa fa-save"></i> Lưu</button>
                        <button type="button" class="btn btn-secondary" onclick="closeServiceModal()">Hủy</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="box-content box-service">
        <div class="collect-left">
            <div class="collect-left_top">
                <div>
                    <h3>Quản lý dịch vụ</h3>
                    <i>Các dịch vụ được áp dụng</i>
                </div>
                <button id="openModalBtn" class="service-btn" style="border: none; color: #fff"><i class="fa fa-plus"></i></button>
            </div>
            <br>

            <div class="collect-list">
                <div class="d-flex flex-wrap justify-content-start">
                    <?php foreach ($allService as $item) { ?>
                        <div class="col-md-4">
                            <div class="service-items">
                                <div class="service-item_left">
                                    <div class="service-item_icon">
                                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/service-icon.svg" alt="">
                                    </div>
                                    <div>
                                        <h6><?php echo $item['tendichvu'] ?></h6>
                                        <p><?php echo number_format($item['giadichvu'], 0, ',', '.') ?>đ/<?php echo $item['donvitinh'] ?></p>
                                    </div>
                                </div>
                                <div class="service-item_right">
                                    <div class="edit">
                                        <a href="javascript:void(0)" onclick="openServiceModal('<?php echo $item['id']; ?>', '<?php echo $item['tendichvu']; ?>', '<?php echo $item['donvitinh']; ?>', '<?php echo $item['giadichvu']; ?>')">
                                            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/service-edit.svg" alt="">
                                        </a>
                                    </div>
                                    <div class="del">
                                        <a href="<?php echo getLinkAdmin('services', 'delete', ['id' => $item['id']]); ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa dịch vụ không ?')">
                                            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/service-delete.svg" alt="">
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php layout('footer', 'admin'); ?>

<script>
    // Hàm để mở modal
    function openServiceModal(id = '', tendichvu = '', donvitinh = '', giadichvu = '', errors = {}) {
        // Điền thông tin vào form
        document.getElementById('serviceId').value = id;
        document.getElementById('tendichvu').value = tendichvu;
        document.getElementById('donvitinh').value = donvitinh;
        document.getElementById('giadichvu').value = giadichvu;

        // Hiển thị các lỗi nếu có
        document.getElementById('tendichvuError').innerText = errors['tendichvu'] || '';
        document.getElementById('donvitinhError').innerText = errors['donvitinh'] || '';
        document.getElementById('giadichvuError').innerText = errors['giadichvu'] || '';

        // Thay đổi tiêu đề modal
        if (id) {
            document.getElementById('modalTitle').innerText = 'Sửa dịch vụ';
        } else {
            document.getElementById('modalTitle').innerText = 'Thêm dịch vụ mới';
        }

        // Hiển thị modal
        document.getElementById('serviceModal').style.display = 'block';
    }

    // Hàm để đóng modal
    function closeServiceModal() {
        document.getElementById('serviceModal').style.display = 'none';
    }

    // Hàm kiểm tra form trước khi gửi
    function validateForm() {
        let errors = {};

        // Lấy giá trị từ form
        const tendichvu = document.getElementById('tendichvu').value.trim();
        const donvitinh = document.getElementById('donvitinh').value.trim();
        const giadichvu = document.getElementById('giadichvu').value.trim();

        // Kiểm tra từng trường
        if (!tendichvu) {
            errors['tendichvu'] = '** Bạn chưa nhập tên dịch vụ';
        }

        if (!giadichvu || isNaN(giadichvu)) {
            errors['giadichvu'] = '** Bạn chưa nhập giá dịch vụ hợp lệ';
        }

        if (!donvitinh) {
            errors['donvitinh'] = '** Bạn chưa nhập đơn vị tính';
        }

        // Nếu có lỗi, trả về false để không đóng modal
        if (Object.keys(errors).length > 0) {
            openServiceModal('', tendichvu, donvitinh, giadichvu, errors);
            return false;
        }

        return true;
    }

    // Sự kiện khi form được gửi
    document.getElementById('serviceForm').addEventListener('submit', function(event) {
        if (!validateForm()) {
            event.preventDefault(); // Ngừng submit nếu form không hợp lệ
        }
    });

    // Nút thêm dịch vụ mở modal
    document.getElementById('openModalBtn').addEventListener('click', function() {
        openServiceModal(); // Mở modal với giá trị mặc định (rỗng)
    });
</script>