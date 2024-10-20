<?php

if (!defined('_INCODE')) die('Access denied...');

// Bao gồm tệp chứa hàm executeResult
require_once 'includes/functions.php'; // Đường dẫn tới tệp kết nối và hàm executeResult

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
    'pageTitle' => 'Thêm bảng giá mới'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');

// Kiểm tra nếu người dùng đã gửi form
if (isset($_POST['addcost'])) {
    $tengia = trim($_POST['tengia']);
    $giathue = $_POST['giathue'];
    $ngaybatdau = $_POST['ngaybatdau'];
    $ngayketthuc = $_POST['ngayketthuc'];

    // Kiểm tra nếu các trường không rỗng
    if (!empty($tengia) && !empty($giathue) && !empty($ngaybatdau) && !empty($ngayketthuc)) {

        // Truy vấn thêm bảng giá mới vào cơ sở dữ liệu
        $query = "INSERT INTO cost (tengia, giathue, ngaybatdau, ngayketthuc) VALUES (:tengia, :giathue, :ngaybatdau, :ngayketthuc)";
        $params = [
            ':tengia' => $tengia,
            ':giathue' => $giathue,
            ':ngaybatdau' => $ngaybatdau,
            ':ngayketthuc' => $ngayketthuc
        ];
        $isInserted = executeResult($query, $params);

        if ($isInserted) {
            setFlashData('msg', 'Thêm bảng giá thất bại');
            setFlashData('msg_type', 'err');
            redirect('?module=cost&action=addcost');
        } else {
            setFlashData('msg', 'Thêm bảng giá thành công');
            setFlashData('msg_type', 'success');
            redirect('?module=cost&action=costroom');
        }
    } else {
        setFlashData('msg', 'Vui lòng điền đầy đủ thông tin');
        setFlashData('msg_type', 'err');
    }
}

?>

<div class="container-fluid">

    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

    <!-- Form thêm bảng giá mới -->
    <div class="container">
        <div class="box-content">

            <form action="" method="post" class="row">
                
                <div class="form-group">
                    <label for="tengia">Tên giá:</label>
                    <input type="text" name="tengia" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="giathue">Giá thuê:</label>
                    <input type="number" name="giathue" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="ngaybatdau">Ngày bắt đầu:</label>
                    <input type="date" name="ngaybatdau" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="ngayketthuc">Ngày kết thúc:</label>
                    <input type="date" name="ngayketthuc" class="form-control" required>
                </div>
                <button type="submit" name="addcost" class="btn btn-primary">Thêm bảng giá</button>
            </form>
        </div>
    </div>
</div>

<?php layout('footer', 'admin'); ?>
