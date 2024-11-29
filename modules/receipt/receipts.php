<?php

if (!defined('_INCODE'))
    die('Access denied...');

$data = [
    'pageTitle' => 'Danh sách phiếu thu'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$allCollect = getRaw("SELECT id, tendanhmuc FROM category_collect");

// Xử lý lọc dữ liệu
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
$perPage = _PER_PAGE; // Mỗi trang có 3 bản ghi
$maxPage = ceil($allReceipt / $perPage);

// 3. Xử lý số trang dựa vào phương thức GET
if (!empty(getBody()['page'])) {
    $page = getBody()['page'];
    if ($page < 1 and $page > $maxPage) {
        $page = 1;
    }
} else {
    $page = 1;
}
$offset = ($page - 1) * $perPage;

$listAllReceipt = getRaw("SELECT *, tenphong, tendanhmuc, receipt.id FROM receipt INNER JOIN room ON room.id = receipt.room_id 
INNER JOIN category_collect ON category_collect.id = receipt.danhmucthu_id $filter LIMIT $offset, $perPage");

// Xử lý query string tìm kiếm với phân trang
$queryString = null;
if (!empty($_SERVER['QUERY_STRING'])) {
    $queryString = $_SERVER['QUERY_STRING'];
    $queryString = str_replace('module=contract', '', $queryString);
    $queryString = str_replace('&page=' . $page, '', $queryString);
    $queryString = trim($queryString, '&');
    $queryString = '&' . $queryString;
}

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
            <a href="<?php echo getLinkAdmin('receipt', 'export'); ?>" class="btn btn-secondary"><i class="fa fa-save"></i> Xuất Excel</a>
            <!--<a style="margin-left: 20px " href="<?php echo getLinkAdmin('sumary') ?>" class="btn btn-secondary"><i class="fa fa-forward"></i></a>-->
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th></th>
                        <th wìdth="5%">STT</th>
                        <th>Khoản</th>
                        <th>Loại</th>
                        <th>Tên phòng</th>
                        <th>Số tiền</th>
                        <th>Ghi chú</th>
                        <th>Ngày phát sinh</th>
                        <th>Phương thức thanh toán</th>
                        <th>Thao tác</th>
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
                                <td>
                                    <div class="image__bill">
                                        <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/bill-icon.svg" class="image__bill-img" alt="">
                                    </div>
                                </td>
                                <td><?php echo $count; ?></td>
                                <td style="color: green"><b><?php echo $item['tendanhmuc']; ?></b></td>
                                <td><span style="background: #15A05C; color: #fff; padding: 2px 4px; border-radius: 5px; font-size: 12px">Khoản thu</span></td>
                                <td><?php echo $item['tenphong'] ?></td>
                                <td><b><?php echo number_format($item['sotien'], 0, ',', '.') ?> đ</b></td>
                                <td><?php echo $item['ghichu'] ?></td>
                                <td><?php echo getDateFormat($item['ngaythu'], 'd-m-Y'); ?></td>
                                <td style="text-align: center"><?php echo $item['phuongthuc'] == 0 ? '<span class="btn-kyhopdong-second">Tiền mặt</span>' : '<span class="btn-kyhopdong-second">Chuyển khoản</span>' ?></td>
                                <td class="">
                                    <a title="In hợp đồng" target="_blank" href="<?php echo getLinkAdmin('receipt', 'print', ['id' => $item['id']]) ?>" class="btn btn-secondary btn-sm"><i class="fa fa-print"></i> </a>
                                    <a href="<?php echo getLinkAdmin('receipt', 'edit', ['id' => $item['id']]); ?>" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i> </a>
                                    <a href="<?php echo getLinkAdmin('receipt', 'delete', ['id' => $item['id']]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa không ?')"><i class="fa fa-trash"></i> </a>
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

            <nav aria-label="Page navigation example" class="d-flex justify-content-center">
                <ul class="pagination pagination-sm">
                    <?php
                    if ($page > 1) {
                        $prePage = $page - 1;
                        echo '<li class="page-item"><a class="page-link" href="' . _WEB_HOST_ROOT . '/?module=receipt' . $queryString . '&page=' . $prePage . '">Pre</a></li>';
                    }
                    ?>

                    <?php
                    // Giới hạn số trang
                    $begin = $page - 2;
                    $end = $page + 2;
                    if ($begin < 1) {
                        $begin = 1;
                    }
                    if ($end > $maxPage) {
                        $end = $maxPage;
                    }
                    for ($index = $begin; $index <= $end; $index++) {  ?>
                        <li class="page-item <?php echo ($index == $page) ? 'active' : false; ?> ">
                            <a class="page-link" href="<?php echo _WEB_HOST_ROOT . '?module=receipt' . $queryString . '&page=' . $index;  ?>"> <?php echo $index; ?> </a>
                        </li>
                    <?php  } ?>

                    <?php
                    if ($page < $maxPage) {
                        $nextPage = $page + 1;
                        echo '<li class="page-item"><a class="page-link" href="' . _WEB_HOST_ROOT . '?module=receipt' . $queryString . '&page=' . $nextPage . '">Next</a></li>';
                    }
                    ?>
                </ul>
            </nav>
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