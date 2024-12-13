<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Lịch sử áp dụng giá'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$historyData = getRaw("
    SELECT room.tenphong, cost.tengia, cost_room.thoigianapdung
    FROM cost_room
    JOIN room ON cost_room.room_id = room.id
    JOIN cost ON cost_room.cost_id = cost.id
    ORDER BY cost_room.thoigianapdung DESC;
    
");
$searchTerm = '';
$searchResults = [];
$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');


// Xử lý tìm kiếm
if (isset($_POST['search'])) {
    $searchTerm = $_POST['search_term'];
    $searchTerm = htmlspecialchars($searchTerm); // Bảo mật đầu vào

    // Truy vấn tìm kiếm theo tên giá hoặc tên phòng
    $query = "
        SELECT room.tenphong, cost.tengia, cost_room.thoigianapdung
        FROM cost_room
        JOIN room ON cost_room.room_id = room.id
        JOIN cost ON cost_room.cost_id = cost.id
        WHERE cost.tengia LIKE '%$searchTerm%' OR room.tenphong LIKE '%$searchTerm%'
        ORDER BY cost_room.thoigianapdung DESC;
    ";
    $searchResults = getRaw($query); // Lấy kết quả tìm kiếm
} else {
    // Nếu không tìm kiếm, lấy toàn bộ dữ liệu
    $query = "
        SELECT room.tenphong, cost.tengia, cost_room.thoigianapdung
        FROM cost_room
        JOIN room ON cost_room.room_id = room.id
        JOIN cost ON cost_room.cost_id = cost.id
        ORDER BY cost_room.thoigianapdung DESC;
    ";
    $searchResults = getRaw($query);
}


?>
<?php layout('navbar', 'admin', $data); ?>


<div class="container-fluid">

    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

    <!-- Hiển thị danh sách bảng giá -->
    <div class="container-fluid">
    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

    

    <!-- Hiển thị danh sách bảng giá -->
    <div class="box-content">
        <form method="POST" action="">

        <!-- Thanh tìm kiếm -->
            <div class="row mb-3">
                <div class="col-4"></div>
                <div class="col-4">
                    <input style="height: 50px" type="search" name="search_term" class="form-control" placeholder="Nhập tên khuyến mãi cần tìm" value="<?php echo htmlspecialchars($searchTerm); ?>">
                </div>
                <div class="col">
                    <button style="height: 50px; width: 50px" type="submit" name="search" class="btn btn-secondary">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>

            <!-- <input type="hidden" name="module" value="cost"> -->
            <a style="margin-right: 5px," href="<?php echo getLinkAdmin('cost', 'applyroom') ?>" class="btn btn-secondary"><i class="fa fa-arrow-circle-left"></i> Quay lại</a>
            <a href="<?php echo getLinkAdmin('cost', 'historycost'); ?>" class="btn btn-secondary"><i class="fa fa-history"></i> Refresh</a>
            
            
            <table class="table table-bordered mt-3 hiscost" >
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Phòng</th>
                        <th>Giá</th>
                        <th>Thời gian áp dụng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($searchResults)) { ?>
                        <?php foreach ($searchResults as $index => $item) { ?>
                        <tr>
                            <td style="text-align: center; width: 100px;"><?php echo $index + 1; ?></td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($item['tenphong']); ?></td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($item['tengia']); ?></td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($item['thoigianapdung']); ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                <!-- Thông báo không có dữ liệu -->
                    <tr>
                        <td colspan="4" class="text-center">
                            <div class="alert alert-warning mb-0" role="alert">
                                <strong>Thông báo:</strong> Chưa có lịch sử áp dụng giá.
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>

            </table>
           
        </form>
    </div>

</div>


<?php layout('footer', 'admin'); ?>