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
        <?php getMsg($msg, $msgType);?>          
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
                        <option value="khoi">Khối</option>
                        <option value="nguoi">Người</option>
                        <option value="thang">Tháng</option>
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
        <div class="service-left">
            <div class="service-left_top">
                <div>
                    <h3>Quản lý dịch vụ</h3>
                    <i>Các dịch vụ khách sử dụng</i>
                </div>
                <!--<button class="service-btn" style="border: none; color: #fff" onclick="return alert('Chức năng này đang được mở rộng')"><i class="fa fa-plus"></i></button>-->
            </div>

            <?php 
                foreach($allService as $item) {
                    ?>
                        <!-- Item 1 -->
                        <div class="service-item">
                            <div class="service-item_left">
                                <div class="service-item_icon">
                                    <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/service-icon.svg" alt="">
                                </div>

                                <div>
                                    <h6><?php echo $item['tendichvu'] ?></h6>
                                    <p><?php echo  number_format($item['giadichvu'], 0, ',', '.')?>đ/<?php echo $item['donvitinh'] ?></p>
                                    <i>Đang áp dụng cho các phòng</i>
                                </div>
                            </div>

                            <div class="service-item_right">
                                <div class="edit">
                                    <a href="javascript:void(0)" onclick="openServiceModal('<?php echo $item['id']; ?>', '<?php echo $item['tendichvu']; ?>', '<?php echo $item['donvitinh']; ?>', '<?php echo $item['giadichvu']; ?>')"><img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/service-edit.svg" alt=""></a>
                                </div>
                                <div class="del">
                                    <a href="<?php echo getLinkAdmin('services','delete',['id' => $item['id']]); ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa dịch vụ không ?')"><img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/service-delete.svg" alt=""></a>
                                </div>
                            </div>
                        </div>
                    <?php
                }
             ?>
        </div>

        <div class="service-right">
                <div class="right-inner">
                    <div class="inner-left">
                        <h3>Khách thuê sử dụng trong tháng</h3>
                        <i>Thống kê mỗi tháng khách thuê sử dụng</i>
                    </div>

                    <div class="inner-right">
                        <!-- Tìm kiếm -->
                        <form action="" method="get">
                            <div class="row">
                                <div class="col-8">
                                    <input style="height: 50px" type="month" class="form-control" name="datebill" id="" value="<?php echo (!empty($datebill))? $datebill:$currentMonthYear; ?>">
                                </div>

                                <div class="col">
                                        <button style="height: 50px; width: 50px" type="submit" class="btn btn-secondary"> <i class="fa fa-search"></i></button>
                                 </div>   
                            </div>
                            <input type="hidden" name="module" value="services">
                        </form>
                    </div>
                </div>

                <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th width="3%" rowspan="2"></th>
                        <th rowspan="2">Tên phòng</th>
                        <th colspan="3">Tiền điện</th>
                        <th colspan="3">Tiền nước</th>
                        <th colspan="2">Tiền rác</th>
                        <th colspan="2">Tiền Wifi</th>
                    </tr>
                    <tr>
                        <th>Số cũ</th>
                        <th>Số mới</th>
                        <th>Thành tiền</th>
                        <th>Số cũ</th>
                        <th>Số mới</th>
                        <th>Thành tiền</th>
                        <th>Người</th>
                        <th>Thành tiền</th>
                        <th>Tháng</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody id="roomData">
        
                    <?php
                        if (!empty($listAllBill)):
                            $count = 0; // Hiển thi số thứ tự
                            foreach ($listAllBill as $item):
                                $count ++;
                    ?>
                     <tr>
                        <td>
                            <div class="image__bill">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/bill-icon.svg" class="image__bill-img" alt="">
                            </div>
                        </td>
                        <td><?php echo $item['tenphong']; ?></td>
                        <td><?php echo $item['sodiencu']; ?></td>
                        <td><?php echo $item['sodienmoi']; ?></td>
                        <td style="color: #13ae38"><b><?php echo number_format($item['tiendien'], 0, ',', '.') ?> đ</b></td>
                        <td><?php echo $item['sonuoccu']; ?></td>
                        <td><?php echo $item['sonuocmoi']; ?></td>
                        <td style="color: #13ae38"><b><?php echo number_format($item['tiennuoc'], 0, ',', '.') ?> đ</b></td>
                        <td><?php echo $item['songuoi']; ?></td>
                        <td style="color: #13ae38"><b><?php echo number_format($item['tienrac'], 0, ',', '.') ?> đ</b></td>
                        <td><?php echo $item['chuky']; ?></td>
                        <td style="color: #13ae38"><b><?php echo number_format($item['tienmang'], 0, ',', '.') ?> đ</b></td>
                    </tr>                
                         
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="19">
                                <div class="alert alert-danger text-center">Không có dữ liệu dịch vụ</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
layout('footer', 'admin');
?>
<script>
    function toggle(__this){
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
