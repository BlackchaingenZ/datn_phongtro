<?php

if(!defined('_INCODE'))
die('Access denied...');


$data = [
    'pageTitle' => 'Cập nhật phiếu chi'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);


// Xử lý hiện dữ liệu cũ của người dùng
$body = getBody();
$id = $_GET['id'];

$allSpend = getRaw("SELECT * FROM category_spend");
$allRoom = getRaw("SELECT * FROM room");


if(!empty($body['id'])) {
    $paymentId = $body['id'];   
    $paymentDetail  = firstRaw("SELECT * FROM payment WHERE id=$paymentId");
    if (!empty($paymentDetail)) {
        // Gán giá trị paymentDetail vào setFalsh
        setFlashData('paymentDetail', $paymentDetail);
    
    }else {
        redirect('/admin/?module=payment');
    }
}


// Xử lý sửa người dùng
if(isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi
    
    //Valide họ tên: Bắt buộc phải nhập, >=5 ký tự

   // Kiểm tra mảng error
  if(empty($errors)) {
    // không có lỗi nào
    $dataUpdate = [
        'danhmucchi_id' => $body['danhmucchi_id'],
        'room_id' => $body['room_id'],
        'sotien' => $body['sotien'],
        'ghichu' => $body['ghichu'],
        'ngaychi' => $body['ngaychi'],
        'phuongthuc' => $body['phuongthuc'],
    ];

    $condition = "id=$id";
    $updateStatus = update('payment', $dataUpdate, $condition);
    if ($updateStatus) {
        setFlashData('msg', 'Cập nhật thông tin phiếu chi thành công');
        setFlashData('msg_type', 'suc');
        redirect('admin/?module=payment');
    }else {
    setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
    setFlashData('msg_type', 'err');
}

  }else {
    // Có lỗi xảy ra
    setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
    setFlashData('msg_type', 'err');
    setFlashData('errors', $errors);
    setFlashData('old', $body);  // giữ lại các trường dữ liệu hợp lê khi nhập vào
  }

  redirect('/admin/?module=payment&action=edit&id='.$paymentId);

}
$msg =getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

if (!empty($paymentDetail) && empty($old)) {
    $old = $paymentDetail;
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
                            <label for="">Danh mục chi <span style="color: red">*</span></label>
                            <select name="danhmucchi_id" id="" class="form-select">
                                <option value="">Chọn danh mục</option>
                                <?php
                                    if(!empty($allSpend)) {
                                        foreach($allSpend as $item) {                                            
                                                ?>
                                                    <option value="<?php echo $item['id'] ?>" <?php echo (old('danhmucchi_id', $old) == $item['id'])?'selected':'' ?>><?php echo $item['tendanhmuc'] ?></option> 
                                                <?php                                           
                                        }
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Chọn phòng lập phiếu chi <span style="color: red">*</span></label>
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
                            <?php echo form_error('room_id', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Nội dung thanh toán <span style="color: red">*</span></label>
                            <textarea  rows="5" name="ghichu" id="" class="form-control" value=""><?php echo old('ghichu', $old); ?></textarea>
                            <?php echo form_error('ghichu', $errors, '<span class="error">', '</span>'); ?>
                        </div>
                    </div>

                    <div class="col-5">

                        <div class="form-group">
                            <label for="">Số tiền <span style="color: red">*</span></label>
                            <input type="text" placeholder="Nhập số tiền thu" name="sotien" id="" class="form-control" value="<?php echo old('sotien', $old); ?>">
                            <?php echo form_error('sotien', $errors, '<span class="error">', '</span>'); ?>
                        </div>
        
                        <div class="form-group">
                            <label for="">Ngày thu <span style="color: red">*</span></label>
                            <input type="date" name="ngaychi" id="" class="form-control" value="<?php echo old('ngaychi', $old); ?>">
                            <?php echo form_error('ngaychi', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Phương thức thanh toán</label>
                            <select name="phuongthuc" class="form-select">
                                <option value="">Chọn phương thức</option>
                                <option value="0" <?php if($paymentDetail['phuongthuc'] == 0) echo 'selected' ?> >Tiền mặt</option>
                                <option value="1" <?php if($paymentDetail['phuongthuc'] == 1) echo 'selected' ?>>Chuyển khoản</option>
                            </select>
                        </div>
                    
                    </div>                  
                    <div class="from-group">                    
                            <div class="btn-row">
                                <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Cập nhật phiếu thu</button>
                                <a style="margin-left: 20px " href="<?php echo getLinkAdmin('payment') ?>" class="btn btn-success"><i class="fa fa-forward"></i></a>
                            </div>
                    </div>
                </form>

            </div>
    </div>


<?php
layout('footer', 'admin');





