<?php

if (!defined('_INCODE')) die('Access denied...');

$data = [
    'pageTitle' => 'Thu/Chi - Tổng kết'
];

layout('header', 'admin', $data);
layout('breadcrumb', 'admin', $data);

$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');
$errors = getFlashData('errors');
$old = getFlashData('old');

// // Sau khi thêm hóa đơn thành công
// header("Location: " . getLinkAdmin('bill', 'bills'));
// exit;
?>

<?php
layout('navbar', 'admin', $data);
?>

<div class="container-fluid">
    <div id="MessageFlash">
        <?php getMsg($msg, $msgType); ?>
    </div>

    <div class="box-content sumary-content">
        <div class="sumary-left">
            <a href="<?php echo getLinkAdmin('bill', 'bills') ?>" class="btn btn-secondary"><i class="fa-solid fa-list-check"></i> Hoá đơn</a>
            <a href="<?php echo getLinkAdmin('collect'); ?>" class="btn btn-secondary"><i class="fa-solid fa-list-check"></i> Quản lý danh mục thu</a>
            <a href="<?php echo getLinkAdmin('receipt'); ?>" class="btn btn-secondary"><i class="fa-solid fa-list-check"></i> Quản lý phiếu thu</a>

        </div>
        <div class="container-fluid" style="text-align: center;">
            <img src="<?php echo _WEB_HOST_ADMIN_TEMPLATE; ?>/assets/img/banner.png" class="img-fluid" alt="Banner" style="transform: scale(0.75); display: block; margin: 0 auto;">
        </div>
    </div>

</div>

<?php
layout('footer', 'admin');
?>

<script>
    function toggleInputType() {
        const filterType = document.querySelector('select[name="filter_type"]').value;
        const dateInput = document.querySelector('input[name="date_input"]');
        const submitButton = document.querySelector('button[type="submit"]');

        if (!filterType) {
            dateInput.disabled = true;
            submitButton.disabled = true;
        } else {
            dateInput.disabled = false;
            submitButton.disabled = false;
        }

        if (filterType === 'year') {
            dateInput.type = 'month';
            dateInput.setAttribute('data-filter-type', 'year');
        } else if (filterType === 'quarter') {
            dateInput.type = 'month';
            dateInput.setAttribute('data-filter-type', 'quarter');
        } else {
            dateInput.type = 'month';
            dateInput.setAttribute('data-filter-type', 'month');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleInputType();
    });

    const ctx = document.getElementById('profitChart').getContext('2d');
    const profitChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Lợi nhuận',
                data: <?php echo json_encode($profits); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>