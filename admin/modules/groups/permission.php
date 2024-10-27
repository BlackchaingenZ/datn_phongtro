<?php

// Lấy dữ liệu cũ của người dùng
$body = getBody();

if(!empty($body['id'])) {

    $groupId = $body['id'];

    $groupDetail  = firstRaw("SELECT * FROM groups WHERE id=$groupId");

    
    if (!empty($groupDetail)) {
        setFlashData('groupDetail', $groupDetail);
    }else {
        redirect('?module=groups&action=lists');
    }
}

$data = [
    'pageTitle' => 'Phân quyền: '.$groupDetail['name']
];

layout('header', 'admin', $data);
layout('sidebar', 'admin', $data);
layout('breadcrumb', 'admin', $data);



// Xử lý sửa người dùng
if(isPost()) {
    // Validate form
    $body = getBody(); // lấy tất cả dữ liệu trong form
    $error = [];

    if (empty($error)) {
        $permissionJson = json_encode($body['permission']);

        $dataUpdate = [
            'permission' => $permissionJson,
            'update_at' => date('Y-m-d H:i:s')
        ];
        $condition = "id=$groupId";

        $updateStatus = update('groups', $dataUpdate, $condition);

        if($updateStatus) {
            setFlashData('msg', 'Phân quyền thành công');
            setFlashData('msg_type', 'success');
            redirect('admin/?module=groups&action=permission&id='.$groupId);
        }else {
            setFlashData('msg', 'Hệ thống đang gặp sự cố');
            setFlashData('msg_type', 'danger');
        }
    }
}
$msg =getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');
$groupDetail = getFlashData('groupDetail');

if (!empty($groupDetail) && empty($old)) {
    $old = $groupDetail;
}

// Lấy danh sách module
$moduleList = getRaw("SELECT * FROM modules");

if(!empty($old['permission'])) {
    $permissionJson = $old['permission'];

    $permissionArr = json_decode($permissionJson, true);
}

?>

   <!-- Main content -->
   <section class="content">
     <div class="container-fluid">
           <form action="" method="post">
           <hr/>
               <?php echo getMsg($msg, $msgType);  ?>
                
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width=30%>Module</th>
                            <th>Chức năng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if(!empty($moduleList)):
                                foreach($moduleList as $item):
                                    $actions = $item['actions'];
                                    $actionsArr = json_decode($actions); // json_decode: chuyển json => Array
                        ?>
                        <tr>
                            <td><?php echo $item['title'] ?></td>
                            <td>
                                <div class="row">
                                    <?php foreach($actionsArr as $roleKey => $roleTitle): ?>
                                    <div class="col">
                                        <input type="checkbox" name="<?php echo 'permission['.$item['name'].'][]' ?>" value="<?php echo $roleKey ?>" <?php echo (!empty($permissionArr[$item['name']]) && in_array($roleKey, $permissionArr[$item['name']])) ? 'checked':false ?>>
                                        <label for=""><?php echo $roleTitle ?></label>
                                    </div>
                                    <?php  endforeach; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>

               <div class="btn-row">
                   <button type="submit" class="btn btn-primary">Phân quyền</button>
                   <a style="margin-left: 20px " href="<?php  echo getLinkAdmin('groups', 'lists') ?>" class="btn btn-success">Quay lại trang danh sách</a>
                   <input type="hidden" name="id" value="<?php echo $groupId; ?>">
               </div>
         
           </form>
     </div><!-- /.container-fluid -->
   </section>
   <!-- /.content -->

<?php
layout('footer', 'admin');