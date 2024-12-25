<?php

if (!defined('_INCODE'))
    die('Access denied...');

$data = [
    'pageTitle' => 'Danh sách phiếu thu'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$allCollect = getRaw("SELECT id, tendanhmuc FROM category_collect");


$filter = '';
$collectId = null;
if (isGet()) {
    $body = getBody('get');

    // Xử lý lọc theo groups
    if (!empty($body['collect_id'])) {
        $collectId = $body['collect_id'];

        if (!empty($filter) && strpos($filter, 'WHERE') >= 0) {
            $operator = 'AND';
        } else {
            $operator = 'WHERE';
        }

        $filter .= " $operator receipt.danhmucthu_id = $collectId";
    }
}

/// Xử lý phân trang
$allReceipt = getRows("SELECT id FROM receipt $filter");

$listAllReceipt = getRaw("SELECT *, tenphong, tendanhmuc, receipt.id FROM receipt INNER JOIN room ON room.id = receipt.room_id 
INNER JOIN category_collect ON category_collect.id = receipt.danhmucthu_id $filter ORDER BY receipt.id DESC ");

// Xóa hết
if (isset($_POST['deleteMultip'])) {
    $numberCheckbox = $_POST['records'];
    $extract_id = implode(',', $numberCheckbox);
    $checkDelete = delete('contract', "id IN($extract_id)");
    if ($checkDelete) {
        setFlashData('msg', 'Xóa thông tin phiếu thu thành công');
        setFlashData('msg_type', 'suc');
    }
    redirect('?module=contract');
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

    <!-- Tìm kiếm -->
    <div class="box-content">
        <form action="" method="get">
            <div class="row">
                <div class="col-3">
                    <div class="form-group">
                        <select name="collect_id" id="" class="form-select">
                            <option value="">Chọn danh mục thu</option>
                            <?php

                            if (!empty($allCollect)) {
                                foreach ($allCollect as $item) {
                            ?>
                                    <option value="<?php echo $item['id'] ?>" <?php echo (!empty($collectId) && $collectId == $item['id']) ? 'selected' : false; ?>><?php echo $item['tendanhmuc'] ?></option>

                            <?php
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="col">
                    <button style="height: 50px; width: 50px" type="submit" class="btn btn-secondary"> <i class="fa fa-search"></i></button>
                </div>
            </div>
            <input type="hidden" name="module" value="receipt">
            <input type="hidden" name="action" value="receipts">
        </form>
        <form action="" method="POST" class="mt-3">
            <div>
            </div>
            <a style="margin-right: 5px" href="<?php echo getLinkAdmin('bill', '') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
            <a href="<?php echo getLinkAdmin('receipt', 'add') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-plus"></i> Thêm</a>
            <a href="<?php echo getLinkAdmin('receipt'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Khoản</th>
                        <th>Loại</th>
                        <th>Tên phòng</th>
                        <th>Số tiền</th>
                        <th>Ghi chú</th>
                        <th>Ngày phát sinh</th>
                        <th>Phương thức thanh toán</th>
                        <th style="width: 4%; text-align:center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="contractData">

                    <?php
                    if (!empty($listAllReceipt)):
                        $count = 0; // Hiển thi số thứ tự
                        foreach ($listAllReceipt as $item):
                            $count++;
                    ?>
                            <tr>
                                <td style="text-align: center"><?php echo $count; ?></td>
                                <td style="color: dark; text-align: center;"><b><?php echo $item['tendanhmuc']; ?></b></td>
                                <td style="text-align: center"><span style="background: #15A05C; color: #fff; padding: 2px 4px; border-radius: 5px; font-size: 12px">Khoản thu</span></td>
                                <td style="text-align: center"><?php echo $item['tenphong'] ?></td>
                                <td style="text-align: center"><b><?php echo number_format($item['sotien'], 0, ',', '.') ?> đ</b></td>
                                <td style="text-align: center"><?php echo $item['ghichu'] ?></td>
                                <td style="text-align: center"><?php echo getDateFormat($item['ngaythu'], 'd-m-Y'); ?></td>
                                <td style="text-align: center"><?php echo $item['phuongthuc'] == 0 ? '<span class="btn-kyhopdong-second">Tiền mặt</span>' : '<span class="btn-kyhopdong-second">Chuyển khoản</span>' ?></td>
                                <td class="" style="text-align: center;">
                                    <div class="action">
                                        <button type="button" class="btn btn-secondary btn-sm"><i class="fa fa-ellipsis-v"></i></button>
                                        <div class="box-action">
                                            <a title="Xem phiếu thu" target="_blank" href="<?php echo getLinkAdmin('receipt', 'view', ['id' => $item['id']]) ?>" class="btn btn-primary btn-sm"><i class="nav-icon fas fa-solid fa-eye"></i> </a>
                                            <a href="<?php echo getLinkAdmin('receipt', 'edit', ['id' => $item['id']]); ?>" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i> </a>
                                            <a href="<?php echo getLinkAdmin('receipt', 'delete', ['id' => $item['id']]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa không ?')"><i class="fa fa-trash"></i> </a>
                                        </div>
                                </td>

                            <?php endforeach;
                    else: ?>
                            <tr>
                                <td colspan="15">
                                    <div class="alert alert-danger text-center">Không có dữ liệu phiếu thu</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                </tbody>
            </table>
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
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select all action buttons
        const actionButtons = document.querySelectorAll('.action');

        actionButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                // Prevent event bubbling
                event.stopPropagation();

                // Toggle the active class
                button.classList.toggle('active');

                // Hide all other .box-action elements
                actionButtons.forEach(btn => {
                    if (btn !== button) {
                        btn.classList.remove('active');
                    }
                });
            });
        });

        // Hide .box-action when clicking outside
        document.addEventListener('click', function(event) {
            actionButtons.forEach(button => {
                button.classList.remove('active');
            });
        });

        // Prevent .box-action click from closing itself
        const boxActions = document.querySelectorAll('.box-action');
        boxActions.forEach(box => {
            box.addEventListener('click', function(event) {
                event.stopPropagation();
            });
        });
    });

    function toggle(__this) {
        let isChecked = __this.checked;
        let checkbox = document.querySelectorAll('input[name="records[]"]');
        for (let index = 0; index < checkbox.length; index++) {
            checkbox[index].checked = isChecked
        }
    }
</script>