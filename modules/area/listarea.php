<?php

if (!defined('_INCODE')) die('Access denied...');


$userId = isLogin()['user_id'];
$userDetail = getUserInfo($userId);
$groupId = $userDetail['group_id'];


if ($groupId != 7) {
    setFlashData('msg', 'Bạn không được truy cập vào trang này');
    setFlashData('msg_type', 'err');
    redirect('admin/?module=dashboard');
}

$data = [
    'pageTitle' => 'Danh sách khu vực'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');

// Lấy danh sách khu vực từ cơ sở dữ liệu
$listAllArea = getRaw("SELECT id AS id, tenkhuvuc, mota, ngaytao FROM area ORDER BY tenkhuvuc ASC");

layout('navbar', 'admin', $data);

// Khởi tạo biến để lưu giá trị tìm kiếm
$searchTerm = '';
$searchResults = [];

// Xử lý tìm kiếm
if (isset($_POST['search'])) {
    $searchTerm = $_POST['search_term'];
    $searchTerm = htmlspecialchars($searchTerm); 

    // Truy vấn tìm kiếm tên khuyến mãi
    $query = "SELECT * FROM area WHERE tenkhuvuc LIKE '%$searchTerm%'"; 
    $searchResults = executeResult($query); 
} else {
    // Nếu không tìm kiếm, lấy toàn bộ dữ liệu
    $query = "SELECT * FROM area";
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
                        <input style="height: 50px" type="search" name="search_term" class="form-control" placeholder="Nhập tên khu vực cần tìm" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    </div>
                    <div class="col">
                        <button style="height: 50px; width: 50px" type="submit" name="search" class="btn btn-secondary">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                    <!-- <div class="col-4">
                        <select style="height: 50px" name="search_term" class="form-control">
                            <option value=""></option>
                            <?php foreach ($listAllArea as $area): ?>
                                <option value="<?php echo $area['tenkhuvuc']; ?>" <?php echo ($searchTerm == $area['tenkhuvuc']) ? 'selected' : ''; ?>>
                                    <?php echo $area['tenkhuvuc']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col">
                        <button style="height: 50px; width: 50px" type="submit" name="search" class="btn btn-secondary">
                            <i class="fa fa-search"></i>
                        </button>
                    </div> -->
                </div>
                <input type="hidden" name="module" value="cost">
                <p></p>
                <a style="margin-right: 5px" href="<?php echo getLinkAdmin('area', '') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                <a href="<?php echo getLinkAdmin('area', 'addarea') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-plus"></i> Thêm khu vực </a>
                <a href="<?php echo getLinkAdmin('area', 'listarea'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên khu vực</th>
                        <th>Mô tả</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="EreaData">
                    <?php
                    // Hiển thị danh sách bảng giá
                    if (!empty($searchResults)):
                        $count = 0;
                        foreach ($searchResults as $item):
                            $count++;
                    ?>
                            <!-- <tr style="background-color: <?php echo ($item['tenkhuvuc'] == 'Khu A') ? 'red' : 'white'; ?>;"></tr> -->
                            <!-- <tr style="background-color:<?php echo (in_array($count, [1, 2, 3])) ? 'red' : (in_array($count, [4, 6]) ? 'green' : 'transparent'); ?>;"> -->
                            <tr>
                                <td style="text-align:left;color:back"><?php echo $count; ?></td>
                                <td><b><?php echo $item['tenkhuvuc']; ?></b></td>
                                <td><b><?php echo $item['mota']; ?></b></td>
                                <td><?php echo getDateFormat($item['ngaytao'], 'd-m-Y'); ?></td>
                                <td class="" style="width: 100px; height: 50px; text-align:center">
                                    <a href="<?php echo getLinkAdmin('area', 'editarea', ['id' => $item['id']]); ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> </a>
                                    <a href="<?php echo getLinkAdmin('area', 'deletearea', ['id' => $item['id']]); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa không ?')"><i class="fa fa-trash"></i> </a>
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