<?php
if (!isLogin()) {
    redirect('?module=auth&action=login');
}

$data = [
    'pageTitle' => 'Báo cáo tổng hợp'
];

$userId = isLogin()['user_id'];
$userDetail = getUserInfo($userId);
$roomId = $userDetail['room_id'];


if ($userDetail['group_id'] == 7) {
    layout('header', 'admin', $data);
    layout('breadcrumb', 'admin', $data);
} else {
    layout('header-tenant', 'admin', $data);
    layout('sidebar', 'admin', $data);
}
?>

<?php
if ($userDetail['group_id'] == 7) {
    layout('navbar', 'admin', $data);
}
?>
<?php
if ($userDetail['group_id'] == 7) {
?>
    <div class="container-fluid">
        <div class="box-content dashboard-content">
            <div class="content-left">

                <div class="total-room">
                    <div class="content-left-title">
                        <div class="content-left-icon">
                            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/home1.svg" alt="">
                        </div>
                        <p class="total-desc">Tổng số phòng</p>
                    </div>
                    <?php $totalRoom = getRows("SELECT id FROM room") ?>
                    <p class="total-count"><?php echo $totalRoom ?></p>
                </div>

                <div class="content-left-child">
                    <div class="child-one">
                        <div class="content-left-title">
                            <div class="content-left-icon">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/tasks.svg" alt="">
                            </div>
                            <p class="total-desc">Danh sách phòng đang cho thuê</p>
                        </div>
                        <?php
                        $rooms = getRaw("SELECT tenphong FROM room WHERE soluong > 0 ORDER BY tenphong");

                        // Xử lý dữ liệu để nhóm theo phòng (ở đây không cần nhóm theo thiết bị nữa)
                        $roomNames = [];
                        foreach ($rooms as $row) {
                            $roomNames[] = $row['tenphong'];
                        }
                        ?>
                        <!-- Button to trigger the popup -->
                        <button id="showPopupBtnOne">Xem chi tiết</button>

                        <!-- Popup Modal -->
                        <div id="popupModalOne" class="popup-modal">
                            <div class="popup-content">
                                <span class="close-btn" id="closePopupBtnOne">&times;</span>
                                <div class="room-details">
                                    <h3>Danh sách phòng</h3>
                                    <?php foreach ($roomNames as $roomName): ?>
                                        <p class="room-name"><b><?php echo $roomName; ?></b></p>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <script>
                            // Get the modal, button and close button elements for the first popup
                            var modalOne = document.getElementById("popupModalOne");
                            var btnOne = document.getElementById("showPopupBtnOne");
                            var closeBtnOne = document.getElementById("closePopupBtnOne");

                            // Show the modal when button is clicked
                            btnOne.onclick = function() {
                                modalOne.style.display = "block";
                            }

                            // Close the modal when the close button is clicked
                            closeBtnOne.onclick = function() {
                                modalOne.style.display = "none";
                            }

                            // Close the modal if the user clicks outside of the modal content
                            window.onclick = function(event) {
                                if (event.target === modalOne) {
                                    modalOne.style.display = "none";
                                }
                            }
                        </script>
                    </div>

                    <div class="child-two">
                        <div class="content-left-title">
                            <div class="content-left-icon">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/tasks.svg" alt="">
                            </div>
                            <p class="total-desc">Danh sách phòng đang trống</p>
                        </div>
                        <?php
                        // Truy vấn danh sách phòng mà không cần thiết bị
                        $rooms = getRaw("SELECT tenphong FROM room WHERE soluong = 0 ORDER BY tenphong");

                        // Xử lý dữ liệu để nhóm theo phòng (ở đây không cần nhóm theo thiết bị nữa)
                        $roomNames = [];
                        foreach ($rooms as $row) {
                            $roomNames[] = $row['tenphong'];
                        }
                        ?>
                        <!-- Button to trigger the popup -->
                        <button id="showPopupBtnTwo">Xem chi tiết</button>

                        <!-- Popup Modal -->
                        <div id="popupModalTwo" class="popup-modal">
                            <div class="popup-content">
                                <span class="close-btn" id="closePopupBtnTwo">&times;</span>
                                <div class="room-details">
                                    <h3>Danh sách phòng</h3>
                                    <?php foreach ($roomNames as $roomName): ?>
                                        <p class="room-name"><b><?php echo $roomName; ?></b></p>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <script>
                            // Get the modal, button and close button elements for the second popup
                            var modalTwo = document.getElementById("popupModalTwo");
                            var btnTwo = document.getElementById("showPopupBtnTwo");
                            var closeBtnTwo = document.getElementById("closePopupBtnTwo");

                            // Show the modal when button is clicked
                            btnTwo.onclick = function() {
                                modalTwo.style.display = "block";
                            }

                            // Close the modal when the close button is clicked
                            closeBtnTwo.onclick = function() {
                                modalTwo.style.display = "none";
                            }

                            // Close the modal if the user clicks outside of the modal content
                            window.onclick = function(event) {
                                if (event.target === modalTwo) {
                                    modalTwo.style.display = "none";
                                }
                            }
                        </script>
                    </div>
                </div>


                <div class="content-left-child">
                    <div class="child-three">
                        <div class="content-left-title">
                            <div class="content-left-icon">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/tasks.svg" alt="">
                            </div>
                            <p class="total-desc">Số phòng đang trong hạn hợp đồng</p>
                        </div>
                        <?php $contractTotal = getRows("SELECT id From contract") ?>
                        <?php $contractPass = getRows("SELECT id From contract where trangthaihopdong = 1") ?>
                        <?php // Kiểm tra nếu tổng số hợp đồng không bằng 0
                        ?>
                        <p class="total-count"><?php echo $contractPass ?> <span style="font-size: 16px"></span></p>
                        <!--<a href=""><div class="dashboard-link"></div></a>-->
                    </div>

                    <div class="child-equipment">
                        <div class="content-left-title">
                            <div class="content-left-icon">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/tasks.svg" alt="">
                            </div>
                            <p class="total-desc">Danh sách thiết bị</p>
                        </div>

                        <?php
                        // Truy vấn tổng số loại thiết bị và số lượng của từng loại
                        $equipmentTypes = getRaw("SELECT tenthietbi, SUM(soluongnhap) as total FROM equipment GROUP BY tenthietbi ORDER BY tenthietbi ASC");

                        // Đếm tổng số loại thiết bị
                        $totalTypes = count($equipmentTypes);

                        // Tạo danh sách chi tiết từng loại thiết bị
                        $details = [];
                        foreach ($equipmentTypes as $equipment) {
                            $details[] = $equipment['tenthietbi'] . ' (' . $equipment['total'] . ')';
                        }

                        // Truy vấn số lượng tồn kho từng loại thiết bị
                        $equipmentTypes1 = getRaw("SELECT tenthietbi, SUM(soluongtonkho) as total1 FROM equipment GROUP BY tenthietbi ORDER BY tenthietbi ASC");

                        // Đếm tổng số loại thiết bị tồn kho
                        $totalTypes1 = count($equipmentTypes1);

                        // Tạo danh sách chi tiết từng loại thiết bị tồn kho
                        $details1 = [];
                        foreach ($equipmentTypes1 as $equipment) {
                            $details1[] = $equipment['tenthietbi'] . ' (' . $equipment['total1'] . ')';
                        }
                        ?>
                        <!-- Button to trigger the popup -->
                        <button id="showEquipmentPopupBtn">Xem chi tiết</button>
                    </div>

                    <!-- Popup Modal for Equipment Details -->
                    <div id="equipmentPopupModal" class="popup-modal">
                        <div class="popup-content">
                            <span class="close-btn" id="closeEquipmentPopupBtn">&times;</span>
                            <div class="equipment-details">

                                <p class="total-count">Tổng <?php echo $totalTypes; ?> loại + Số lượng nhập </p>
                                <ul>
                                    <?php foreach ($equipmentTypes as $equipment): ?>
                                        <li><b><?php echo $equipment['tenthietbi']; ?></b>: <?php echo $equipment['total']; ?> cái</li>
                                    <?php endforeach; ?>
                                </ul>

                                <p class="total-count">Tổng <?php echo $totalTypes1; ?> loại + số lượng tồn</p>
                                <ul>
                                    <?php foreach ($equipmentTypes1 as $equipment): ?>
                                        <li><b><?php echo $equipment['tenthietbi']; ?></b>: <?php echo $equipment['total1']; ?> cái</li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Popup Modal JavaScript -->
                    <script>
                        // Get elements
                        var popupModal = document.getElementById('equipmentPopupModal');
                        var showPopupBtn = document.getElementById('showEquipmentPopupBtn');
                        var closePopupBtn = document.getElementById('closeEquipmentPopupBtn');

                        // Show popup when button is clicked
                        showPopupBtn.onclick = function() {
                            popupModal.style.display = "block";
                        }

                        // Close popup when close button is clicked
                        closePopupBtn.onclick = function() {
                            popupModal.style.display = "none";
                        }

                        // Close popup when clicking outside the popup content
                        window.onclick = function(event) {
                            if (event.target === popupModal) {
                                popupModal.style.display = "none";
                            }
                        }
                    </script>
                </div>

            </div>

            <div class="content-right">
                <div class="child-five">
                    <div class="content-left-title">
                        <div class="content-left-icon background-icon">
                            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/tasks.svg" alt="">
                        </div>
                        <p class="total-desc">Số khách thuê</p>
                    </div>
                    <?php $toTalTenant = getRows("SELECT id FROM tenant"); ?>
                    <p class="total-count"><?php echo $toTalTenant ?></p>
                    <!--<a href=""><div class="dashboard-link"></div></a>-->
                </div>
                <div class="child-seven">
                    <div class="content-left-title">
                        <div class="content-left-icon background-icon">
                            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/tasks.svg" alt="">
                        </div>
                        <p class="total-desc">Số người dùng hệ thống</p>
                    </div>
                    <?php $allUsers = getRows("SELECT id FROM users") ?>
                    <p class="total-count"><?php echo $allUsers ?></p>
                </div>
            </div>
        </div>
    </div>
<?php
}

layout('footer', 'admin');
