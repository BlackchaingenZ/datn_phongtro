<?php

if(!defined('_INCODE'))
die('Access denied...');


$data = [
    'pageTitle' => 'Cập nhật thông tin người dùng'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Truy vấn lấy ra danh sách nhóm
$allGroups = getRaw("SELECT id, name FROM groups ORDER BY id");
$allRoom = getRaw("SELECT id, tenphong, soluong FROM room ORDER BY tenphong");


// Xử lý hiện dữ liệu cũ của người dùng
$body = getBody();
$id = $_GET['id'];


if(!empty($body['id'])) {
    $userId = $body['id'];   
    $userDetail  = firstRaw("SELECT * FROM users WHERE id=$userId");
    if (!empty($userDetail)) {
        // Gán giá trị userDetail vào setFalsh
        setFlashData('userDetail', $userDetail);
    
    }else {
        redirect('/admin/?module=users');
    }
}

// Xử lý sửa người dùng
if(isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi
    
    //Validate nhập lại Password: Bắt buộc phải nhập, giống Password
   if (!empty(trim($body['password']))) {
    // Chỉ validate comfirm_password nếu password được nhập 
       if(empty(trim($body['confirm_password']))) {
            $errors['confirm_password']['required'] = '** Bạn chưa xác nhận lại mật khẩu !';
       }else{
            if(trim($body['password']) != trim($body['confirm_password'])) {
                $errors['confirm_password']['match'] = '** Xác nhận lại mật khẩu chưa trùng khớp';
            }
       }
   }
  
   // Kiểm tra mảng error
  if(empty($errors)) {
    // không có lỗi nào
    $room_id = !empty($body['room_id']) ? $body['room_id'] : NULL;

    $dataUpdate = [
        'fullname' => $body['fullname'],
        'email' => $body['email'],
        'group_id' => $body['group_id'],
        'room_id' => $room_id,
        'status' => $body['status'], 
    ];

    // Trường hợp password được sửa
    if (!empty(trim($body['password']))) { 
        $dataUpdate['password'] = password_hash($body['password'], PASSWORD_DEFAULT);
    }

    $condition = "id=$id";
    $updateStatus = update('users', $dataUpdate, $condition);
    if ($updateStatus) {
        setFlashData('msg', 'Cập nhật thông tin người dùng thành công');
        setFlashData('msg_type', 'suc');
        redirect('admin/?module=users');
    }else {
        setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
        setFlashData('msg_type', 'err');
}

  } else {
    // Có lỗi xảy ra
    setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
    setFlashData('msg_type', 'err');
    setFlashData('errors', $errors);
    setFlashData('old', $body);  // giữ lại các trường dữ liệu hợp lê khi nhập vào
  }

  redirect('/admin/?module=users&action=edit&id='.$userId);

}
$msg =getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

if (!empty($userDetail) && empty($old)) {
    $old = $userDetail;
}
?>
<?php
layout('navbar', 'admin', $data);
?>

    <div class="container">
        <hr/>

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
                            <input type="text" placeholder="Email" name="email" id="" class="form-control" value="<?php echo old('email', $old); ?>">
                            <?php echo form_error('email', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Nhóm người dùng</label>
                            <select name="group_id" id="" class="form-control">
                                <option value="">Chọn nhóm</option>
                                <?php

                                    if(!empty($allGroups)) {
                                        foreach($allGroups as $item) {
                                    ?>
                                        <option value="<?php echo $item['id'] ?>" <?php  echo (old('group_id', $old) == $item['id'])?'selected':false; ?>><?php echo $item['name'] ?></option> 
                                    
                                    <?php
                                        }
                                    }
                                    ?>
                            </select>
                            <?php echo form_error('group_id', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Phòng đang ở <span style="color: red">*</span></label>
                            <select name="room_id" id="" class="form-select">
                                <option value="">Chọn phòng</option>
                                <?php
                                    if(!empty($allRoom)) {
                                        foreach($allRoom as $item) {
                                            if($item['soluong'] < 2 ) {
                                                ?>
                                                    <option value="<?php echo $item['id'] ?>" <?php echo (old('room_id', $old) == $item['id'])?'selected':'' ?>><?php echo $item['tenphong'] ?></option> 
                                                <?php
                                            }
                                        }
                                    }
                                ?>
                            </select>
                            <?php echo form_error('room_id', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                    </div>

                    <div class="col-5">
                        
                        <div class="form-group">
                            <label for="">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="">Chọn trạng thái</option>
                                <option value="0" <?php echo (old('status', $old) == 0) ? 'selected':false; ?>>Chưa kích hoạt</option>
                                <option value="1" <?php echo (old('status', $old) == 1) ? 'selected':false; ?>>Đã kích hoạt</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Mật khẩu</label>
                            <input type="password" name="password" id="" class="form-control" placeholder="Không nhập nếu không thay đổi">
                            <?php echo form_error('password', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Nhập lại mật khẩu</label>
                            <input type="password" name="confirm_password" id="" class="form-control" placeholder="Không nhập nếu không thay đổi">
                            <?php echo form_error('confirm_password', $errors, '<span class="error">', '</span>'); ?>
                        </div>            
                    </div>     

                    <div class="from-group">                    
                            <div class="btn-row">
                                <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Cập nhật người dùng</button>
                                <a style="margin-left: 20px " href="<?php echo getLinkAdmin('users') ?>" class="btn btn-success"><i class="fa fa-forward"></i></a>
                            </div>
                    </div>
                </form>
        </div>
    </div>


<?php
layout('footer', 'admin');





