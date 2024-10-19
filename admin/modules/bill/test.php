<?php

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Hóa Đơn Mới</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .form-group input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-group input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h2>Thêm Hóa Đơn Mới</h2>
    <form action="process_invoice.php" method="post">
        <div class="form-group">
            <label for="mahoadon">Mã Hóa Đơn:</label>
            <input type="text" id="mahoadon" name="mahoadon" required>
        </div>
        <div class="form-group">
            <label for="room_id">Phòng:</label>
            <select id="room_id" name="room_id" required onchange="updateTienPhong()">
                <option value="">Chọn phòng</option>
                <?php foreach ($rooms as $room): ?>
                    <option value="<?php echo $room['id']; ?>" data-giaphong="<?php echo $room['giaphong']; ?>">
                        <?php echo $room['tenphong']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="tienphong">Tiền Phòng:</label>
            <input type="number" step="0.01" id="tienphong" name="tienphong" readonly>
        </div>
        <div class="form-group">
            <label for="khachthue_id">Khách Thuê:</label>
            <input type="number" id="khachthue_id" name="khachthue_id" required>
        </div>
        <div class="form-group">
            <label for="sonuoccu">Số Nước Cũ:</label>
            <input type="number" id="sonuoccu" name="sonuoccu" required oninput="calculateTienNuoc()">
        </div>
        <div class="form-group">
            <label for="sonuocmoi">Số Nước Mới:</label>
            <input type="number" id="sonuocmoi" name="sonuocmoi" required oninput="calculateTienNuoc()">
        </div>
            <div class="form-group">
                <label for="tiennuoc">Tiền Nước:</label>
                <input type="number" step="0.01" id="tiennuoc" name="tiennuoc" readonly>
            </div>
        <div class="form-group">
            <label for="sodiencu">Số Điện Cũ:</label>
            <input type="number" id="sodiencu" name="sodiencu" required>
        </div>
        <div class="form-group">
            <label for="sodienmoi">Số Điện Mới:</label>
            <input type="number" id="sodienmoi" name="sodienmoi" required>
        </div>
        <div class="form-group">
            <label for="tiendien">Tiền Điện:</label>
            <input type="number" step="0.01" id="tiendien" name="tiendien" required>
        </div>
        <div class="form-group">
            <label for="tienrac">Tiền Rác:</label>
            <input type="number" step="0.01" id="tienrac" name="tienrac" required>
        </div>
        <div class="form-group">
            <label for="tienwifi">Tiền Wi-Fi:</label>
            <input type="number" step="0.01" id="tienwifi" name="tienwifi" required>
        </div>
        <div class="form-group">
            <label for="tongtien">Tổng Tiền:</label>
            <input type="number" step="0.01" id="tongtien" name="tongtien" required>
        </div>
        <div class="form-group">
            <label for="trangthai">Trạng Thái:</label>
            <input type="number" id="trangthai" name="trangthai" required>
        </div>
        <div class="form-group">
            <label for="trangthaiguiHD">Trạng Thái Gửi Hóa Đơn:</label>
            <input type="number" id="trangthaiguiHD" name="trangthaiguiHD" required>
        </div>
        <div class="form-group">
            <label for="create_at">Ngày Tạo:</label>
            <input type="datetime-local" id="create_at" name="create_at" required>
        </div>
        <div class="form-group">
            <input type="submit" value="Thêm Hóa Đơn">
        </div>
    </form>

    <script>
        function updateTienPhong() {
            const roomSelect = document.getElementById('room_id');
            const selectedOption = roomSelect.options[roomSelect.selectedIndex];
            const giaPhong = selectedOption.getAttribute('data-giaphong');
            
            document.getElementById('chuky').value = giaPhong;
        }

        const donGiaNuoc = <?php echo $donGiaNuoc; ?>;

        function calculateTienNuoc() {
            const sonuoccu = parseFloat(document.getElementById('sonuoccu').value) || 0;
            const sonuocmoi = parseFloat(document.getElementById('sonuocmoi').value) || 0;
            const tiennuoc = (sonuocmoi - sonuoccu) * donGiaNuoc;
            document.getElementById('tiennuoc').value = tiennuoc.toFixed(2);
        }
    </script>
</body>
</html>
