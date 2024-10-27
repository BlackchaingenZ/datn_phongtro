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
    'pageTitle' => 'Danh sách khu vực'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');

// Lấy danh sách khu vực từ cơ sở dữ liệu
$listAllArea = getRaw("SELECT id AS id, tenkhuvuc, mota, ngaytao FROM area ORDER BY tenkhuvuc ASC");


if (isset($_POST['deleteMultip'])) {
    $numberCheckbox = $_POST['records']; // Lấy các ID thiết bị đã chọn
    if (empty($numberCheckbox)) {
        setFlashData('msg', 'Bạn chưa chọn mục nào để xóa!');
        setFlashData('msg_type', 'err');
    } else {
        // Chuyển mảng các ID thiết bị thành chuỗi để sử dụng trong câu truy vấn SQL
        $extract_id = implode(',', array_map('intval', $numberCheckbox));

        try {
            // Kiểm tra trước nếu có khu vưc nào đang được sử dụng trong bảng room
            $sqlCheck = "SELECT COUNT(*) AS count FROM area_room WHERE area_id IN ($extract_id)";
            $count = getRow($sqlCheck)['count'];

            if ($count > 0) {
                // Nếu khu vực đang được sử dụng trong phòng, không thực hiện xóa
                setFlashData('msg', 'Không thể xóa vì đang chứa phòng nào đó!');
                setFlashData('msg_type', 'err');
                redirect('?module=area&action=listarea');
                exit(); // Dừng việc thực hiện thêm
            } else {
                // Thực hiện xóa các thiết bị đã chọn từ cơ sở dữ liệu nếu không có thiết bị nào đang được sử dụng
                $checkDelete = delete('cost', "id IN($extract_id)");

                if ($checkDelete) {
                    setFlashData('msg', 'Xóa  thành công');
                    setFlashData('msg_type', 'suc');
                } else {
                    setFlashData('msg', 'Có lỗi xảy ra khi xóa loại giá');
                    setFlashData('msg_type', 'err');
                }
            }
        } catch (PDOException $e) {
            setFlashData('msg', 'Đã xảy ra lỗi: ' . $e->getMessage());
            setFlashData('msg_type', 'err');
        }
    }
    redirect('?module=area&action=listarea'); // Chuyển hướng về trang danh sách
}


layout('navbar', 'admin', $data);

// Khởi tạo biến để lưu giá trị tìm kiếm
$searchTerm = '';
$searchResults = [];

// Xử lý tìm kiếm
if (isset($_POST['search'])) {
    $searchTerm = $_POST['search_term'];
    $searchTerm = htmlspecialchars($searchTerm); // Bảo mật đầu vào

    // Truy vấn tìm kiếm tên khuyến mãi
    $query = "SELECT * FROM area WHERE tenkhuvuc LIKE '%$searchTerm%'"; // Thêm điều kiện tìm kiếm
    $searchResults = executeResult($query); // Lấy kết quả tìm kiếm
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
                </div>
                <input type="hidden" name="module" value="cost">
                <p></p>
                <a style="margin-right: 5px" href="<?php echo getLinkAdmin('area', '') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                <a href="<?php echo getLinkAdmin('area', 'addarea') ?>" class="btn btn-secondary" style="color: #fff"><i class="fa fa-plus"></i> Thêm khu vực mới </a>
                <a href="<?php echo getLinkAdmin('area', 'listarea'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
                <button type="submit" name="deleteMultip" value="Delete" onclick="return confirm('Bạn có chắn chắn muốn xóa không ?')" class="btn btn-secondary"><i class="fa fa-trash"></i> Xóa</button>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="check-all" onclick="toggle(this)"></th>
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
                            <tr>
                                <td><input type="checkbox" name="records[]" value="<?php echo $item['id']; ?>"></td>
                                <td><?php echo $count; ?></td>
                                <td><b><?php echo $item['tenkhuvuc']; ?></b></td>
                                <td><b><?php echo $item['mota']; ?></b></td>
                                <td><?php echo $item['ngaytao']; ?></td>
                                <td class="" style="width: 100px; height: 50px;">
                                    <a href="<?php echo getLinkAdmin('area', 'editarea', ['id' => $item['id']]); ?>" class="btn btn-primary btn-sm" style="margin-right: 9px;"><i class="fa fa-edit"></i> </a>
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

<script>
    function toggle(__this) {
        let isChecked = __this.checked;
        let checkbox = document.querySelectorAll('input[name="records[]"]');
        for (let index = 0; index < checkbox.length; index++) {
            checkbox[index].checked = isChecked;
        }
    }
</script>