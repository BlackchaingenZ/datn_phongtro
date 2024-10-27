<?php
$data = [
    'pageTitle' => 'Cập nhật nhóm người dùng'
];

layout('header', 'admin', $data);
layout('sidebar', 'admin', $data);
layout('breadcrumb', 'admin', $data);

// Lấy dữ liệu cũ của người dùng
$body = getBody();

if(!empty($body['id'])) {

    $groupId = $body['id'];

    $groupDetail  = firstRaw("SELECT * FROM groups WHERE id=$groupId");

    
    if (!empty($groupDetail)) {
        setFlashData('groupDetail', $groupDetail);
    }else {
        redirect('admin/?module=groups&action=lists');
    }
}

// Xử lý sửa người dùng
if(isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $errors = [];  // mảng lưu trữ các lỗi
    
    // Valide họ tên: Bắt buộc phải nhập, >=5 ký tự
    if(empty(trim($body['name']))) {
        $errors['name']['required'] = '** Bạn chưa nhập họ tên!';
    }else {
        if(strlen(trim($body['name'])) <= 5) {
        $errors['name']['min'] = '** Họ tên phải lớn hơn 5 ký tự!';
        }
    }

   
   // Kiểm tra mảng error
  if(empty($errors)) {
    // không có lỗi nào
    $dataUpdate = [
        'name' => $body['name'],
        'update_at' => date('Y-m-d H:i:s'),
    ];

    /////////////////////////////////////
    $condition = "id=$groupId";

    $updateStatus = update('groups', $dataUpdate, $condition);
    if ($updateStatus) {
        setFlashData('msg', 'Cập nhật nhóm người dùng thành công');
        setFlashData('msg_type', 'success');
  
    }else {
        setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
        setFlashData('msg_type', 'danger');
}

  }else {
    // Có lỗi xảy ra
    setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
    setFlashData('msg_type', 'danger');
    setFlashData('errors', $errors);
    setFlashData('old', $body);  // giữ lại các trường dữ liệu hợp lê khi nhập vào
  }

  redirect('?module=groups&action=edit&id='.$groupId);

}
$msg =getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');
$groupDetail = getFlashData('groupDetail');

if (!empty($groupDetail) && empty($old)) {
    $old = $groupDetail;
}


?>

   <!-- Main content -->
   <section class="content">
     <div class="container-fluid">
           <form action="" method="post">
           <hr/>
               <?php echo getMsg($msg, $msgType);  ?>
                   <div class="col-6">
                        <div class="form-group">
                            <label for="name">Tên nhóm</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo old('name', $old); ?>">
                            <?php echo form_error('name', $errors, '<span class="error">', '</span>'); ?>
                        </div>
                   </div>
             
          
               <div class="btn-row">
                   <button type="submit" class="btn btn-primary">Cập nhật</button>
                   <a style="margin-left: 20px " href="<?php  echo getLinkAdmin('groups', 'lists') ?>" class="btn btn-success"><i class="fa fa-forward"></i></a>
                   <input type="hidden" name="id" value="<?php echo $groupId; ?>">
               </div>
         
           </form>
     </div><!-- /.container-fluid -->
   </section>
   <!-- /.content -->

<?php
layout('footer', 'admin');