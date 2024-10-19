<?php
require __DIR__ . '/vendor/autoload.php'; // Đường dẫn tới thư viện Twilio

use Twilio\Rest\Client;

// Thông tin tài khoản Twilio
$account_sid = 'YOUR_TWILIO_ACCOUNT_SID';
$auth_token = 'YOUR_TWILIO_AUTH_TOKEN';
$twilio_number = 'YOUR_TWILIO_PHONE_NUMBER';

// Khởi tạo đối tượng Twilio
$client = new Client($account_sid, $auth_token);

// Lấy danh sách khách hàng cần thông báo
// Cần thay thế hàm này bằng hàm lấy danh sách khách hàng của bạn

// Ví dụ:
$customers = [
    ['name' => 'Customer 1', 'phone' => '+1234567890'],
    ['name' => 'Customer 2', 'phone' => '+0987654321']
];

// Gửi tin nhắn cho từng khách hàng
foreach ($customers as $customer) {
    $sms_body = 'Xin chào ' . $customer['name'] . ', hóa đơn của bạn đã đến hạn thanh toán. Vui lòng thanh toán trước ngày 30 của tháng. Trân trọng, Nhà trọ XYZ';

    // Gửi tin nhắn SMS
    $message = $client->messages->create(
        $customer['phone'], // Số điện thoại của khách hàng
        [
            'from' => $twilio_number,
            'body' => $sms_body
        ]
    );

    // Log kết quả gửi tin nhắn
    if ($message->sid) {
        echo 'Đã gửi tin nhắn đến ' . $customer['phone'] . ' thành công.';
    } else {
        echo 'Gửi tin nhắn đến ' . $customer['phone'] . ' thất bại.';
    }
}
?>
