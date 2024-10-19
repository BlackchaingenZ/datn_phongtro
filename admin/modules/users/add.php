<?php

if(!defined('_INCODE'))
die('Access denied...');

$data = [
    'pageTitle' => 'Thêm người dùng'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Truy vấn lấy ra danh sách nhóm
$allGroups = getRaw("SELECT id, name FROM groups ORDER BY id");
$allRoom = getRaw("SELECT id, tenphong, soluong FROM room ORDER BY tenphong");

// Xử lý thêm người dùng
if(isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi
    
    //Valide họ tên: Bắt buộc phải nhập, >=5 ký tự
    if(empty(trim($body['fullname']))) {
        $errors['fullname']['required'] = '** Bạn chưa nhập tên người dùng';
    }

    // Validate email
    if(empty(trim($body['email']))) {
        $errors['email']['required'] = '** Bạn chưa nhập email';
    }

    // Validate password
    if(empty(trim($body['password']))) {
        $errors['password']['required'] = '** Bạn chưa nhập mật khẩu';
    }

    // Validate confirm password
    if(empty(trim($body['confirm_password']))) {
        $errors['confirm_password']['required'] = '** Bạn chưa nhập lại mật khẩu';
    } elseif($body['password'] !== $body['confirm_password']) {
        $errors['confirm_password']['match'] = '** Mật khẩu không khớp';
    }
   
    // Kiểm tra mảng error
    if(empty($errors)) {
        // không có lỗi nào
        $room_id = !empty($body['room_id']) ? $body['room_id'] : NULL;

        $dataInsert = [
            'fullname' => $body['fullname'],
            'email' => $body['email'],
            'group_id' => $body['group_id'],
            'password' => password_hash($body['password'], PASSWORD_DEFAULT),
            'room_id' => $room_id,
            'status' => $body['status'], 
            'create_at' => date('Y-m-d H:i:s'),
        ];

        $insertStatus = insert('users', $dataInsert);
        if ($insertStatus) {
            setFlashData('msg', 'Thêm thông tin người dùng thành công');
            setFlashData('msg_type', 'suc');
            redirect('admin/?module=users');
        }
    } else {
        // Có lỗi xảy ra
        setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
        setFlashData('msg_type', 'err');
        setFlashData('errors', $errors);
        setFlashData('old', $body);  // giữ lại các trường dữ liệu hợp lệ khi nhập vào
        redirect('admin/?module=users&action=add'); 
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

<div class="container">
    <div id="MessageFlash">
        <?php getMsg($msg, $msgType);?> 
    </div>

    <div class="box-content">
        <form action="" method="post" class="row" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="col-5">
                <div class="form-group">
                    <label for="">Tên người dùng <span style="color: red">*</span></label>
                    <input type="text" placeholder="Tên người dùng" name="fullname" id="" class="form-control" value="<?php echo old('fullname', $old); ?>">
                    <?php echo form_error('fullname', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Email <span style="color: red">*</span></label>
                    <input type="text" placeholder="Email" name="email" id="" class="form-control">
                    <?php echo form_error('email', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Mật khẩu</label>
                    <input type="password" placeholder="Nhập mật khẩu" name="password" id="" class="form-control">
                    <?php echo form_error('password', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Nhập lại mật khẩu</label>
                    <input type="password" placeholder="Nhập lại mật khẩu" name="confirm_password" id="" class="form-control">
                    <?php echo form_error('confirm_password', $errors, '<span class="error">', '</span>'); ?>
                </div>
            </div>

            <div class="col-5">
                <div class="form-group">
                    <label for="">Nhóm người dùng</label>
                    <select name="group_id" id="" class="form-control">
                        <option value="">Chọn nhóm</option>
                        <?php
                            if(!empty($allGroups)) {
                                foreach($allGroups as $item) {
                        ?>
                            <option value="<?php echo $item['id'] ?>" <?php  echo (!empty($groupId) && $groupId == $item['id'])?'selected':false; ?>><?php echo $item['name'] ?></option> 
                        <?php
                                }
                            }
                        ?>
                    </select>
                    <?php echo form_error('group_id', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Phòng đang ở</label>
                    <select name="room_id" id="" class="form-select">
                        <option value="">Chọn phòng</option>
                        <?php
                            if(!empty($allRoom)) {
                                foreach($allRoom as $item) {
                                    if($item['soluong'] < 2 ) {
                        ?>
                                        <option value="<?php echo $item['id'] ?>" <?php echo (!empty($roomId) && $roomId == $item['id'])?'selected':'' ?>><?php echo $item['tenphong'] ?></option> 
                        <?php
                                    }
                                }
                            }
                        ?>
                    </select>
                    <?php echo form_error('room_id', $errors, '<span class="error">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Chọn trạng thái</option>
                        <option value="0" <?php echo (old('status', $old==0)) ? 'selected':false;  ?>>Chưa kích hoạt</option>
                        <option value="1" <?php echo (old('status', $old==1)) ? 'selected':false; ?>>Kích hoạt</option>
                    </select>
                </div>                  
            </div>                  
            <div class="from-group">                    
                <div class="btn-row">
                    <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Thêm người dùng</button>
                    <a style="margin-left: 20px " href="<?php echo getLinkAdmin('users') ?>" class="btn btn-success"><i class="fa fa-forward"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
layout('footer', 'admin');
?>
