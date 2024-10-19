<?php
$data = [
    'pageTitle' => 'Thêm nhóm'
];

layout('header', 'admin', $data);
layout('sidebar', 'admin', $data);
layout('breadcrumb', 'admin', $data);

if(isPost()) {
     // Validate form
     $body = getBody(); // lấy tất cả dữ liệu trong form
     $errors = [];  // mảng lưu trữ các lỗi

 

    // Kiểm tra mảng error
  if(empty($errors)) {

    $dataInsert = [
        'name' => $body['name'],
        'create_at' => date('Y-m-d H:i:s', ),
    ];
    $insertStatus = insert('groups', $dataInsert);
    if ($insertStatus) {
        setFlashData('msg', 'Thêm nhóm người dùng thành công');
        setFlashData('msg_type', 'success');
        redirect('?module=groups&action=lists');
    }else {
        setFlashData('msg', 'Hệ thống đang gặp sự cố, vui lòng thử lại sau');
        setFlashData('msg_type', 'danger');
        redirect('?module=groups&action=add');
    }

  }else {  
    // Có lỗi xảy ra
    setFlashData('msg', 'Vui lòng kiểm tra chính xác thông tin nhập vào');
    setFlashData('msg_type', 'danger');
    setFlashData('errors', $errors);
    setFlashData('old', $body);  // giữ lại các trường dữ liệu hợp lê khi nhập vào
    redirect('?module=groups&action=add'); // Load lại trang 
  }
}

$msg =getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');
   
?>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
            <form action="" method="post">
            <hr/>
                
                    <div class="col-6">
                        <div class="form-group">
                            <label for="name">Tên nhóm</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo old('name', $old); ?>">
                        </div>
                    </div>
              

                <div class="btn-row">
                    <button type="submit" class="btn btn-primary">Thêm mới</button>
                    <a style="margin-left: 20px " href="<?php  echo getLinkAdmin('groups', 'lists') ?>" class="btn btn-success"><i class="fa fa-forward"></i></a>
                </div>
          
            </form>
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->

<?php
layout('footer', 'admin');