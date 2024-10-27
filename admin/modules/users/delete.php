<?php

$body = getBody();

if(!empty($body['id'])) {
    $userId = $body['id'];
    // Kiểm tra Id có tồn tại trong hệ thống hay không
    $userDetail = getRows("SELECT id FROM users WHERE id=$userId");
    if($userDetail > 0) {
        // Thực hiện xóa
        // 1. Xóa logintoken(vì liên kết khóa ngoại)
        $deleteToken = delete('login_token', "user_id=$userId");
        if($deleteToken) {
            //2. Xóa user
            $deleteUser = delete('users', "id=$userId");
            if($deleteUser) {
                setFlashData('msg', 'Xóa người dùng thành công');
                setFlashData('msg_type', 'suc');
            }else {
                setFlashData('msg', 'Lỗi hệ thống! Vui lòng thử lại sau');
                setFlashData('msg_type', 'err');
            }
        } else {
                setFlashData('msg', 'Lỗi hệ thống! Vui lòng thử lại sau');
                setFlashData('msg_type', 'err');
        }
    }else {
        setFlashData('msg', 'Người dùng không tồn tại trên hệ thống');
        setFlashData('msg_type', 'err');
    }
}else {
    setFlashData('msg', 'Liên kết không tồn tại');
    setFlashData('msg_type', 'err');
}

redirect('admin/?module=users');