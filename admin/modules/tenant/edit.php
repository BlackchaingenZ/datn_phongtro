<?php

if(!defined('_INCODE'))
die('Access denied...');


$data = [
    'pageTitle' => 'Cập nhật thông tin khách thuê'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$allRoom = getRaw("SELECT id, tenphong FROM room ORDER BY tenphong");


// Xử lý hiện dữ liệu cũ của người dùng
$body = getBody();
$id = $_GET['id'];


if(!empty($body['id'])) {
    $tenantId = $body['id'];   
    $tenantDetail  = firstRaw("SELECT * FROM tenant WHERE id=$tenantId");
    if (!empty($tenantDetail)) {
        // Gán giá trị tenantDetail vào setFalsh
        setFlashData('tenantDetail', $tenantDetail);
    
    }else {
        redirect('/admin/?module=tenant');
    }
}

// Xử lý sửa người dùng
if(isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi
    
    // Valide họ tên: Bắt buộc phải nhập, >=5 ký tự
    if(empty(trim($body['tenkhach']))) {
        $errors['tenkhach']['required'] = '** Bạn chưa nhập tên khách thuê!';
    }else {
        if(strlen(trim($body['tenkhach'])) <= 5) {
        $errors['tenkhach']['min'] = '** Tên khách thuê phải lớn hơn 5 ký tự!';
        }
    }
  
   // Kiểm tra mảng error
  if(empty($errors)) {
    // không có lỗi nào
    $dataUpdate = [
        'tenkhach' => $body['tenkhach'],
        'sdt' => $body['sdt'],
        'ngaysinh' => $body['ngaysinh'],
        'gioitinh' => $body['gioitinh'],
        'diachi' => $body['diachi'],
        'nghenghiep' => $body['nghenghiep'],
        'cmnd' => $body['cmnd'],
        'ngaycap' => $body['ngaycap'],
        'anhmattruoc' => $body['anhmattruoc'],
        'anhmatsau' => $body['anhmatsau'],
        'zalo' => $body['zalo'],
        'room_id' => $body['room_id'],
    ];

    $condition = "id=$id";
    $updateStatus = update('tenant', $dataUpdate, $condition);
    if ($updateStatus) {
        setFlashData('msg', 'Cập nhật thông tin khách thuê thành công');
        setFlashData('msg_type', 'suc');
        redirect('admin/?module=tenant');
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

  redirect('/admin/?module=tenant&action=edit&id='.$tenantId);

}
$msg =getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

if (!empty($tenantDetail) && empty($old)) {
    $old = $tenantDetail;
}
?>
<?php
layout('navbar', 'admin', $data);
?>

    <div class="container">
        <hr/>

        <div class="box-content">
            <form action="" method="post" class="row">
                    <div class="col-5">
                        <div class="form-group">
                            <label for="">Tên khách <span style="color: red">*</span></label>
                            <input type="text" placeholder="Tên khách thuê" name="tenkhach" id="" class="form-control" value="<?php echo old('tenkhach', $old); ?>">
                            <?php echo form_error('tenkhach', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Số điện thoại <span style="color: red">*</span></label>
                            <input type="text" placeholder="Số điện thoại" name="sdt" id="" class="form-control" value="<?php echo old('sdt', $old); ?>">
                            <?php echo form_error('sdt', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Ngày sinh <span style="color: red">*</span></label>
                            <input type="date" name="ngaysinh" id="" class="form-control" value="<?php echo old('ngaysinh', $old); ?>">
                            <?php echo form_error('ngaysinh', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="gioitinh">Giới tính <span style="color: red">*</span></label>
                            <select name="gioitinh" id="gioitinh" class="form-select">
                                <option value="">Chọn giới tính</option>
                                <option value="Nam" <?php echo (old('gioitinh', $old) == 'Nam') ? 'selected' : ''; ?>>Nam</option>
                                <option value="Nữ" <?php echo (old('gioitinh', $old) == 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Địa chỉ <span style="color: red">*</span></label>
                            <input type="text" placeholder="Địa chỉ" name="diachi" id="" class="form-control" value="<?php echo old('diachi', $old); ?>">
                            <?php echo form_error('diachi', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Nghề nghiệp <span style="color: red">*</span></label>
                            <input type="text" placeholder="Nghề nghiệp" name="nghenghiep" id="" class="form-control" value="<?php echo old('nghenghiep', $old); ?>">
                            <?php echo form_error('nghenghiep', $errors, '<span class="error">', '</span>'); ?>
                        </div>
                    </div>

                    <div class="col-5">
                        <div class="form-group">
                            <label for="">Số CMND/CCCD <span style="color: red">*</span></label>
                            <input type="text" placeholder="Số CMND/CCCD" name="cmnd" id="" class="form-control" value="<?php echo old('cmnd', $old); ?>">
                            <?php echo form_error('cmnd', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Ngày cấp <span style="color: red">*</span></label>
                            <input type="date" name="ngaycap" id="" class="form-control" value="<?php echo old('ngaycap', $old); ?>">
                            <?php echo form_error('ngaycap', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="name">Ảnh mặt trước <span style="color: red">*</span></label>
                            <div class="row ckfinder-group">
                                <div class="col-11">
                                    <input type="text" placeholder="Ảnh mặt trước" name="anhmattruoc" id="name" class="form-control image-render" value="<?php echo old('anhmattruoc', $old); ?>">   
                                </div>
                                <div class="col-1">
                                    <button type="button" class="btn btn-warning choose-image"><i class="fa fa-upload"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="name">Ảnh mặt sau <span style="color: red">*</span></label>
                            <div class="row ckfinder-group">
                                <div class="col-11">
                                    <input type="text" placeholder="Ảnh mặt sau" name="anhmatsau" id="name" class="form-control image-render" value="<?php echo old('anhmatsau', $old); ?>">   
                                </div>
                                <div class="col-1">
                                    <button type="button" class="btn btn-warning choose-image"><i class="fa fa-upload"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="">Zalo <span style="color: red">*</span></label>
                            <input type="text" placeholder="Link zalo" name="zalo" id="" class="form-control" value="<?php echo old('zalo', $old); ?>">
                            <?php echo form_error('zalo', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Phòng đang ở <span style="color: red">*</span></label>
                            <select name="room_id" id="" class="form-select">
                                <option value="">Chọn phòng</option>
                                <?php
                                    if(!empty($allRoom)) {
                                        foreach($allRoom as $item) {
                                            ?>
                                                <option value="<?php echo $item['id'] ?>" <?php  echo (old('room_id', $old) == $item['id'])?'selected':false; ?>><?php echo $item['tenphong'] ?></option> 
                                            <?php
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                    
                    </div>                  
                    <div class="from-group">                    
                            <div class="btn-row">
                                <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Cập nhật</button>
                                <a style="margin-left: 20px " href="<?php echo getLinkAdmin('tenant') ?>" class="btn btn-success"><i class="fa fa-forward"></i></a>
                            </div>
                    </div>
            </form>
        </div>
    </div>


<?php
layout('footer', 'admin');





