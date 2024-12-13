<?php

if (!defined('_INCODE'))
    die('Access denied...');

$data = [
    'pageTitle' => 'Danh sách phiếu chi'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$allSpend = getRaw("SELECT id, tendanhmuc FROM category_spend");
// Xử lý lọc dữ liệu
$filter = '';
$spendId = null;
if (isGet()) {
    $body = getBody('get');

    // Xử lý lọc theo danh mục chi
    if (!empty($body['payment_id'])) {
        $spendId = $body['payment_id'];

        if (!empty($filter) && strpos($filter, 'WHERE') !== false) {
            $operator = 'AND';
        } else {
            $operator = 'WHERE';
        }

        $filter .= " $operator payment.danhmucchi_id = $spendId";
    }
}

$listAllPayment = getRaw("SELECT *, tenphong, tendanhmuc, payment.id 
FROM payment LEFT JOIN room ON room.id = payment.room_id 
LEFT JOIN category_spend ON category_spend.id = payment.danhmucchi_id ORDER BY payment.id DESC  ");


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
                        <select name="payment_id" id="" class="form-select">
                            <option value="">Chọn danh mục chi</option>
                            <?php

                            if (!empty($allSpend)) {
                                foreach ($allSpend as $item) {
                            ?>
                                    <option value="<?php echo $item['id'] ?>" <?php echo (!empty($spendId) && $spendId == $item['id']) ? 'selected' : false; ?>><?php echo $item['tendanhmuc'] ?></option>

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
            <input type="hidden" name="module" value="payment">
        </form>
        <form action="" method="POST" class="mt-3">
            <div>

            </div>
            <a style="margin-right: 5px" href="<?php echo getLinkAdmin('receipt', '') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
            <a href="<?php echo getLinkAdmin('payment', 'add') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-plus"></i> Thêm</a>
            <a href="<?php echo getLinkAdmin('payment'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
            <a href="<?php echo getLinkAdmin('payment', 'export'); ?>" class="btn btn-secondary"><i class="fa fa-save"></i> Xuất Excel</a>
            <!--<a style="margin-left: 20px " href="<?php echo getLinkAdmin('sumary') ?>" class="btn btn-secondary"><i class="fa fa-forward"></i></a>-->
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Khoản</th>
                        <th>Loại</th>
                        <th>Tên Phòng</th>
                        <th>Số tiền</th>
                        <th>Ghi chú</th>
                        <th>Ngày phát sinh</th>
                        <th>Phương thức thanh toán</th>
                        <th style="width: 4%; text-align:center;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="contractData">

                    <?php
                    if (!empty($listAllPayment)):
                        $count = 0; // Hiển thi số thứ tự
                        foreach ($listAllPayment as $item):
                            $count++;
                    ?>

                            <tr>
                                <td style="text-align: center"><?php echo $count; ?></td>
                                <td style="color: green; text-align: center"><b><?php echo $item['tendanhmuc']; ?></b></td>
                                <td style="text-align: center"><span style="background: #d93025; color: #fff; padding: 2px 4px; border-radius: 5px; font-size: 12px">Khoản chi</span></td>
                                <td style="text-align: center"><?php echo $item['tenphong'] ?></td>
                                <td style="text-align: center"><b><?php echo number_format($item['sotien'], 0, ',', '.') ?> đ</b></td>
                                <td style="text-align: center"><?php echo $item['ghichu'] ?></td>
                                <td style="text-align: center"><?php echo getDateFormat($item['ngaychi'], 'd-m-Y'); ?></td>
                                <td style="text-align: center"><?php echo $item['phuongthuc'] == 0 ? '<span class="btn-kyhopdong-second">Tiền mặt</span>' : '<span class="btn-kyhopdong-second">Chuyển khoản</span>' ?></td>
                                <td class="" style="text-align: center;">
                                    <div class="action">
                                        <button type="button" class="btn btn-secondary btn-sm"><i class="fa fa-ellipsis-v"></i></button>
                                        <div class="box-action">
                                            <a title="Xem phiếu chi" target="_blank" href="<?php echo getLinkAdmin('payment', 'view', ['id' => $item['id']]) ?>" class="btn btn-primary btn-sm"><i class="nav-icon fas fa-solid fa-eye"></i> </a>
                                            <a href="<?php echo getLinkAdmin('payment', 'edit', ['id' => $item['id']]); ?>" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i> </a>
                                            <a href="<?php echo getLinkAdmin('payment', 'delete', ['id' => $item['id']]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa không ?')"><i class="fa fa-trash"></i> </a>
                                        </div>
                                </td>
                            <?php endforeach;
                    else: ?>
                            <tr>
                                <td colspan="15">
                                    <div class="alert alert-danger text-center">Không có dữ liệu phiếu chi</div>
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