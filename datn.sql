-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 25, 2024 lúc 12:49 PM
-- Phiên bản máy phục vụ: 8.0.29
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `datn`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `area`
--

CREATE TABLE `area` (
  `id` int NOT NULL,
  `tenkhuvuc` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mota` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ngaytao` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `area`
--

INSERT INTO `area` (`id`, `tenkhuvuc`, `mota`, `ngaytao`) VALUES
(5, 'Khu A', '    Gần trung tâm thương mại', '2024-10-28'),
(6, 'Khu B', '    Gần Đại học Hàng Hải Việt Nam  ', '2024-10-27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `area_room`
--

CREATE TABLE `area_room` (
  `id` int NOT NULL,
  `room_id` int DEFAULT NULL,
  `area_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `area_room`
--

INSERT INTO `area_room` (`id`, `room_id`, `area_id`) VALUES
(11, 86, 6),
(15, 91, 5),
(18, 96, 6),
(21, 87, 5),
(22, 97, 5),
(23, 98, 5),
(26, 102, 5),
(27, 99, 6),
(28, 101, 6),
(29, 103, 5),
(30, 104, 6),
(31, 105, 6),
(32, 106, 5),
(33, 107, 5);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bill`
--

CREATE TABLE `bill` (
  `id` int NOT NULL,
  `mahoadon` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `room_id` int DEFAULT NULL,
  `tenant_id` int DEFAULT NULL,
  `tienphong` float DEFAULT NULL,
  `sodiencu` int DEFAULT NULL,
  `sodienmoi` int DEFAULT NULL,
  `img_sodienmoi` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tiendien` float DEFAULT NULL,
  `sonuoccu` int DEFAULT NULL,
  `sonuocmoi` int DEFAULT NULL,
  `img_sonuocmoi` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tiennuoc` float DEFAULT NULL,
  `songuoi` int DEFAULT NULL,
  `tienrac` float DEFAULT NULL,
  `tienmang` float DEFAULT NULL,
  `tongtien` float DEFAULT NULL,
  `sotiendatra` float DEFAULT NULL,
  `sotienconthieu` float DEFAULT NULL,
  `trangthaihoadon` int DEFAULT NULL,
  `thang` int DEFAULT NULL,
  `create_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `bill`
--

INSERT INTO `bill` (`id`, `mahoadon`, `room_id`, `tenant_id`, `tienphong`, `sodiencu`, `sodienmoi`, `img_sodienmoi`, `tiendien`, `sonuoccu`, `sonuocmoi`, `img_sonuocmoi`, `tiennuoc`, `songuoi`, `tienrac`, `tienmang`, `tongtien`, `sotiendatra`, `sotienconthieu`, `trangthaihoadon`, `thang`, `create_at`) VALUES
(200, 'f7svp', 103, NULL, 2000000, 1, 3, '', 8000, 1, 3, '', 40000, 1, 10000, 50000, 2108000, 1608000, NULL, 1, 11, '2024-11-30');

--
-- Bẫy `bill`
--
DELIMITER $$
CREATE TRIGGER `after_hoadon_update_status` AFTER UPDATE ON `bill` FOR EACH ROW BEGIN
    IF OLD.trangthaihoadon = 2 AND NEW.trangthaihoadon = 1 THEN
        -- Tạo một phiếu thu mới khi trạng thái hóa đơn chuyển từ 2 (chưa thu) sang 1 (đã thu)
        INSERT INTO receipt (
            bill_id,
            room_id, 
            sotien, 
            ghichu, 
            ngaythu, 
            phuongthuc, 
            danhmucthu_id
        ) VALUES (
            NEW.id,                        -- Lưu hoadon_id
            NEW.room_id,                   -- room_id từ bảng hoadon
            NEW.tongtien,                  -- Số tiền tổng từ bảng hoadon
            'Thu tiền trọ hàng tháng',    -- Ghi chú
            NEW.create_at,                         -- Ngày thu
            1,                             -- Phương thức thanh toán (ví dụ: 1)
            1                              -- Danh mục thu (ví dụ: 1)
        );
    ELSEIF OLD.trangthaihoadon = 2 AND NEW.trangthaihoadon = 3 THEN
        -- Tạo một phiếu thu mới với số tiền đã trả khi trạng thái hóa đơn chuyển từ 2 (chưa thu) sang 3 (đang nợ)
        INSERT INTO receipt (
            bill_id,
            room_id, 
            sotien, 
            ghichu, 
            ngaythu, 
            phuongthuc, 
            danhmucthu_id
        ) VALUES (
            NEW.id,                        -- Lưu hoadon_id
            NEW.room_id,                   -- room_id từ bảng hoadon
            NEW.sotiendatra,               -- Số tiền đã trả từ bảng hoadon
             CONCAT('Thu tiền trọ hàng tháng - Còn nợ. Số tiền còn nợ: ', 
                   FORMAT(NEW.sotienconthieu, 0, 'de_DE'),'đ'), -- Ghi chú với số tiền định dạng có dấu chấm
           NEW.create_at,                         -- Ngày thu
            1,                             -- Phương thức thanh toán (ví dụ: 1)
            1                              -- Danh mục thu (ví dụ: 1)
        );
    ELSEIF OLD.trangthaihoadon = 3 AND NEW.trangthaihoadon = 1 THEN
        -- Tạo một phiếu thu mới khi trạng thái hóa đơn chuyển từ 3 (đang nợ) sang 1 (đã thanh toán)
        INSERT INTO receipt (
            bill_id,
            room_id, 
            sotien, 
            ghichu, 
            ngaythu, 
            phuongthuc, 
            danhmucthu_id
        ) VALUES (
            NEW.id,                        -- Lưu hoadon_id
            NEW.room_id,                   -- room_id từ bảng hoadon
            NEW.sotiendatra,               -- Số tiền đã trả từ bảng hoadon
            'Thu tiền trọ hàng tháng - Đã thanh toán phần còn nợ', -- Ghi chú
           NEW.create_at,                         -- Ngày thu
            1,                             -- Phương thức thanh toán (ví dụ: 1)
            1                              -- Danh mục thu (ví dụ: 1)
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_delete_hoadon` AFTER DELETE ON `bill` FOR EACH ROW BEGIN
    DELETE FROM receipt
    WHERE room_id = OLD.room_id
      AND sotien = OLD.tongtien;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `category_collect`
--

CREATE TABLE `category_collect` (
  `id` int NOT NULL,
  `tendanhmuc` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `create_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `category_collect`
--

INSERT INTO `category_collect` (`id`, `tendanhmuc`, `create_at`) VALUES
(1, 'Thu tiền trọ tháng', NULL),
(2, 'Thu tiền cọc', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `category_spend`
--

CREATE TABLE `category_spend` (
  `id` int NOT NULL,
  `tendanhmuc` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `create_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `category_spend`
--

INSERT INTO `category_spend` (`id`, `tendanhmuc`, `create_at`) VALUES
(9, 'Chi trả tiền cọc', NULL),
(10, 'Chi trả tiền rác', NULL),
(12, 'Chi trả tiền điện', NULL),
(13, 'Chi trả tiền nước', NULL),
(14, 'Chi trả tiền Wifi', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contract`
--

CREATE TABLE `contract` (
  `id` int NOT NULL,
  `room_id` int DEFAULT NULL,
  `soluongthanhvien` int DEFAULT NULL,
  `ngaylaphopdong` date DEFAULT NULL,
  `ngayvao` date DEFAULT NULL,
  `ngayra` date DEFAULT NULL,
  `tinhtrangcoc` int DEFAULT NULL,
  `trangthaihopdong` int DEFAULT NULL,
  `lydothanhly` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `create_at` date DEFAULT NULL,
  `ghichu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sotiencoc` float DEFAULT NULL,
  `dieukhoan1` varchar(550) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dieukhoan2` varchar(550) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dieukhoan3` varchar(550) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `thoigianthanhly` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `contract`
--

INSERT INTO `contract` (`id`, `room_id`, `soluongthanhvien`, `ngaylaphopdong`, `ngayvao`, `ngayra`, `tinhtrangcoc`, `trangthaihopdong`, `lydothanhly`, `create_at`, `ghichu`, `sotiencoc`, `dieukhoan1`, `dieukhoan2`, `dieukhoan3`, `thoigianthanhly`) VALUES
(536, 103, NULL, '2024-11-01', '2024-11-30', '2025-04-07', 1, 0, 'dđ', '2024-11-01', 'Bỏ trống', 1000000, 'Sử dụng phòng đúng mục đích đã thoả thuận, Đảm bảo các thiết bị và sửa chữa các hư hỏng trong phòng trong khi sử dụng. Nếu không sửa chữa thì khi trả phòng, bên A sẽ trừ vào tiền đặt cọc, giá trị cụ thể được tính theo giá thị trường.', 'Trả đủ tiền thuê phòng đúng kỳ hạn đã thỏa thuận, Chỉ sử dụng phòng trọ vào mục đích ở, không chứa các thiết bị gây cháy nổ, hàng cấm... cung cấp giấy tờ tùy thân để đăng ký tạm trú theo quy định, giữ gìn an ninh trật tự, nếp sống văn hóa đô thị; không tụ tập nhậu nhẹt, cờ bạc và các hành vi vi phạm pháp luật khác.', 'Tôn trọng quy tắc sinh hoạt công cộng, Không được tự ý cải tạo kiếm trúc phòng hoặc trang trí ảnh hưởng tới tường, cột, nền... Nếu có nhu cầu trên phải trao đổi với bên A để được thống nhất', '2024-12-25'),
(538, 87, NULL, '2024-09-01', '2024-09-01', '2025-03-11', 1, 0, 'không thuê nữa', '2024-09-01', 'Bỏ trống', 500000, 'Sử dụng phòng đúng mục đích đã thoả thuận, Đảm bảo các thiết bị và sửa chữa các hư hỏng trong phòng trong khi sử dụng. Nếu không sửa chữa thì khi trả phòng, bên A sẽ trừ vào tiền đặt cọc, giá trị cụ thể được tính theo giá thị trường.', 'Trả đủ tiền thuê phòng đúng kỳ hạn đã thỏa thuận, Chỉ sử dụng phòng trọ vào mục đích ở, không chứa các thiết bị gây cháy nổ, hàng cấm... cung cấp giấy tờ tùy thân để đăng ký tạm trú theo quy định, giữ gìn an ninh trật tự, nếp sống văn hóa đô thị; không tụ tập nhậu nhẹt, cờ bạc và các hành vi vi phạm pháp luật khác.', 'Tôn trọng quy tắc sinh hoạt công cộng, Không được tự ý cải tạo kiếm trúc phòng hoặc trang trí ảnh hưởng tới tường, cột, nền... Nếu có nhu cầu trên phải trao đổi với bên A để được thống nhất', '2024-12-25'),
(539, 104, NULL, '2024-12-18', '2024-12-11', '2024-12-11', 1, 0, NULL, '2024-12-18', 'Bỏ trống', 1000000, 'Sử dụng phòng đúng mục đích đã thoả thuận, Đảm bảo các thiết bị và sửa chữa các hư hỏng trong phòng trong khi sử dụng. Nếu không sửa chữa thì khi trả phòng, bên A sẽ trừ vào tiền đặt cọc, giá trị cụ thể được tính theo giá thị trường.', 'Trả đủ tiền thuê phòng đúng kỳ hạn đã thỏa thuận, Chỉ sử dụng phòng trọ vào mục đích ở, không chứa các thiết bị gây cháy nổ, hàng cấm... cung cấp giấy tờ tùy thân để đăng ký tạm trú theo quy định, giữ gìn an ninh trật tự, nếp sống văn hóa đô thị; không tụ tập nhậu nhẹt, cờ bạc và các hành vi vi phạm pháp luật khác.', 'Tôn trọng quy tắc sinh hoạt công cộng, Không được tự ý cải tạo kiếm trúc phòng hoặc trang trí ảnh hưởng tới tường, cột, nền... Nếu có nhu cầu trên phải trao đổi với bên A để được thống nhất', NULL),
(540, 91, NULL, '2024-12-18', '2024-12-17', '2024-12-09', 1, 0, NULL, '2024-12-18', 'Bỏ trống', 500000, 'Sử dụng phòng đúng mục đích đã thoả thuận, Đảm bảo các thiết bị và sửa chữa các hư hỏng trong phòng trong khi sử dụng. Nếu không sửa chữa thì khi trả phòng, bên A sẽ trừ vào tiền đặt cọc, giá trị cụ thể được tính theo giá thị trường.', 'Trả đủ tiền thuê phòng đúng kỳ hạn đã thỏa thuận, Chỉ sử dụng phòng trọ vào mục đích ở, không chứa các thiết bị gây cháy nổ, hàng cấm... cung cấp giấy tờ tùy thân để đăng ký tạm trú theo quy định, giữ gìn an ninh trật tự, nếp sống văn hóa đô thị; không tụ tập nhậu nhẹt, cờ bạc và các hành vi vi phạm pháp luật khác.', 'Tôn trọng quy tắc sinh hoạt công cộng, Không được tự ý cải tạo kiếm trúc phòng hoặc trang trí ảnh hưởng tới tường, cột, nền... Nếu có nhu cầu trên phải trao đổi với bên A để được thống nhất', NULL),
(541, 102, NULL, '2024-12-25', '2024-12-25', '2025-02-25', 1, 0, 'ssdsdsd', '2024-12-25', 'Bỏ trống', 500000, 'Sử dụng phòng đúng mục đích đã thoả thuận, Đảm bảo các thiết bị và sửa chữa các hư hỏng trong phòng trong khi sử dụng. Nếu không sửa chữa thì khi trả phòng, bên A sẽ trừ vào tiền đặt cọc, giá trị cụ thể được tính theo giá thị trường.', 'Trả đủ tiền thuê phòng đúng kỳ hạn đã thỏa thuận, Chỉ sử dụng phòng trọ vào mục đích ở, không chứa các thiết bị gây cháy nổ, hàng cấm... cung cấp giấy tờ tùy thân để đăng ký tạm trú theo quy định, giữ gìn an ninh trật tự, nếp sống văn hóa đô thị; không tụ tập nhậu nhẹt, cờ bạc và các hành vi vi phạm pháp luật khác.', 'Tôn trọng quy tắc sinh hoạt công cộng, Không được tự ý cải tạo kiếm trúc phòng hoặc trang trí ảnh hưởng tới tường, cột, nền... Nếu có nhu cầu trên phải trao đổi với bên A để được thống nhất', '2024-12-18'),
(542, 98, NULL, '2024-12-11', '2024-12-25', '2025-02-25', 1, 0, '0hghfghfg', '2024-12-11', 'Bỏ trống', 1000000, 'Sử dụng phòng đúng mục đích đã thoả thuận, Đảm bảo các thiết bị và sửa chữa các hư hỏng trong phòng trong khi sử dụng. Nếu không sửa chữa thì khi trả phòng, bên A sẽ trừ vào tiền đặt cọc, giá trị cụ thể được tính theo giá thị trường.', 'Trả đủ tiền thuê phòng đúng kỳ hạn đã thỏa thuận, Chỉ sử dụng phòng trọ vào mục đích ở, không chứa các thiết bị gây cháy nổ, hàng cấm... cung cấp giấy tờ tùy thân để đăng ký tạm trú theo quy định, giữ gìn an ninh trật tự, nếp sống văn hóa đô thị; không tụ tập nhậu nhẹt, cờ bạc và các hành vi vi phạm pháp luật khác.', 'Tôn trọng quy tắc sinh hoạt công cộng, Không được tự ý cải tạo kiếm trúc phòng hoặc trang trí ảnh hưởng tới tường, cột, nền... Nếu có nhu cầu trên phải trao đổi với bên A để được thống nhất', '2024-12-11'),
(543, 106, NULL, '2024-12-18', '2024-12-25', '2025-03-25', 1, 1, NULL, '2024-12-18', 'Bỏ trống', 500000, 'Sử dụng phòng đúng mục đích đã thoả thuận, Đảm bảo các thiết bị và sửa chữa các hư hỏng trong phòng trong khi sử dụng. Nếu không sửa chữa thì khi trả phòng, bên A sẽ trừ vào tiền đặt cọc, giá trị cụ thể được tính theo giá thị trường.', 'Trả đủ tiền thuê phòng đúng kỳ hạn đã thỏa thuận, Chỉ sử dụng phòng trọ vào mục đích ở, không chứa các thiết bị gây cháy nổ, hàng cấm... cung cấp giấy tờ tùy thân để đăng ký tạm trú theo quy định, giữ gìn an ninh trật tự, nếp sống văn hóa đô thị; không tụ tập nhậu nhẹt, cờ bạc và các hành vi vi phạm pháp luật khác.', 'Tôn trọng quy tắc sinh hoạt công cộng, Không được tự ý cải tạo kiếm trúc phòng hoặc trang trí ảnh hưởng tới tường, cột, nền... Nếu có nhu cầu trên phải trao đổi với bên A để được thống nhất', NULL),
(544, 97, NULL, '2024-12-25', '2025-02-25', '2025-04-25', 1, 0, 'fdfdf', '2024-12-25', 'Bỏ trống', 1000000, 'Sử dụng phòng đúng mục đích đã thoả thuận, Đảm bảo các thiết bị và sửa chữa các hư hỏng trong phòng trong khi sử dụng. Nếu không sửa chữa thì khi trả phòng, bên A sẽ trừ vào tiền đặt cọc, giá trị cụ thể được tính theo giá thị trường.', 'Trả đủ tiền thuê phòng đúng kỳ hạn đã thỏa thuận, Chỉ sử dụng phòng trọ vào mục đích ở, không chứa các thiết bị gây cháy nổ, hàng cấm... cung cấp giấy tờ tùy thân để đăng ký tạm trú theo quy định, giữ gìn an ninh trật tự, nếp sống văn hóa đô thị; không tụ tập nhậu nhẹt, cờ bạc và các hành vi vi phạm pháp luật khác.', 'Tôn trọng quy tắc sinh hoạt công cộng, Không được tự ý cải tạo kiếm trúc phòng hoặc trang trí ảnh hưởng tới tường, cột, nền... Nếu có nhu cầu trên phải trao đổi với bên A để được thống nhất', '2024-12-25'),
(545, 107, NULL, '2024-12-25', '2024-12-25', '2025-03-25', 1, 0, 'dfdfdfd', '2024-12-25', 'Bỏ trống', 500000, 'Sử dụng phòng đúng mục đích đã thoả thuận, Đảm bảo các thiết bị và sửa chữa các hư hỏng trong phòng trong khi sử dụng. Nếu không sửa chữa thì khi trả phòng, bên A sẽ trừ vào tiền đặt cọc, giá trị cụ thể được tính theo giá thị trường.', 'Trả đủ tiền thuê phòng đúng kỳ hạn đã thỏa thuận, Chỉ sử dụng phòng trọ vào mục đích ở, không chứa các thiết bị gây cháy nổ, hàng cấm... cung cấp giấy tờ tùy thân để đăng ký tạm trú theo quy định, giữ gìn an ninh trật tự, nếp sống văn hóa đô thị; không tụ tập nhậu nhẹt, cờ bạc và các hành vi vi phạm pháp luật khác.', 'Tôn trọng quy tắc sinh hoạt công cộng, Không được tự ý cải tạo kiếm trúc phòng hoặc trang trí ảnh hưởng tới tường, cột, nền... Nếu có nhu cầu trên phải trao đổi với bên A để được thống nhất', '2024-12-25'),
(546, 101, NULL, '2024-12-25', '2024-12-25', '2025-03-25', 1, 1, NULL, '2024-12-25', 'Bỏ trống', 1000000, 'Sử dụng phòng đúng mục đích đã thoả thuận, Đảm bảo các thiết bị và sửa chữa các hư hỏng trong phòng trong khi sử dụng. Nếu không sửa chữa thì khi trả phòng, bên A sẽ trừ vào tiền đặt cọc, giá trị cụ thể được tính theo giá thị trường.', 'Trả đủ tiền thuê phòng đúng kỳ hạn đã thỏa thuận, Chỉ sử dụng phòng trọ vào mục đích ở, không chứa các thiết bị gây cháy nổ, hàng cấm... cung cấp giấy tờ tùy thân để đăng ký tạm trú theo quy định, giữ gìn an ninh trật tự, nếp sống văn hóa đô thị; không tụ tập nhậu nhẹt, cờ bạc và các hành vi vi phạm pháp luật khác.', 'Tôn trọng quy tắc sinh hoạt công cộng, Không được tự ý cải tạo kiếm trúc phòng hoặc trang trí ảnh hưởng tới tường, cột, nền... Nếu có nhu cầu trên phải trao đổi với bên A để được thống nhất', NULL),
(547, 99, NULL, '2024-12-25', '2024-12-25', '2025-03-25', 1, 0, 'sdsdsd', '2024-12-25', 'Bỏ trống', 1111110000, 'Sử dụng phòng đúng mục đích đã thoả thuận, Đảm bảo các thiết bị và sửa chữa các hư hỏng trong phòng trong khi sử dụng. Nếu không sửa chữa thì khi trả phòng, bên A sẽ trừ vào tiền đặt cọc, giá trị cụ thể được tính theo giá thị trường.', 'Trả đủ tiền thuê phòng đúng kỳ hạn đã thỏa thuận, Chỉ sử dụng phòng trọ vào mục đích ở, không chứa các thiết bị gây cháy nổ, hàng cấm... cung cấp giấy tờ tùy thân để đăng ký tạm trú theo quy định, giữ gìn an ninh trật tự, nếp sống văn hóa đô thị; không tụ tập nhậu nhẹt, cờ bạc và các hành vi vi phạm pháp luật khác.', 'Tôn trọng quy tắc sinh hoạt công cộng, Không được tự ý cải tạo kiếm trúc phòng hoặc trang trí ảnh hưởng tới tường, cột, nền... Nếu có nhu cầu trên phải trao đổi với bên A để được thống nhất', '2024-12-25');

--
-- Bẫy `contract`
--
DELIMITER $$
CREATE TRIGGER `after_contract_insert_status` AFTER INSERT ON `contract` FOR EACH ROW BEGIN
    -- Tạo phiếu thu mới khi tinhtrangcoc = 1 (đã thu)
    IF NEW.tinhtrangcoc = 1 THEN
        -- Lấy số tiền cọc từ bảng contract để tạo phiếu thu
        INSERT INTO receipt (
            contract_id,
            room_id, 
            sotien, 
            ghichu, 
            ngaythu, 
            phuongthuc, 
            danhmucthu_id
        ) VALUES (
            NEW.id,                        -- Lưu hoadon_id
            NEW.room_id,                   -- room_id từ bảng contract
            (SELECT sotiencoc FROM contract WHERE id = NEW.id),  -- Số tiền cọc từ bảng contract
            'Thu tiền cọc phòng - Đã thu', -- Ghi chú
            NEW.ngaylaphopdong,    
            0,                             -- Phương thức thanh toán (ví dụ: 0)
            2                              -- Danh mục thu (ví dụ: 2 cho tiền cọc)
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_contract_update_status` AFTER UPDATE ON `contract` FOR EACH ROW BEGIN
    -- Kiểm tra nếu trạng thái "tinhtrangcoc" được chuyển từ 2 sang 1
    IF OLD.tinhtrangcoc = 2 AND NEW.tinhtrangcoc = 1 THEN
        -- Tạo phiếu thu mới
        INSERT INTO receipt (
            contract_id,
            room_id, 
            sotien, 
            ghichu, 
            ngaythu, 
            phuongthuc, 
            danhmucthu_id
        ) VALUES (
            NEW.id,                        -- Lưu contract_id
            NEW.room_id,                   -- room_id từ bảng contract
            (SELECT sotiencoc FROM contract WHERE id = NEW.id),  -- Lấy số tiền cọc
            'Thu tiền cọc phòng - Đã thu', -- Ghi chú
            NEW.ngaylaphopdong,                         -- Ngày thu hiện tại
            0,                             -- Phương thức thanh toán (ví dụ: 0 - tiền mặt)
            2                              -- Danh mục thu (ví dụ: 2 - tiền cọc)
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `contract_status` BEFORE INSERT ON `contract` FOR EACH ROW BEGIN
    IF NEW.ngayvao <= CURDATE() AND (NEW.ngayra IS NULL OR NEW.ngayra >= CURDATE()) THEN
        SET NEW.trangthaihopdong = 1; -- Hợp đồng đang hoạt động
    ELSEIF NEW.ngayra < CURDATE() THEN
        SET NEW.trangthaihopdong = 0; -- Hợp đồng đã quá hạn
    ELSE
        SET NEW.trangthaihopdong = 2; -- Hợp đồng sắp hết hạn
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_room_on_insert` AFTER INSERT ON `contract` FOR EACH ROW BEGIN
    UPDATE room
    SET ngayvao = NEW.ngayvao,
        ngayra = NEW.ngayra
    WHERE id = NEW.room_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_room_on_update` AFTER UPDATE ON `contract` FOR EACH ROW BEGIN
    UPDATE room
    SET ngayvao = NEW.ngayvao,
        ngayra = NEW.ngayra
    WHERE id = NEW.room_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contract_services`
--

CREATE TABLE `contract_services` (
  `id` int NOT NULL,
  `contract_id` int DEFAULT NULL,
  `services_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `contract_services`
--

INSERT INTO `contract_services` (`id`, `contract_id`, `services_id`) VALUES
(1947, 538, 5),
(1948, 538, 8),
(1949, 538, 10),
(1950, 538, 1),
(1951, 536, 5),
(1952, 536, 8),
(1953, 536, 10),
(1954, 536, 1),
(1955, 539, 5),
(1956, 539, 8),
(1957, 539, 10),
(1958, 539, 1),
(1959, 540, 5),
(1960, 540, 8),
(1961, 540, 10),
(1962, 540, 1),
(1967, 541, 5),
(1968, 541, 8),
(1969, 541, 10),
(1970, 541, 1),
(1979, 543, 5),
(1980, 543, 8),
(1981, 543, 10),
(1982, 543, 1),
(1983, 542, 5),
(1984, 542, 8),
(1985, 542, 10),
(1986, 542, 1),
(1991, 544, 5),
(1992, 544, 8),
(1993, 544, 10),
(1994, 544, 1),
(1999, 545, 5),
(2000, 545, 8),
(2001, 545, 10),
(2002, 545, 1),
(2003, 546, 5),
(2004, 546, 8),
(2005, 546, 10),
(2006, 546, 1),
(2011, 547, 5),
(2012, 547, 8),
(2013, 547, 10),
(2014, 547, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contract_tenant`
--

CREATE TABLE `contract_tenant` (
  `id` int NOT NULL,
  `contract_id_1` int DEFAULT NULL,
  `tenant_id_1` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `contract_tenant`
--

INSERT INTO `contract_tenant` (`id`, `contract_id_1`, `tenant_id_1`) VALUES
(327, 536, 584),
(329, 538, 586),
(330, 539, 587),
(331, 540, 588),
(332, 541, 589),
(333, 542, 590),
(334, 543, 591),
(335, 544, 592),
(336, 545, 593),
(337, 546, 594),
(338, 547, 595);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cost`
--

CREATE TABLE `cost` (
  `id` int NOT NULL,
  `tengia` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `giathue` float DEFAULT NULL,
  `ngaybatdau` date DEFAULT NULL,
  `ngayketthuc` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cost`
--

INSERT INTO `cost` (`id`, `tengia`, `giathue`, `ngaybatdau`, `ngayketthuc`) VALUES
(38, 'Khuyến mại 1', 3000000, '2024-10-25', '2025-02-25'),
(39, 'Khuyến mại 2', 2000000, '2024-10-25', '2025-02-25');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cost_room`
--

CREATE TABLE `cost_room` (
  `id` int NOT NULL,
  `room_id` int DEFAULT NULL,
  `cost_id` int DEFAULT NULL,
  `thoigianapdung` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cost_room`
--

INSERT INTO `cost_room` (`id`, `room_id`, `cost_id`, `thoigianapdung`) VALUES
(40, 86, 39, '2024-11-16'),
(42, 87, 39, '2024-11-16'),
(51, 103, 39, '2024-12-18'),
(52, 107, 38, '2024-12-19'),
(53, 104, 39, '2024-12-18'),
(54, 97, 39, '2024-12-12'),
(55, 98, 39, '2024-12-17'),
(56, 99, 39, '2024-12-03'),
(57, 105, 38, '2024-12-17'),
(58, 102, 39, '2024-12-10'),
(59, 91, 39, '2024-12-17');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `equipment`
--

CREATE TABLE `equipment` (
  `id` int NOT NULL,
  `mathietbi` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tenthietbi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `giathietbi` float DEFAULT NULL,
  `ngaynhap` date DEFAULT NULL,
  `soluongnhap` int DEFAULT NULL,
  `soluongtonkho` int DEFAULT NULL,
  `thoihanbaohanh` date DEFAULT NULL,
  `ngaybaotri` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `equipment`
--

INSERT INTO `equipment` (`id`, `mathietbi`, `tenthietbi`, `giathietbi`, `ngaynhap`, `soluongnhap`, `soluongtonkho`, `thoihanbaohanh`, `ngaybaotri`) VALUES
(95, '15247', 'Televison', 1000000, '2024-11-14', 50, 37, '2025-02-14', '2025-02-20'),
(96, '65478', 'Bình nóng lạnh', 1000000, '2024-11-14', 50, 36, '2024-12-31', '2024-12-20'),
(97, '78972', 'Giường', 800000, '2024-11-14', 50, 35, '2025-02-12', '2025-02-12'),
(98, '18252', 'Điều hoà', 1000000, '2024-11-14', 50, 35, '2025-02-12', '2025-02-12'),
(102, '57166', 'Máy sưởi', 800000, '2024-12-18', 14, 12, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `equipment_room`
--

CREATE TABLE `equipment_room` (
  `id` int NOT NULL,
  `room_id` int DEFAULT NULL,
  `equipment_id` int DEFAULT NULL,
  `soluongcap` int DEFAULT NULL,
  `thoigiancap` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `equipment_room`
--

INSERT INTO `equipment_room` (`id`, `room_id`, `equipment_id`, `soluongcap`, `thoigiancap`) VALUES
(402, 86, 96, 1, '2024-11-14'),
(403, 86, 97, 1, '2024-11-14'),
(404, 86, 95, 1, '2024-11-14'),
(406, 87, 96, 1, '2024-11-14'),
(407, 87, 97, 1, '2024-11-14'),
(408, 87, 95, 1, '2024-11-14'),
(409, 87, 98, 1, '2024-11-14'),
(410, 91, 96, 1, '2024-11-14'),
(411, 91, 97, 1, '2024-11-14'),
(412, 91, 95, 1, '2024-11-14'),
(413, 91, 98, 1, '2024-11-14'),
(414, 96, 96, 1, '2024-11-14'),
(415, 96, 97, 1, '2024-11-14'),
(416, 96, 95, 1, '2024-11-14'),
(417, 96, 98, 1, '2024-11-14'),
(426, 86, 98, 1, '2024-11-14'),
(428, 105, 96, 2, '2024-11-15'),
(429, 105, 97, 2, '2024-11-15'),
(431, 105, 98, 2, '2024-11-15'),
(432, 104, 96, 1, '2024-11-15'),
(433, 104, 97, 1, '2024-11-15'),
(434, 104, 95, 1, '2024-11-15'),
(435, 104, 98, 1, '2024-11-15'),
(437, 103, 97, 1, '2024-11-15'),
(438, 103, 95, 1, '2024-11-15'),
(439, 103, 98, 1, '2024-11-15'),
(440, 102, 96, 1, '2024-11-15'),
(441, 102, 97, 1, '2024-11-15'),
(442, 102, 95, 1, '2024-11-15'),
(443, 102, 98, 1, '2024-11-15'),
(444, 101, 96, 1, '2024-11-15'),
(445, 101, 97, 1, '2024-11-15'),
(446, 101, 95, 1, '2024-11-15'),
(447, 101, 98, 1, '2024-11-15'),
(448, 99, 96, 1, '2024-11-15'),
(449, 99, 97, 1, '2024-11-15'),
(450, 99, 95, 1, '2024-11-15'),
(451, 99, 98, 1, '2024-11-15'),
(452, 98, 96, 1, '2024-11-15'),
(453, 98, 97, 1, '2024-11-15'),
(454, 98, 95, 1, '2024-11-15'),
(455, 98, 98, 1, '2024-11-15'),
(456, 97, 96, 1, '2024-11-15'),
(457, 97, 97, 1, '2024-11-15'),
(458, 97, 95, 1, '2024-11-15'),
(459, 97, 98, 1, '2024-11-15'),
(460, 106, 96, 1, '2024-11-22'),
(461, 106, 97, 1, '2024-11-22'),
(462, 106, 95, 1, '2024-11-22'),
(463, 106, 98, 1, '2024-11-22'),
(464, 107, 96, 1, '2024-12-24'),
(465, 107, 97, 1, '2024-12-24'),
(466, 107, 95, 1, '2024-12-24'),
(467, 107, 98, 1, '2024-12-24'),
(469, 107, 102, 1, '2024-12-24');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `groups`
--

CREATE TABLE `groups` (
  `id` int NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `create_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `groups`
--

INSERT INTO `groups` (`id`, `name`, `create_at`) VALUES
(7, 'Quản lý', NULL),
(9, 'Khách thuê', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `history`
--

CREATE TABLE `history` (
  `id` int NOT NULL,
  `tenphong` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `giathue` int DEFAULT NULL,
  `thoigianapdung` date DEFAULT NULL,
  `ngayketthuc` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `login_token`
--

CREATE TABLE `login_token` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `create_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `login_token`
--

INSERT INTO `login_token` (`id`, `user_id`, `token`, `create_at`) VALUES
(351, 30, 'cde6ebe3c8ace08c73bb137caa9e0a1165575029', '2024-10-17 13:05:36'),
(352, 30, 'c6b93caa2a9fa3c427545369cec1314266a25070', '2024-10-17 20:37:17'),
(354, 30, '52e0e761f2d771316c005f7519df716fc30642cd', '2024-10-18 09:11:20'),
(355, 30, 'c7e807891dfa65b8e87c423ae493ac8c14053dcd', '2024-10-18 13:38:29'),
(356, 30, '6bca5097d55cbbff710107f988ac30c4a24eb81f', '2024-10-18 15:11:15'),
(357, 30, '1fe3919420160dc81fa422ae85312a3533137b02', '2024-10-18 15:33:43'),
(358, 30, 'bf15cfa8c2e929ef06dc6b21261cbf574f874091', '2024-10-18 17:23:49'),
(359, 30, 'ec6896bb55db9aac3282f4a7a24c35c767a4c661', '2024-10-18 17:52:39'),
(360, 30, 'e0bcc22d32ecb8e138f0b25f056eec01a4df3373', '2024-10-18 19:47:38'),
(361, 30, 'cf7f5422371fb872ccdb0ff7e71dcd9515c21e19', '2024-10-18 20:07:07'),
(363, 30, '64b0d9de83139a35112c31e6939db62af2cbaccd', '2024-10-18 23:40:54'),
(364, 30, 'e332f70a8a5955616a1106e04ba4598a4208cea0', '2024-10-19 14:45:41'),
(365, 30, '2416c73ac44f85588fe21ea649906bd7f310f55c', '2024-10-19 14:50:24'),
(367, 30, 'f6b865ab10c561653e7e1dcbea83ddd1f10be186', '2024-10-19 16:04:17'),
(368, 30, '0434afd77961c5e6fbd8ab1228a6d623dfe4d700', '2024-10-20 08:25:37'),
(369, 30, '8054bbe9aaab4d5d4ecd897a32e186222b43687b', '2024-10-20 10:29:59'),
(371, 30, 'e43c96c1a15c0c363d43c40bd222c326d1af84d2', '2024-10-20 19:54:40'),
(372, 30, '08efbdbb0ae4b6a00311fc85d5780f49a512a2c2', '2024-10-21 00:15:38'),
(373, 30, 'a4eb55e8f8b58db6c198744c95c1ce571715bcb6', '2024-10-21 11:21:09'),
(375, 30, 'dc2ba3547bcf8e3d38c38fab58e7e01ee93ff528', '2024-10-21 16:43:17'),
(377, 30, '4cf1826a7661e6a56be31359c1bb9eaaac4116e8', '2024-10-21 21:16:01'),
(378, 30, '680e574e3f24f6ed626d73ae20b69a9e786a4613', '2024-10-22 08:36:21'),
(384, 30, '53121c4835d2c5818ec1c637b5ada65182483209', '2024-10-22 17:27:51'),
(385, 30, 'e98f2e563cdee1d50e2f64636a6aa77069ed8ab8', '2024-10-22 17:33:43'),
(390, 30, 'd73b5936f0d98c03e71343401c64ba81bad27bf8', '2024-10-23 02:04:35'),
(397, 30, '60933e0622d9e28d4b458af0ed829821f8fe1504', '2024-10-24 00:04:30'),
(398, 30, '6d4517466eaa1d7912f0b557f0b9bbf662011768', '2024-10-26 01:09:26'),
(399, 30, 'b90cad76943ec2134cabba17e884b622588e56be', '2024-10-26 09:32:27'),
(400, 30, '7b8b6c56ee4f56515bb6b69dcc01d88a5383d9cd', '2024-10-26 16:25:57'),
(401, 30, 'b5b96cd5a3e4374f8a37ba6ae73b86521681ddb4', '2024-10-26 23:31:22'),
(402, 30, 'ad2cee061d7d50e40042387c23b696fbb6ff2acb', '2024-10-27 23:54:47'),
(404, 30, '9c26aa127b319e86c0e67b3fba0b03c0c1bfe934', '2024-10-28 23:31:24'),
(405, 30, '1c9ec4513f07b57feb7512cacd9d9eac7cef2b4f', '2024-10-29 00:07:59'),
(406, 30, '292b9157937b27fe62c6e95af35a2c001d25e521', '2024-10-29 13:25:48'),
(407, 30, '140aea4e25863cba2b88a9d6ace1a27f7abb6a6b', '2024-10-29 20:36:12'),
(408, 30, '74e042843ec1f0be6d9755bf5e2299f9e3fc2073', '2024-10-30 00:23:38'),
(411, 30, 'd8e2d5e41b13765a3ae36e8037c8e10440a37459', '2024-10-30 00:32:33'),
(412, 30, '4b33bb571899c7674baa732dc6f1d943c5ead371', '2024-10-30 09:21:13'),
(413, 30, '6719d7dc8ea0ccd8910f48ed998a9e2fd5b12f45', '2024-10-30 21:38:26'),
(414, 30, '5438aeef0a3366b4c9b948b8e0165b686d95972a', '2024-10-30 22:39:21'),
(415, 30, '227bb25690f37578813cb11fb10bbcf2f1fb0b8b', '2024-10-31 12:40:43'),
(416, 30, 'b30d3004465fb1275fa8d1b8315a1e295d04abe8', '2024-10-31 14:25:42'),
(417, 30, 'e73804f570477fac8f0ae7002b44e7b0112015fa', '2024-10-31 22:17:51'),
(419, 30, 'aad665521c4940bb71abaff901e0685cda47a9c4', '2024-11-02 21:26:18'),
(420, 30, '2b37a9c6a0720bc64c8592f4cdc357d93b880c2a', '2024-11-03 09:07:56'),
(421, 30, 'e314e9af4cb50a2d427211c8d6922bfc1ccc55c5', '2024-11-03 14:28:08'),
(422, 30, 'a30ef28c25d048d237181d2f87ccd31567c27b13', '2024-11-04 21:36:41'),
(423, 30, 'f594f52f9c2cc85dafe34ece79161db1378d0c0b', '2024-11-06 09:23:33'),
(424, 30, 'cc5e4f9295cf5574b29e189fc72b408f02870547', '2024-11-07 22:56:50'),
(425, 30, 'bea85b7d04065c835ba32c255cab0e9159e3db33', '2024-11-08 09:24:03'),
(426, 30, '7ec655fbf46fdca07463d84923d7b982c8e89671', '2024-11-10 08:13:42'),
(427, 30, '846cfeaa811eb452197a20028f30324fa081da78', '2024-11-12 17:29:27'),
(428, 30, 'b839b3e4208086bf05116ecfa83cbc6d9576e377', '2024-11-13 07:07:15'),
(429, 30, '19a0c60518873ce54ed551b82abbec2150658e55', '2024-11-14 21:32:05'),
(430, 30, '035547c69801d96ed180bb7efbb4cb023d0db4fd', '2024-11-15 09:01:12'),
(431, 30, '35e28cbb1876efa5170cb5d7792a9d1667b419ff', '2024-11-15 10:04:21'),
(432, 30, '8438794c8252d183182fabe033ed5269edc91aa3', '2024-11-15 13:58:20'),
(433, 30, 'a8b15a33f34e8803a6dfee386a44f314bee904f4', '2024-11-16 13:01:07'),
(434, 30, '98be7096d242bddf3ad48ce7e4101a4024b4704b', '2024-11-16 20:59:28'),
(435, 30, 'db492b2ff3b1a05dc23a72f8b35eca70745ca5fc', '2024-11-17 08:55:11'),
(436, 30, 'eb128ac693188496221f41f2d681ef9af2c45462', '2024-11-17 18:53:41'),
(437, 30, '751cb07102499f6725568fd03906f9b8f9fc458e', '2024-11-17 22:29:17'),
(438, 30, '5f95f7ebd5a4a1fdd3d3d2e0b845dd453367dec3', '2024-11-18 10:21:27'),
(439, 30, '23b61c9e7ce76032c36328368fca84c03b3fc8e8', '2024-11-18 20:59:18'),
(441, 30, '828983091b71b440ba79524f56b4ae42bf4cc3c1', '2024-11-19 12:28:49'),
(457, 30, 'c31e2f788a471336797c4150b48b25d7c84a1b32', '2024-11-19 22:32:45'),
(467, 30, '058d97f9d0d59bb57850aaa6deae3868853afe83', '2024-11-20 00:12:54'),
(468, 30, '3b453dc273f05f628cad3dbc215ffda92dcbf32d', '2024-11-20 09:17:30'),
(469, 30, 'f32cc1d2f21aa316b6b2b8aba8dd144610d8f402', '2024-11-20 12:51:56'),
(470, 30, '6c414afacc8fe3e7e2e0b02c6aa959889d934c7e', '2024-11-20 23:19:48'),
(476, 30, '7ae402edb70db74626198a6ef46bd5a72c8efe0b', '2024-11-21 23:20:09'),
(480, 30, 'a0e2c68441a534c865920b64781085e05a01decc', '2024-11-22 22:30:36'),
(487, 30, '571762db39d42b1cdf1c2a807c89440e2bb5a60e', '2024-11-23 09:42:39'),
(488, 30, '146a2a9f05f8169a39d4ca29d1cf7e0c3c1468e1', '2024-11-23 12:51:23'),
(508, 30, '72fcf17597e00b2d29462b9c1a9d8a8f0cf3e717', '2024-11-29 18:38:41'),
(509, 30, 'ec44b6a92148aa13a86df988bed5d00c2b96f132', '2024-11-29 23:47:08'),
(510, 30, '4a7900530591622142fc706a72692c81220c7ef0', '2024-11-30 16:31:38'),
(511, 30, '7efdae6bf4849cdec2c3a593cf9e6f187eeeb40a', '2024-11-30 16:31:40'),
(512, 30, 'a6f594f751a6d2ed9ec5580c4ac42611642bce09', '2024-11-30 23:31:48'),
(513, 30, '265c2fd36f6e01e12ee43c0e7fde5b727dcefd20', '2024-12-04 11:45:46'),
(519, 30, '526e2dc2fc16669c5d31b2584bdbeae8f801855f', '2024-12-05 01:37:55'),
(520, 30, '042bbdb80fd14b1018a267474329258f3c54a184', '2024-12-05 12:49:57'),
(521, 30, '0c64b074a797fe9b31ca6da6df7eb273467141d2', '2024-12-05 14:30:32'),
(524, 30, '9a8e27a0670381a5583b99365387c403fdf2d6b5', '2024-12-06 10:36:32'),
(525, 30, '050b6a44831adc5219b5e37bfa9160d3dc80ba72', '2024-12-06 15:45:47'),
(526, 30, '9133d0b2ac78f87c5853a79b5803f0d8d6eda4a7', '2024-12-06 16:35:08'),
(527, 30, '95382e6fbbd918d7c2a061f987def3e66fc3ec07', '2024-12-07 15:03:07'),
(530, 30, 'fd0c2714287803d253d9cedaaf3fe7ac259d4aa7', '2024-12-07 18:45:11'),
(531, 30, '873af0f86c1deb7ce31b1bf26502dc1f29dee1ef', '2024-12-07 19:33:50'),
(532, 30, '369745f85fa0e52455a146209981f20c4c2c725c', '2024-12-07 20:24:33'),
(533, 30, 'e55e61946b811cd32a1d4587c8780b3e1c7846a7', '2024-12-09 16:49:49'),
(534, 30, '81e4432d44b22006f8ea9294fdd74c08b188a36f', '2024-12-11 22:04:56'),
(539, 30, '572c5937a652fc40416e1c9470089bd6f4c6efeb', '2024-12-12 20:47:48'),
(540, 30, '0f43717eba66a91dce3e2b6cb97c9bd7aee7b93e', '2024-12-13 07:43:18'),
(541, 30, '5d5a15b3013865e992e629f23579a3d805adc655', '2024-12-13 07:57:54'),
(542, 30, '948b998d28bdca27e4fb8c383229c4e356ee594e', '2024-12-13 08:05:35'),
(543, 30, '22b2d6dca1ed1df0eebbb607f398db78d8b3afd5', '2024-12-14 05:56:56'),
(544, 30, '0b9d49cfb613759b9a7e90443aa774e9a88deb1c', '2024-12-14 08:57:58'),
(545, 30, '156c67d8de8192a0f7e2dba5b6df3d34b97eb038', '2024-12-16 14:07:55'),
(546, 30, '10bf687fd1ffc6c7c19d7900d227d5357f00bcf3', '2024-12-18 19:27:14'),
(547, 30, 'a2db82b5a8d690915086ab6a2f3140ee1497d7ac', '2024-12-21 10:05:49'),
(549, 35, '4d2dc671c0364abd4d915435ba9fe6b2a59260e3', '2024-12-22 15:38:46'),
(552, 35, 'b7e8dfc3fe2d0169a96d701b02656b4bec1d6be7', '2024-12-22 16:08:08'),
(559, 30, '433c466c26baf7eb5c87585681e94fd687686626', '2024-12-22 18:31:10'),
(560, 30, '2a00121171d579875f1f5973bc96d9c485bf2400', '2024-12-22 19:09:26'),
(563, 30, '715b86adb7326cdf90694256314aa5bba2d73003', '2024-12-22 23:15:30'),
(564, 30, 'f07edd732ea60bae6abe50d73db81d9c912cf873', '2024-12-22 23:35:26'),
(565, 30, '06b4c940536c04a0cd383a4558108cfc64d7bcb0', '2024-12-23 08:52:04'),
(566, 30, '80bb0c6ace1a1c7decaee40d7419e06f244ca04c', '2024-12-23 12:00:11'),
(567, 30, '85c6c5f74e64f34f8b063f387ccf9e513bc0d842', '2024-12-24 07:13:56'),
(568, 30, '67a7cd2e31499c7ec3f46cb29d59286000a4f624', '2024-12-25 13:55:49');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payment`
--

CREATE TABLE `payment` (
  `id` int NOT NULL,
  `room_id` int DEFAULT NULL,
  `sotien` float DEFAULT NULL,
  `ghichu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ngaychi` date DEFAULT NULL,
  `phuongthuc` int DEFAULT NULL,
  `danhmucchi_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `payment`
--

INSERT INTO `payment` (`id`, `room_id`, `sotien`, `ghichu`, `ngaychi`, `phuongthuc`, `danhmucchi_id`) VALUES
(18, 103, 8000, 'đã chi', '2024-11-30', 0, 12),
(19, 103, 40000, 'đã chi', '2024-11-30', 0, 13),
(20, 103, 50000, 'đã chi', '2024-11-30', 0, 14),
(21, 103, 10000, 'đã chi', '2024-11-30', 0, 10);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `receipt`
--

CREATE TABLE `receipt` (
  `id` int NOT NULL,
  `room_id` int DEFAULT NULL,
  `sotien` float DEFAULT NULL,
  `ghichu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ngaythu` date DEFAULT NULL,
  `phuongthuc` int DEFAULT NULL,
  `danhmucthu_id` int DEFAULT NULL,
  `bill_id` int DEFAULT NULL,
  `contract_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `receipt`
--

INSERT INTO `receipt` (`id`, `room_id`, `sotien`, `ghichu`, `ngaythu`, `phuongthuc`, `danhmucthu_id`, `bill_id`, `contract_id`) VALUES
(241, 103, 1000000, 'Thu tiền cọc phòng - Đã thu', '2024-11-01', 0, 2, NULL, 536),
(242, 103, 500000, 'Thu tiền trọ hàng tháng - Còn nợ. Số tiền còn nợ: 1.608.000đ', '2024-11-30', 1, 1, 200, NULL),
(243, 103, 1608000, 'Thu tiền trọ hàng tháng - Đã thanh toán phần còn nợ', '2024-11-30', 1, 1, 200, NULL),
(246, 87, 500000, 'Thu tiền cọc phòng - Đã thu', '2024-09-01', 0, 2, NULL, 538),
(247, 104, 1000000, 'Thu tiền cọc phòng - Đã thu', '2024-12-18', 0, 2, NULL, 539),
(248, 91, 500000, 'Thu tiền cọc phòng - Đã thu', '2024-12-18', 0, 2, NULL, 540),
(249, 102, 500000, 'Thu tiền cọc phòng - Đã thu', '2024-12-25', 0, 2, NULL, 541),
(250, 98, 1000000, 'Thu tiền cọc phòng - Đã thu', '2024-12-11', 0, 2, NULL, 542),
(251, 106, 500000, 'Thu tiền cọc phòng - Đã thu', '2024-12-18', 0, 2, NULL, 543),
(252, 97, 1000000, 'Thu tiền cọc phòng - Đã thu', '2024-12-25', 0, 2, NULL, 544),
(253, 107, 500000, 'Thu tiền cọc phòng - Đã thu', '2024-12-25', 0, 2, NULL, 545),
(254, 101, 1000000, 'Thu tiền cọc phòng - Đã thu', '2024-12-25', 0, 2, NULL, 546),
(255, 99, 1111110000, 'Thu tiền cọc phòng - Đã thu', '2024-12-25', 0, 2, NULL, 547);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `room`
--

CREATE TABLE `room` (
  `id` int NOT NULL,
  `image` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tenphong` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dientich` int DEFAULT NULL,
  `tiencoc` float DEFAULT NULL,
  `soluong` int DEFAULT '0',
  `soluongtoida` int DEFAULT NULL,
  `ngayvao` date DEFAULT NULL,
  `ngayra` date DEFAULT NULL,
  `trangthai` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `room`
--

INSERT INTO `room` (`id`, `image`, `tenphong`, `dientich`, `tiencoc`, `soluong`, `soluongtoida`, `ngayvao`, `ngayra`, `trangthai`) VALUES
(86, '/datn/uploads/images/anh8.jpg', 'Phòng B01', 20, 300000, 0, 2, '2024-10-31', '2025-03-11', 0),
(87, '/datn/uploads/images/anh2.jpg', 'Phòng A02', 30, 1000000, 0, 3, '2024-09-01', '2025-03-11', 0),
(91, '/datn/uploads/images/anh4.jpg', 'Phòng A04', 40, 1000000, 1, 4, '2024-12-17', '2024-12-09', 1),
(96, '/datn/uploads/images/anh5.jpg', 'Phòng B05', 20, 1000000, 0, 2, '2024-11-23', '2025-01-23', 0),
(97, '/datn/uploads/images/anh3.jpg', 'Phòng A03', 30, 1000000, 1, 3, '2025-02-25', '2025-04-25', 1),
(98, '/datn/uploads/images/anh5.jpg', 'Phòng A05', 30, 1000000, 1, 3, '2024-12-25', '2025-02-25', 1),
(99, '/datn/uploads/images/anh12.jpg', 'Phòng B04', 20, 1000000, 1, 2, '2024-12-25', '2025-03-25', 1),
(101, '/datn/uploads/images/anh13.jpg', 'Phòng B06', 40, 1000000, 1, 4, '2024-12-25', '2025-03-25', 1),
(102, '/datn/uploads/images/anh12.jpg', 'Phòng A06', 20, 1000000, 1, 2, '2024-12-25', '2025-02-25', 1),
(103, '/datn/uploads/images/anh10.jpg', 'Phòng A01', 30, 1000000, 1, 3, '2024-11-30', '2025-04-07', 1),
(104, '/datn/uploads/images/anh10.jpg', 'Phòng B02', 30, 1000000, 1, 3, '2024-12-11', '2024-12-11', 1),
(105, '/datn/uploads/images/anh9.jpg', 'Phòng B03', 20, 1000000, 0, 2, '2024-11-13', '2024-11-06', 0),
(106, '/datn/uploads/images/anh6.jpg', 'Phòng A07', 40, 1000000, 1, 4, '2024-12-25', '2025-03-25', 1),
(107, '/datn/uploads/images/anh4.jpg', 'Phòng A10', 30, 200000, 1, 3, '2024-12-25', '2025-03-25', 1);

--
-- Bẫy `room`
--
DELIMITER $$
CREATE TRIGGER `update_trangthai` BEFORE UPDATE ON `room` FOR EACH ROW BEGIN
    IF NEW.soluong > 0 THEN
        SET NEW.trangthai = 1;
    ELSE
        SET NEW.trangthai = 0;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `services`
--

CREATE TABLE `services` (
  `id` int NOT NULL,
  `tendichvu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `donvitinh` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `giadichvu` float DEFAULT NULL,
  `create_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `services`
--

INSERT INTO `services` (`id`, `tendichvu`, `donvitinh`, `giadichvu`, `create_at`) VALUES
(1, 'Tiền điện', 'KWh', 4000, NULL),
(5, 'Tiền nước', 'khối', 20000, NULL),
(8, 'Tiền rác', 'người', 10000, NULL),
(10, 'Tiền Wifi', 'người', 50000, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tenant`
--

CREATE TABLE `tenant` (
  `id` int NOT NULL,
  `tenkhach` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sdt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ngaysinh` date DEFAULT NULL,
  `gioitinh` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `diachi` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nghenghiep` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cmnd` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ngaycap` date DEFAULT NULL,
  `anhmattruoc` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anhmatsau` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `room_id` int DEFAULT NULL,
  `trangthai` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tenant`
--

INSERT INTO `tenant` (`id`, `tenkhach`, `sdt`, `ngaysinh`, `gioitinh`, `diachi`, `nghenghiep`, `cmnd`, `ngaycap`, `anhmattruoc`, `anhmatsau`, `room_id`, `trangthai`) VALUES
(584, 'Nguyễn Văn A', '0886558169', '2024-10-07', 'Nam', 'Số 78, Đường Võ Nguyên Giáp, Quận Sơn Trà, Đà Nẵng', NULL, '678901234567', NULL, '/datn/uploads/images/57a785c2b94902175b58.jpg', '/datn/uploads/images/1d041e2b24a09ffec6b1.jpg', 103, 0),
(586, 'Nguyễn Văn C', '0886556178', '2024-12-03', 'Nam', 'Số 78, Đường Võ Nguyên Giáp, Quận Sơn Trà, Đà Nẵng', NULL, '088766545612', NULL, '/datn/uploads/images/4a1f6c7b50f0ebaeb2e1.jpg', '/datn/uploads/images/1d041e2b24a09ffec6b1.jpg', 87, 1),
(587, 'Nguyễn Văn M', NULL, '2024-12-04', 'Nam', '456 Đường Hùng Vương, Phường 6, Quận 6, TP.HCM', NULL, '097676787', NULL, NULL, NULL, 104, 0),
(588, 'Phạm Bảo Ngọc', NULL, '2024-12-17', 'Nam', 'Số 78, Đường Võ Nguyên Giáp, Quận Sơn Trà, Đà Nẵng', NULL, '098787878777', NULL, NULL, NULL, 91, NULL),
(589, 'Nguyễn Văn Linh', NULL, '2024-12-11', 'Nam', '456 Đường Hùng Vương, Phường 6, Quận 6, TP.HCM', NULL, '098989898989', NULL, NULL, NULL, 102, NULL),
(590, 'Phạm Bảo Ngọc', NULL, '2024-12-04', 'Nam', '456 Đường Hùng Vương, Phường 6, Quận 6, TP.HCM', NULL, '088777666733', NULL, NULL, NULL, 98, 1),
(591, 'Nguyễn Văn A', NULL, '2024-12-11', 'Nam', '456 Đường Hùng Vương, Phường 6, Quận 6, TP.HCM', NULL, '087876787733', NULL, NULL, NULL, 106, 0),
(592, 'Nguyễn Văn C', NULL, '2024-12-11', 'Nữ', '456 Đường Hùng Vương, Phường 6, Quận 6, TP.HCM', NULL, '098767878788', NULL, NULL, NULL, 97, 1),
(593, 'Phạm Bảo Ngọc', NULL, '2024-12-11', 'Nam', 'Số 78, Đường Võ Nguyên Giáp, Quận Sơn Trà, Đà Nẵng', NULL, '098787878787', NULL, NULL, NULL, 107, 1),
(594, 'Nguyễn Vinh Quang', NULL, '2024-12-06', 'Nữ', '456 Đường Hùng Vương, Phường 6, Quận 6, TP.HCM', NULL, '098765767656', NULL, NULL, NULL, 101, 0),
(595, 'Trần Vinh Quang', NULL, '2024-12-05', 'Nam', '456 Đường Hùng Vương, Phường 6, Quận 6, TP.HCM', NULL, '098765676566', NULL, NULL, NULL, 99, 1);

--
-- Bẫy `tenant`
--
DELIMITER $$
CREATE TRIGGER `update_room_quantity_on_delete` AFTER DELETE ON `tenant` FOR EACH ROW BEGIN
    -- Giảm số lượng của phòng mà khách thuê đã rời đi
    UPDATE room
    SET soluong = soluong - 1
    WHERE id = OLD.room_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_room_quantity_on_insert` AFTER INSERT ON `tenant` FOR EACH ROW BEGIN
    -- Tăng số lượng của phòng mới được thuê
    UPDATE room
    SET soluong = soluong + 1
    WHERE id = NEW.room_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_room_quantity_on_update` AFTER UPDATE ON `tenant` FOR EACH ROW BEGIN
    -- Giảm số lượng của phòng cũ
    UPDATE room
    SET soluong = soluong - 1
    WHERE id = OLD.room_id;

    -- Tăng số lượng của phòng mới được chọn
    UPDATE room
    SET soluong = soluong + 1
    WHERE id = NEW.room_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `fullname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `group_id` int DEFAULT NULL,
  `status` int DEFAULT '0',
  `last_activity` datetime DEFAULT NULL,
  `forget_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `room_id` int DEFAULT '0',
  `create_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `group_id`, `status`, `last_activity`, `forget_token`, `room_id`, `create_at`) VALUES
(30, 'Nguyễn Ngọc Nguyên', 'ngocnguyen2k02@gmail.com', '$2y$10$uo.k.ZWqDtho4Xtgp1GWBuqbARMWjji5zr0LMaTTHAmOKc0/PybcW', 7, 1, '2024-09-03 13:01:36', NULL, NULL, '2024-10-01'),
(35, 'Nguyễn Văn A', 'nguyenvana@gmail.com', '$2y$10$UxhniRn.B0oFI5XgMnAB8.f/ianhCqB8lbapThKUuJbNx7QE6l1Ty', 9, 1, NULL, NULL, 103, '2024-12-05');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `area`
--
ALTER TABLE `area`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `area_room`
--
ALTER TABLE `area_room`
  ADD PRIMARY KEY (`id`),
  ADD KEY `area_ibfk_1` (`room_id`),
  ADD KEY `area_ibfk_2` (`area_id`);

--
-- Chỉ mục cho bảng `bill`
--
ALTER TABLE `bill`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Chỉ mục cho bảng `category_collect`
--
ALTER TABLE `category_collect`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `category_spend`
--
ALTER TABLE `category_spend`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `contract`
--
ALTER TABLE `contract`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`);

--
-- Chỉ mục cho bảng `contract_services`
--
ALTER TABLE `contract_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contract_services_1` (`contract_id`),
  ADD KEY `contract_services_2` (`services_id`);

--
-- Chỉ mục cho bảng `contract_tenant`
--
ALTER TABLE `contract_tenant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_contract_ibfk_1` (`contract_id_1`),
  ADD KEY `tenant_contract_ibfk_2` (`tenant_id_1`);

--
-- Chỉ mục cho bảng `cost`
--
ALTER TABLE `cost`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `cost_room`
--
ALTER TABLE `cost_room`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cost_ibfk_1` (`room_id`),
  ADD KEY `cost_ibfk_2` (`cost_id`);

--
-- Chỉ mục cho bảng `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `equipment_room`
--
ALTER TABLE `equipment_room`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_ibfk_1` (`room_id`),
  ADD KEY `equipment_ibfk_2` (`equipment_id`);

--
-- Chỉ mục cho bảng `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `login_token`
--
ALTER TABLE `login_token`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `danhmucchi_id` (`danhmucchi_id`);

--
-- Chỉ mục cho bảng `receipt`
--
ALTER TABLE `receipt`
  ADD PRIMARY KEY (`id`),
  ADD KEY `danhmucthu_id` (`danhmucthu_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `receipt_ibfk_3` (`bill_id`),
  ADD KEY `receipt_ibfk_4` (`contract_id`);

--
-- Chỉ mục cho bảng `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tenant`
--
ALTER TABLE `tenant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `room_id` (`room_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `area`
--
ALTER TABLE `area`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `area_room`
--
ALTER TABLE `area_room`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT cho bảng `bill`
--
ALTER TABLE `bill`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;

--
-- AUTO_INCREMENT cho bảng `category_collect`
--
ALTER TABLE `category_collect`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `category_spend`
--
ALTER TABLE `category_spend`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `contract`
--
ALTER TABLE `contract`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=548;

--
-- AUTO_INCREMENT cho bảng `contract_services`
--
ALTER TABLE `contract_services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2015;

--
-- AUTO_INCREMENT cho bảng `contract_tenant`
--
ALTER TABLE `contract_tenant`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=339;

--
-- AUTO_INCREMENT cho bảng `cost`
--
ALTER TABLE `cost`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT cho bảng `cost_room`
--
ALTER TABLE `cost_room`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT cho bảng `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT cho bảng `equipment_room`
--
ALTER TABLE `equipment_room`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=470;

--
-- AUTO_INCREMENT cho bảng `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `history`
--
ALTER TABLE `history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `login_token`
--
ALTER TABLE `login_token`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=569;

--
-- AUTO_INCREMENT cho bảng `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `receipt`
--
ALTER TABLE `receipt`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=256;

--
-- AUTO_INCREMENT cho bảng `room`
--
ALTER TABLE `room`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT cho bảng `services`
--
ALTER TABLE `services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `tenant`
--
ALTER TABLE `tenant`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=596;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `area_room`
--
ALTER TABLE `area_room`
  ADD CONSTRAINT `area_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `room` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `area_ibfk_2` FOREIGN KEY (`area_id`) REFERENCES `area` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Các ràng buộc cho bảng `bill`
--
ALTER TABLE `bill`
  ADD CONSTRAINT `bill_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `room` (`id`),
  ADD CONSTRAINT `bill_ibfk_3` FOREIGN KEY (`tenant_id`) REFERENCES `tenant` (`id`);

--
-- Các ràng buộc cho bảng `contract`
--
ALTER TABLE `contract`
  ADD CONSTRAINT `contract_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `room` (`id`);

--
-- Các ràng buộc cho bảng `contract_services`
--
ALTER TABLE `contract_services`
  ADD CONSTRAINT `contract_services_1` FOREIGN KEY (`contract_id`) REFERENCES `contract` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `contract_services_2` FOREIGN KEY (`services_id`) REFERENCES `services` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Các ràng buộc cho bảng `contract_tenant`
--
ALTER TABLE `contract_tenant`
  ADD CONSTRAINT `tenant_contract_ibfk_1` FOREIGN KEY (`contract_id_1`) REFERENCES `contract` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `tenant_contract_ibfk_2` FOREIGN KEY (`tenant_id_1`) REFERENCES `tenant` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Các ràng buộc cho bảng `cost_room`
--
ALTER TABLE `cost_room`
  ADD CONSTRAINT `cost_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `room` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `cost_ibfk_2` FOREIGN KEY (`cost_id`) REFERENCES `cost` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Các ràng buộc cho bảng `equipment_room`
--
ALTER TABLE `equipment_room`
  ADD CONSTRAINT `equipment_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `room` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `equipment_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Các ràng buộc cho bảng `login_token`
--
ALTER TABLE `login_token`
  ADD CONSTRAINT `login_token_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`danhmucchi_id`) REFERENCES `category_spend` (`id`);

--
-- Các ràng buộc cho bảng `receipt`
--
ALTER TABLE `receipt`
  ADD CONSTRAINT `receipt_ibfk_1` FOREIGN KEY (`danhmucthu_id`) REFERENCES `category_collect` (`id`),
  ADD CONSTRAINT `receipt_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `room` (`id`),
  ADD CONSTRAINT `receipt_ibfk_3` FOREIGN KEY (`bill_id`) REFERENCES `bill` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `receipt_ibfk_4` FOREIGN KEY (`contract_id`) REFERENCES `contract` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Các ràng buộc cho bảng `tenant`
--
ALTER TABLE `tenant`
  ADD CONSTRAINT `tenant_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `room` (`id`);

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `room` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
