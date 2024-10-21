<?php

if (!defined('_INCODE')) die('Access denied...');

// Ngăn chặn quyền truy cập nếu người dùng không thuộc nhóm có quyền
$userId = isLogin()['user_id'];
$userDetail = getUserInfo($userId);
$groupId = $userDetail['group_id'];

// Kiểm tra nếu người dùng không thuộc nhóm 7 thì chặn truy cập
if ($groupId != 7) {
    setFlashData('msg', 'Bạn không được truy cập vào trang này');
    setFlashData('msg_type', 'err');
    redirect('admin/?module=dashboard');
}

$data = [
    'pageTitle' => 'Danh sách bảng giá'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');

layout('navbar', 'admin', $data);

// Khởi tạo biến để lưu giá trị tìm kiếm
$searchTerm = '';
$searchResults = [];

// Xử lý tìm kiếm
if (isset($_POST['search'])) {
    $searchTerm = $_POST['search_term'];
    $searchTerm = htmlspecialchars($searchTerm); // Bảo mật đầu vào

    // Truy vấn tìm kiếm tên khuyến mãi
    $query = "SELECT * FROM cost WHERE tengia LIKE '%$searchTerm%'"; // Thêm điều kiện tìm kiếm
    $searchResults = executeResult($query); // Lấy kết quả tìm kiếm
} else {
    // Nếu không tìm kiếm, lấy toàn bộ dữ liệu
    $query = "SELECT * FROM cost";
    $searchResults = executeResult($query);
}
?>

<div class="container-fluid">

    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

    <!-- Hiển thị danh sách bảng giá -->
    <div class="box-content">
        <form method="POST" action="">
            <table class="table table-bordered mt-3">
                <div class="row">
                    <div class="col-4"></div>
                    <div class="col-4">
                        <input style="height: 50px" type="search" name="search_term" class="form-control" placeholder="Nhập tên khuyến mãi cần tìm" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    </div>
                    <div class="col">
                        <button style="height: 50px; width: 50px" type="submit" name="search" class="btn btn-secondary">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="module" value="cost">
                <p></p>
                <a href="<?php echo getLinkAdmin('cost', 'addcost') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-plus"></i> Thêm mới </a>
                <a href="<?php echo getLinkAdmin('cost', 'lists'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
                <button type="submit" name="deleteMultip" value="Delete" onclick="return confirm('Bạn có chắn chắn muốn xóa không ?')" class="btn btn-secondary"><i class="fa fa-trash"></i> Xóa</button>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="check-all" onclick="toggle(this)"></th>
                        <th>STT</th>
                        <th>Tên giá</th>
                        <th>Giá thuê</th>
                        <th>Ngày bắt đầu</th>
                        <th>Ngày kết thúc</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="costData">
                    <?php
                    // Hiển thị danh sách bảng giá
                    if (!empty($searchResults)):
                        $count = 0;
                        foreach ($searchResults as $item):
                            $count++;
                    ?>
                            <tr>
                                <td><input type="checkbox" name="records[]" value="<?php echo $item['id']; ?>"></td>
                                <td><?php echo $count; ?></td>
                                <td><b><?php echo $item['tengia']; ?></b></td>
                                <td><?php echo number_format($item['giathue'], 0, ',', '.'); ?> VND</td>
                                <td><?php echo getDateFormat($item['ngaybatdau'], 'd-m-Y'); ?></td>
                                <td><?php echo getDateFormat($item['ngayketthuc'], 'd-m-Y'); ?></td>
                                <td class="" style="width: 100px; height: 50px;">
                                    <a href="<?php echo getLinkAdmin('cost', 'editcostroom', ['id' => $item['id']]); ?>" class="btn btn-primary btn-sm" style="margin-right: 9px;"><i class="fa fa-edit"></i> </a>
                                    <a href="<?php echo getLinkAdmin('', '', ['id' => $item['id']]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa không ?')"><i class="fa fa-trash"></i> </a>
                                </td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="alert alert-danger text-center">Không tìm thấy kết quả nào</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    </div>

</div>

<?php layout('footer', 'admin'); ?>