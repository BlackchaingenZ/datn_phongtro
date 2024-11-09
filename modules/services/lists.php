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
    $body = getBody();
    $errors = [];

    if (empty(trim($body['tendichvu']))) {
        $errors['tendichvu']['required'] = '** Bạn chưa nhập tên dịch vụ';
    }

    if (empty(trim($body['donvitinh']))) {
        $errors['donvitinh']['required'] = '** Bạn chưa chọn đơn vị tính';
    }

    if (empty(trim($body['giadichvu']))) {
        $errors['giadichvu']['required'] = '** Bạn chưa nhập giá dịch vụ';
    }

    if (empty($errors)) {
        $dataSave = [
            'tendichvu' => $body['tendichvu'],
            'donvitinh' => $body['donvitinh'],
            'giadichvu' => $body['giadichvu'],
        ];

        if (!empty($body['id'])) {
            // Xử lý cập nhật dữ liệu
            $updateStatus = update('services', $dataSave, "id=" . $body['id']);
            if ($updateStatus) {
                setFlashData('msg', 'Cập nhật dịch vụ thành công');
                setFlashData('msg_type', 'suc');
            } else {
                setFlashData('msg', 'Có lỗi xảy ra, vui lòng thử lại');
                setFlashData('msg_type', 'err');
            }
        } else {
            // Xử lý thêm mới
            $insertStatus = insert('services', $dataSave);
            if ($insertStatus) {
                setFlashData('msg', 'Thêm dịch vụ khách hàng thành công');
                setFlashData('msg_type', 'suc');
            } else {
                setFlashData('msg', 'Có lỗi xảy ra, vui lòng thử lại');
                setFlashData('msg_type', 'err');
            }
        }

        redirect('?module=services');
    } else {
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);
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

    <!-- Thêm/Sửa -->
    <div id="serviceModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeServiceModal()">&times;</span>
            <h4 id="modalTitle" style="margin: 20px 0">Thêm dịch vụ mới</h4>
            <hr />
            <form id="serviceForm" method="post">
                <input type="hidden" name="id" id="serviceId">
                <div class="form-group">
                    <label for="tendichvu">Tên dịch vụ <span style="color: red">*</span></label>
                    <input type="text" placeholder="Tên dịch vụ" name="tendichvu" id="tendichvu" class="form-control">
                </div>
                <div class="form-group">
                    <label for="donvitinh">Đơn vị tính <span style="color: red">*</span></label>
                    <select name="donvitinh" id="donvitinh" class="form-select">
                        <!-- <option value="">Chọn đơn vị</option> -->
                        <option value="KWh">KWh</option>
                        <option value="khối">Khối</option>
                        <option value="người">Người</option>
                        <option value="tháng">Tháng</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="giadichvu">Giá dịch vụ <span style="color: red">*</span></label>
                    <input type="text" placeholder="Giá dịch vụ" name="giadichvu" id="giadichvu" class="form-control">
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
        <div class="box-container">
            <div class="col-5">
                <div>
                    <h3>Quản lý dịch vụ</h3>
                    <i>Các dịch vụ khách sử dụng</i>
                    <a href="<?php echo getLinkAdmin('services', 'add') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-plus"></i> Thêm mới </a>
                </div>
            </div>
            <p></p>
            <div class="box-container">
            <div class="d-flex flex-wrap justify-content-start">
                    <?php foreach ($allService as $item) { ?>
                        <div class="col-md-4"> <!-- Mỗi dịch vụ chiếm 1/3 chiều rộng của hàng -->
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

<?php
layout('footer', 'admin');
?>
<script>
    function toggle(__this) {
        let isChecked = __this.checked;
        let checkbox = document.querySelectorAll('input[name="records[]"]');
        for (let index = 0; index < checkbox.length; index++) {
            checkbox[index].checked = isChecked
        }
    }

    function openServiceModal(id = '', tendichvu = '', donvitinh = '', giadichvu = '') {
        document.getElementById('serviceId').value = id;
        document.getElementById('tendichvu').value = tendichvu;
        document.getElementById('donvitinh').value = donvitinh;
        document.getElementById('giadichvu').value = giadichvu;

        if (id) {
            document.getElementById('modalTitle').innerText = 'Sửa dịch vụ';
        } else {
            document.getElementById('modalTitle').innerText = 'Thêm dịch vụ mới';
        }

        document.getElementById('serviceModal').style.display = 'block';
    }

    function closeServiceModal() {
        document.getElementById('serviceModal').style.display = 'none';
    }
</script>