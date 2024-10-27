<?php

if(!defined('_INCODE'))
die('Access denied...');


$data = [
    'pageTitle' => 'Cập nhật thông tin hợp đồng'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$allRoom = getRaw("SELECT id, tenphong FROM room ORDER BY tenphong");
$allTenant = getRaw("SELECT tenant.id, tenant.tenkhach, room.tenphong FROM tenant INNER JOIN room ON room.id = tenant.room_id ORDER BY tenphong");



// Xử lý hiện dữ liệu cũ của người dùng
$body = getBody();
$id = $_GET['id'];

if(!empty($body['id'])) {
    $contractId = $body['id'];   
    $contractDetail  = firstRaw("SELECT * FROM contract WHERE id=$contractId");
    if (!empty($contractDetail)) {
        // Gán giá trị contractDetail vào setFalsh
        setFlashData('contractDetail', $contractDetail);
    
    }else {
        redirect('?module=contract');
    }
}

// Xử lý sửa người dùng
if(isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi
      
   // Kiểm tra mảng error
  if(empty($errors)) {
    // không có lỗi nào
    $dataUpdate = [
        'room_id' => $body['room_id'],
        'tenant_id' => $body['tenant_id'],
        'tinhtrangcoc' => $body['tinhtrangcoc'],
        'ngaylaphopdong' => $body['ngaylaphopdong'],
        'ngayvao' => $body['ngayvao'],
        'ngayra' => $body['ngayra'],
    ];

    $condition = "id=$id";
    $updateStatus = update('contract', $dataUpdate, $condition);
    if ($updateStatus) {
        setFlashData('msg', 'Cập nhật thông tin hợp đồng thành công');
        setFlashData('msg_type', 'suc');
        redirect('?module=contract');
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

  redirect('?module=contract&action=edit&id='.$contractId);

}
$msg =getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

if (!empty($contractDetail) && empty($old)) {
    $old = $contractDetail;
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
                            <label for="">Phòng lập hợp đồng <span style="color: red">*</span></label>
                            <select name="room_id" id="" class="form-select">
                                <!-- <option value="">Chọn phòng</option> -->
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

                        <div class="form-group">
                            <label for="">Người đại diện <span style="color: red">*</span></label>
                            <select name="tenant_id" id="" class="form-select">
                                <!-- <option value="">Chọn người đại diện</option> -->
                                <?php
                                    if(!empty($allTenant)) {
                                        foreach($allTenant as $item) {
                                            ?>
                                                <option value="<?php echo $item['id'] ?>" <?php  echo (old('tenant_id', $old) == $item['id'])?'selected':false; ?>><?php echo $item['tenkhach']?> - <?php echo $item['tenphong'] ?></option> 
                                            <?php
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                                
                        <div class="form-group">
                            <label for="">Ngày lập hợp đồng <span style="color: red">*</span></label>
                            <input type="date" name="ngaylaphopdong" id="" class="form-control" value="<?php echo old('ngaylaphopdong', $old); ?>">
                            <?php echo form_error('ngaylaphopdong', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                    </div>

                    <div class="col-5">

                        <div class="form-group">
                            <label for="">Ngày vào ở <span style="color: red">*</span></label>
                            <input type="date" name="ngayvao" id="" class="form-control" value="<?php echo old('ngayvao', $old); ?>">
                            <?php echo form_error('ngayvao', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Ngày hết hạn hợp đồng <span style="color: red">*</span></label>
                            <input type="date" name="ngayra" id="" class="form-control" value="<?php echo old('ngayra', $old); ?>">
                            <?php echo form_error('ngayra', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Tình trạng cọc</label>
                            <select name="tinhtrangcoc" class="form-select">
                                <!-- <option value="">Chọn trạng thái</option>                                -->
                                <option value="0" <?php if($contractDetail['tinhtrangcoc'] == 0) echo 'selected' ?> >Chưa thu tiền</option>
                                <option value="1" <?php if($contractDetail['tinhtrangcoc'] == 1) echo 'selected' ?>>Đã thu tiền</option>
                            </select>
                        </div>
                    </div>                  
                    <div class="from-group">                    
                            <div class="btn-row">
                            <a style="margin-right: 20px " href="<?php echo getLinkAdmin('contract') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
                                <button type="submit" class="btn btn-secondary"><i class="fa fa-edit"></i> Cập nhật</button>
                            </div>
                    </div>
            </form>
        </div>
    </div>


<?php
layout('footer', 'admin');





