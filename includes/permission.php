<?php


// Kiểm tra permission tương ứng module, action
function checkPermission($permissionData, $module, $role='lists') {
    if(!empty($permissionData[$module])) {
        $roleArr = $permissionData[$module];

        if(!empty($roleArr) && in_array($role, $roleArr)) {
            return true;
        }
    }
    return false;
}

//Lấy groupId hiện tại của user đang đăng nhập
function getGroupId() {
    $userId = isLogin()['user_id'];
    $groupRow = firstRaw("SELECT group_id FROM users WHERE id=$userId");

    if(!empty($groupRow)) {
        $groupId = $groupRow['group_id'];

        return $groupId;
    }
    return false;
}


// lấy mảng permission trong bảng group
function getPermissionData($groupId) {
    $groupRow = firstRaw("SELECT permission FROM groups WHERE id=$groupId");

    if(!empty($groupRow)) {
        $permissionData = json_decode($groupRow['permission'], true);
        
        return $permissionData;
    }
    return false;
}