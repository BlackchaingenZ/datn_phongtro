<?php

if(!defined('_INCODE'))
die('Access denied...');


$data = [
    'pageTitle' => 'Cập nhật phòng'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);


// Xử lý hiện dữ liệu cũ của người dùng
$body = getBody();
$id = $_GET['id'];


if(!empty($body['id'])) {
    $roomId = $body['id'];   
    $roomDetail  = firstRaw("SELECT * FROM room WHERE id=$roomId");
    if (!empty($roomDetail)) {
        // Gán giá trị roomDetail vào setFalsh
        setFlashData('roomDetail', $roomDetail);
    
    }else {
        redirect('/admin/?module=room&action=lists');
    }
}


// Xử lý sửa người dùng
if(isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi
    
    //Valide họ tên: Bắt buộc phải nhập, >=5 ký tự
    if(empty(trim($body['tenphong']))) {
        $errors['tenphong']['required'] = '** Bạn chưa nhập tên phòng!';
    }else {
        if(strlen(trim($body['tenphong'])) <= 5) {
        $errors['tenphong']['min'] = '** Tên phòng phải lớn hơn 5 ký tự!';
        }
    }

    if(empty(trim($body['giathue']))) {
        $errors['giathue']['required'] = '** Bạn chưa nhập giá phòng!';
    }

    if(empty(trim($body['tiencoc']))) {
        $errors['tiencoc']['required'] = '** Bạn chưa nhập giá tiền cọc!';
    }

  
   // Kiểm tra mảng error
  if(empty($errors)) {
    // không có lỗi nào
    $dataUpdate = [
        'tenphong' => $body['tenphong'],
        'dientich' => $body['dientich'],
        'giathue' => $body['giathue'],
        'tiencoc' => $body['tiencoc'],
        'ngaylaphd' => $body['ngaylaphd'],
        'chuky' => $body['chuky'],
        'ngayvao' => $body['ngayvao'],
        'ngayra' => $body['ngayra'],
    ];

    $condition = "id=$id";
    $updateStatus = update('room', $dataUpdate, $condition);
    if ($updateStatus) {
        setFlashData('msg', 'Cập nhật thông tin phòng thành công');
        setFlashData('msg_type', 'suc');
        redirect('admin/?module=room');
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

  redirect('/admin/?module=room&action=edit&id='.$roomId);

}
$msg =getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

if (!empty($roomDetail) && empty($old)) {
    $old = $roomDetail;
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
                            <label for="">Tên phòng <span style="color: red">*</span></label>
                            <input type="text" placeholder="Tên phòng" name="tenphong" id="" class="form-control" value="<?php echo old('tenphong', $old); ?>">
                            <?php echo form_error('tenphong', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Diện tích</label>
                            <input type="text" placeholder="Diện tích (m2)" name="dientich" id="" class="form-control" value="<?php echo old('dientich', $old); ?>">
                            <?php echo form_error('dientich', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Giá thuê <span style="color: red">*</span></label>
                            <input type="text" placeholder="Giá thuê (đ)" name="giathue" id="" class="form-control" value="<?php echo old('giathue', $old); ?>">
                            <?php echo form_error('giathue', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Giá tiền cọc <span style="color: red">*</span></label>
                            <input type="text" placeholder="Giá cọc (đ)" name="tiencoc" id="" class="form-control" value="<?php echo old('tiencoc', $old); ?>">
                            <?php echo form_error('tiencoc', $errors, '<span class="error">', '</span>'); ?>
                        </div>
                    </div>

                    <div class="col-5">
                        <div class="form-group">
                            <label for="">Ngày lập hóa đơn</label>
                            <select name="ngaylaphd" id="" class="form-select">
                                <option value="">Chọn ngày</option>
                                <?php
                                    for($i=1; $i <= 31; $i++) { 
                                        $selected = ($i == $roomDetail['ngaylaphd']) ? "selected" : "";
                                    ?>
                                        <option value="<?php echo $i ?>" <?php echo $selected; ?> >Ngày <?php echo $i; ?></option> 
                                    <?php } 
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Chu kỳ thu tiền</label>
                            <select name="chuky" id="" class="form-select">
                                <option value="">Chọn chu kỳ</option>
                                <?php 
                                    for($i = 1; $i < 7; $i+=2) { 
                                        $selectChuky = ($i == $roomDetail['chuky']) ? 'selected' : "";
                                        ?>
                                        <option value="<?php echo $i ?>" <?php echo $selectChuky; ?> ><?php echo $i;?> tháng</option>
                                    <?php }
                                 ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">Ngày vào ở</label>
                            <input type="date" name="ngayvao" id="" class="form-control" value="<?php echo old('ngayvao', $old); ?>">
                            <?php echo form_error('ngayvao', $errors, '<span class="error">', '</span>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="">Thời hạn hợp đồng <span style="color: red">*</span></label>
                            <input type="date" name="ngayra" id="" class="form-control" value="<?php echo old('ngayra', $old); ?>">
                            <?php echo form_error('ngayra', $errors, '<span class="error">', '</span>'); ?>
                        </div>
                    
                    </div>                  
                    <div class="from-group">                    
                            <div class="btn-row">
                                <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-edit"></i> Cập nhật</button>
                                <a style="margin-left: 20px " href="<?php echo getLinkAdmin('room') ?>" class="btn btn-success"><i class="fa fa-forward"></i></a>
                            </div>
                    </div>
                </form>

            </div>
    </div>


<?php
layout('footer', 'admin');





