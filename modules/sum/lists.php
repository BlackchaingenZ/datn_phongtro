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
                            <p class="total-desc">Tổng số phòng đang cho thuê</p>
                        </div>
                        <?php $totalRoomThue = getRows("SELECT id FROM room WHERE trangthai=1") ?>
                        <?php $ratio1 = ($totalRoomThue / $totalRoom) * 100; ?>
                        <?php $ratio1 = number_format($ratio1, 2) ?>
                        <p class="total-count"><?php echo $totalRoomThue ?> <span style="font-size: 16px">(<?php echo $ratio1 ?>%)</span></p>
                        <!-- <a href=""><div class="dashboard-link"></div></a> -->

                    </div>

                    <div class="child-two">
                        <div class="content-left-title">
                            <div class="content-left-icon">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/tasks.svg" alt="">
                            </div>
                            <p class="total-desc">Tổng số phòng đang trống</p>
                        </div>
                        <?php $totalRoomTrong = getRows("SELECT id FROM room WHERE trangthai=0") ?>
                        <?php $ratio2 = ($totalRoomTrong / $totalRoom) * 100; ?>
                        <?php $ratio2 = number_format($ratio2, 2) ?>
                        <p class="total-count"><?php echo $totalRoomTrong ?> <span style="font-size: 16px">(<?php echo $ratio2 ?>%)</span></p>
                        <!--<a href=""><div class="dashboard-link"></div></a>-->
                    </div>

                </div>


                <div class="content-left-child">
                    <div class="child-three">
                        <div class="content-left-title">
                            <div class="content-left-icon">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/tasks.svg" alt="">
                            </div>
                            <p class="total-desc">Tổng số phòng đang trong hạn hợp đồng</p>
                        </div>
                        <?php $contractTotal = getRows("SELECT id From contract") ?>
                        <?php $contractPass = getRows("SELECT id From contract where trangthaihopdong = 1") ?>
                        <?php // Kiểm tra nếu tổng số hợp đồng không bằng 0
                        if ($contractTotal > 0) {
                            $ratioContract1 = ($contractPass / $contractTotal) * 100;
                        } else {
                            $ratioContract1 = 0; // Gán giá trị 0 hoặc giá trị mặc định
                        } ?>
                        <?php $ratioContract1 = number_format($ratioContract1, 2) ?>
                        <p class="total-count"><?php echo $contractPass ?> <span style="font-size: 16px">(<?php echo $ratioContract1 ?>%)</span></p>
                        <!--<a href=""><div class="dashboard-link"></div></a>-->
                    </div>


                    <div class="child-four">
                        <div class="content-left-title">
                            <div class="content-left-icon">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/tasks.svg" alt="">
                            </div>
                            <p class="total-desc">Tổng số phòng đã hết hạn hợp đồng</p>
                        </div>
                        <?php $contractFail = getRows("SELECT id From contract where trangthaihopdong = 0") ?>
                        <?php // Kiểm tra nếu tổng số hợp đồng không bằng 0
                        if ($contractTotal > 0) {
                            $ratioContract2 = ($contractFail / $contractTotal) * 100;
                        } else {
                            $ratioContract2 = 0; // Gán giá trị 0 hoặc giá trị mặc định
                        } ?>
                        <?php $ratioContract2 = number_format($ratioContract2, 2) ?>
                        <p class="total-count"><?php echo $contractFail ?> <span style="font-size: 16px">(<?php echo $ratioContract2 ?>%)</span></p>
                        <!--<a href=""><div class="dashboard-link"></div></a>-->
                    </div>

                </div>

                <div class="content-left-child">
                    <!-- Child Equipment Section -->
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


                    <div class="child-equipment">
                        <div class="content-left-title">
                            <div class="content-left-icon">
                                <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/tasks.svg" alt="">
                            </div>
                            <p class="total-desc">Danh sách thiết bị theo từng phòng</p>
                        </div>
                        <?php
                        // Truy vấn danh sách phòng và thiết bị
                        $roomsWithEquipments = getRaw("
        SELECT 
            tenphong AS tenphong, 
            equipment.tenthietbi AS equipment_name, 
            COUNT(equipment_room.equipment_id) AS total 
        FROM 
            room
        LEFT JOIN equipment_room ON room.id = equipment_room.room_id
        LEFT JOIN equipment ON equipment.id = equipment_room.equipment_id
        GROUP BY tenphong, equipment.tenthietbi
        ORDER BY tenphong
    ");

                        // Xử lý dữ liệu để nhóm theo phòng
                        $roomDetails = [];
                        foreach ($roomsWithEquipments as $row) {
                            if (!isset($roomDetails[$row['tenphong']])) {
                                $roomDetails[$row['tenphong']] = [];
                            }
                            $roomDetails[$row['tenphong']][] = "{$row['equipment_name']}: {$row['total']} cái";
                        }
                        ?>
                        <!-- Button to trigger the popup -->
                        <button id="showPopupBtn">Xem chi tiết</button>

                        <!-- Popup Modal -->
                        <div id="popupModal" class="popup-modal">
                            <div class="popup-content">
                                <span class="close-btn" id="closePopupBtn">&times;</span>
                                <div class="room-details">
                                    <h3> Danh sách phòng và thiết bị đang có</h3>
                                    <?php foreach ($roomDetails as $roomName => $equipmentList): ?>
                                        <p class="room-name"><b><?php echo $roomName; ?></b>: <?php echo implode(', ', $equipmentList); ?></p>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <script>
                            // Get the modal, button and close button elements
                            var modal = document.getElementById("popupModal");
                            var btn = document.getElementById("showPopupBtn");
                            var closeBtn = document.getElementById("closePopupBtn");

                            // Show the modal when button is clicked
                            btn.onclick = function() {
                                modal.style.display = "block";
                            }

                            // Close the modal when the close button is clicked
                            closeBtn.onclick = function() {
                                modal.style.display = "none";
                            }

                            // Close the modal if the user clicks outside of the modal content
                            window.onclick = function(event) {
                                if (event.target === modal) {
                                    modal.style.display = "none";
                                }
                            }
                        </script>
                    </div>
                </div>

            </div>

            <div class="content-right">
                <div class="child-five">
                    <div class="content-left-title">
                        <div class="content-left-icon background-icon">
                            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/tasks.svg" alt="">
                        </div>
                        <p class="total-desc">Tổng số khách thuê</p>
                    </div>
                    <?php $toTalTenant = getRows("SELECT id FROM tenant"); ?>
                    <p class="total-count"><?php echo $toTalTenant ?></p>
                    <!--<a href=""><div class="dashboard-link"></div></a>-->
                </div>

                <div class="child-six">
                    <div class="content-left-title">
                        <div class="content-left-icon background-icon">
                            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/tasks.svg" alt="">
                        </div>
                        <?php
                        $listAllcontract = getRaw("SELECT *, contract.id, tenphong, tenkhach,  cost.giathue, tiencoc, contract.ngayvao as ngayvaoo, contract.ngayra as thoihanhopdong FROM contract 
                                    INNER JOIN room ON contract.room_id = room.id
                                    INNER JOIN cost_room ON room.id = cost_room.room_id
                                     INNER JOIN cost ON cost_room.cost_id = cost.id
                                    INNER JOIN contract_tenant ON contract.id = contract_tenant.contract_id_1
                                    INNER JOIN tenant ON contract_tenant.tenant_id_1 = tenant.id");


                        // Danh sách các hợp đồng sắp hết hạn
                        $expiringContracts = [];

                        // Thêm các hợp đồng sắp hết hạn vào danh sách
                        $countContract = 0;
                        foreach ($listAllcontract as $contract) {
                            $daysUntilExpiration = getContractStatus($contract['thoihanhopdong']);
                            if ($daysUntilExpiration == "Sắp hết hạn") {
                                $expiringContracts[] = $contract;
                                $countContract++;
                            }
                        }

                        ?>
                        <p class="total-desc">Số phòng sắp hết hạn hợp đồng</p>
                    </div>
                    <?php
                    // Kiểm tra nếu tổng số hợp đồng không bằng 0
                    if ($contractTotal > 0) {
                        $ratio3 = ($countContract / $contractTotal) * 100;
                    } else {
                        $ratio3 = 0; // Gán giá trị 0 hoặc giá trị mặc định khi không có hợp đồng
                    }

                    // Định dạng tỷ lệ
                    $ratio3 = number_format($ratio3, 2);
                    ?>

                    <p class="total-count"><?php echo $countContract; ?><span style="font-size: 16px">(<?php echo $ratio3 ?>%) </span> </p>
                    <!--<a href=""><div class="dashboard-link"></div></a>-->

                </div>

                <div class="child-seven">
                    <div class="content-left-title">
                        <div class="content-left-icon background-icon">
                            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/tasks.svg" alt="">
                        </div>
                        <p class="total-desc">Tổng số người dùng hệ thống</p>
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
