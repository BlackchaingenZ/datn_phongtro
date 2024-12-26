if (empty(trim($body['password']))) {
    $errors['password']['required'] = '** Bạn chưa nhập mật khẩu';
} elseif (!is_numeric($body['password'])) {
    $errors['password']['numeric'] = '** Mật khẩu chỉ được chứa số';
}



if (empty(trim($body['password']))) {
    $errors['password']['required'] = '** Bạn chưa nhập mật khẩu';
} elseif (!ctype_alpha($body['password'])) {
    $errors['password']['alpha'] = '** Mật khẩu chỉ được chứa các chữ cái (a-z, A-Z)';
}


if (empty(trim($body['tenphong']))) {
        $errors['tenphong']['required'] = '** Bạn chưa nhập tên phòng!';
} else {
        if (strlen(trim($body['tenphong'])) >10) {
            $errors['tenphong']['max'] = '** Tên phòng đối đa 10 ký tự';
    }
}
///
<div class="form-group">
                <label for="">Mật khẩu</label><br />            
                <input 
        type="password" 
        name="password" 
        class="" 
        placeholder="Mật khẩu" 
        maxlength="6" 
        oninput="validatePasswordLength(this)"
    >
    <small id="passwordHelp" style="color: red; display: none;">Mật khẩu phải đủ 6 ký tự!</small>
            </div>


//
    $ghichu = $_POST['ghichu'] ?? null;

    // Kiểm tra nếu ghi chú vượt quá 20 ký tự
    if (!empty($ghichu) && strlen($ghichu) > 20) {
        setFlashData('msg', 'Ghi chú không được vượt quá 20 ký tự.');
        setFlashData('msg_type', 'err');
        redirect('?module=contract&action=add'); // Quay lại trang thêm hợp đồng
        exit; // Ngừng xử lý
    }

    // Nếu ghi chú trống, gán giá trị mặc định
    if (empty(trim($ghichu))) {
        $ghichu = 'Bỏ trống';
    }

//
    <td style="text-align: center;background-color:blue"><b><?php echo $item['tenphong']; ?></b></td>
//
    <table class="table table-dark table-striped-columns">

//
                    <div class="form-group">
                    <label for="">Giá thiết bị <span style="color: red">*</span></label>
                    <input type="text" name="giathietbi" class="form-control" value="<?php echo old('giathietbi', $old); ?>"oninput="validateNumber(this)">
                    <?php echo form_error('giathietbi', $errors, '<span class="error">', '</span>'); ?>
                </div>
                <script>

                    function validateNumber(input) {
                        input.value = input.value.replace(/[^0-9\.]/g, ''); 
                         input.value = input.value.replace(/[^a-zA-Z\s]/g, '');
                    }
                </script>

                echo '<span style = "color: red">'. "Trống".'</span>';


if (empty(trim($body['dientich']))) {
    $errors['dientich']['required'] = '** Bạn chưa nhập diện tích phòng!';
} elseif (!is_numeric($body['dientich']) || $body['dientich'] <= 0) {
    $errors['dientich']['invalid'] = '** Diện tích phải là một số dương!';
} elseif ($body['dientich'] >= 100) {
    $errors['dientich']['max'] = '** Diện tích phải nhỏ hơn 100!';
}

GROUP_CONCAT(
    CASE 
        WHEN equipment_room.soluongcap > 0 THEN equipment_room.soluongcap 
        ELSE NULL 
    END
    SEPARATOR ', '
) AS soluong
<div class="form-group">
    <label for="name">Ảnh <span style="color: red">*</span></label>
    <div class="row">
        <div class="col-10">
            <input type="text" placeholder="Ảnh phòng" name="image" id="name" class="form-control" value="<?php echo old('image', $old); ?>">
        </div>
        <div class="col-2">
            <button type="button" class="btn btn-secondary btn-sm choose-image">
                <i class="fa fa-upload"></i> Chọn ảnh
            </button>
        </div>
    </div>
    <div class="mt-2">
        <img id="preview-image" src="<?php echo old('image', $old); ?>" alt="Ảnh phòng" style="max-width: 200px; max-height: 200px; display: <?php echo $old ? 'block' : 'none'; ?>">
    </div>
</div>

<script src="path/to/ckfinder/ckfinder.js"></script>
<script>
    document.querySelector('.choose-image').addEventListener('click', function() {
        CKFinder.modal({
            chooseFiles: true,
            onInit: function(finder) {
                finder.on('files:choose', function(evt) {
                    var file = evt.data.files.first();
                    var imageUrl = file.getUrl();
                    document.getElementById('name').value = imageUrl;
                    document.getElementById('preview-image').src = imageUrl;
                    document.getElementById('preview-image').style.display = 'block';
                });
            }
        });
    });
</script>

function getThoihanbaohanhStatus($endDate, $dem)
{
    $currentDate = new DateTime(); //
    $EquipmentEndDate = new DateTime($endDate);
    $interval = $currentDate->diff($EquipmentEndDate); // Tính khoảng cách giữa 2 ngày
    $daysLeft = (int)$interval->format('%R%a'); // chuyển khoảng cách ngày thành số ngày

    if ($daysLeft < 0) {
        return "Đã hết hạn bảo hành";
    } elseif ($daysLeft > 0 && $daysLeft <= $dem) {
        return "Sắp hết hạn bảo hành";
    } else {
        return "Trong thời hạn bảo hành";
    }
}
<td>
    <?php
    // Lấy giá trị 'dem' từ cơ sở dữ liệu
    $stmt = $pdo->prepare("SELECT dem FROM equipment WHERE id = ?");
    $stmt->execute([$item['id']]); // $item['id'] là ID thiết bị
    $dem = $stmt->fetchColumn();

    // Gọi hàm để lấy trạng thái bảo hành
    $getEquipmentStatus = getThoihanbaohanhStatus($item['thoihanbaohanh'], $dem);

    // Hiển thị trạng thái với class phù hợp
    if ($getEquipmentStatus == "Đã hết hạn bảo hành") {
        echo '<span class="btn-dahethan-err">' . $getEquipmentStatus . '</span>';
    } elseif ($getEquipmentStatus == "Sắp hết hạn bảo hành") {
        echo '<span class="btn-saphethan-err">' . $getEquipmentStatus . '</span>';
    } elseif ($getEquipmentStatus == "Trong thời hạn bảo hành") {
        echo '<span class="btn-trongthoihan-err">' . $getEquipmentStatus . '</span>';
    }
    ?>
</td>
