-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 18, 2024 lúc 04:12 PM
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
(31, 105, 6);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bill`
--

CREATE TABLE `bill` (
  `id` int NOT NULL,
  `mahoadon` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `room_id` int DEFAULT NULL,
  `tenant_id` int DEFAULT NULL,
  `chuky` int DEFAULT NULL,
  `songayle` int DEFAULT NULL,
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
  `nocu` float DEFAULT NULL,
  `trangthaihoadon` int DEFAULT NULL,
  `create_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
            'Thu tiền nhà hàng tháng',     -- Ghi chú
            NOW(),    
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
            'Thu tiền nhà hàng tháng - Đang nợ', -- Ghi chú
            NOW(),    
            1,                             -- Phương thức thanh toán (ví dụ: 1)
            1                              -- Danh mục thu (ví dụ: 1)
        );
    ELSEIF OLD.trangthaihoadon = 3 AND NEW.trangthaihoadon = 1 THEN
        -- Cập nhật lại số tiền đã trả khi trạng thái hóa đơn chuyển từ 3 (đang nợ) sang 1 (đã thu)
        UPDATE receipt
        SET
            sotien = NEW.sotiendatra,
            ngaythu = NOW(),
            ghichu = 'Thu tiền nhà hàng tháng - Đã thanh toán'
        WHERE bill_id = NEW.id;
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
(1, 'Hoá đơn thu tiền nhà', NULL),
(2, 'Tiền cọc phòng trọ', NULL);

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
(9, 'Tiền ủng hộ', NULL);

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
  `dieukhoan3` varchar(550) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `contract`
--

INSERT INTO `contract` (`id`, `room_id`, `soluongthanhvien`, `ngaylaphopdong`, `ngayvao`, `ngayra`, `tinhtrangcoc`, `trangthaihopdong`, `lydothanhly`, `create_at`, `ghichu`, `sotiencoc`, `dieukhoan1`, `dieukhoan2`, `dieukhoan3`) VALUES
(475, 103, NULL, '2024-11-18', '2024-11-18', '2025-02-18', 1, 1, NULL, '2024-11-18', 'Bỏ trống', 1000000, 'Sử dụng phòng đúng mục đích đã thoả thuận, Đảm bảo các thiết bị và sửa chữa các hư hỏng trong phòng trong khi sử dụng. Nếu không sửa chữa thì khi trả phòng, bên A sẽ trừ vào tiền đặt cọc, giá trị cụ thể được tính theo giá thị trường.', 'Trả đủ tiền thuê phòng đúng kỳ hạn đã thỏa thuận, Chỉ sử dụng phòng trọ vào mục đích ở, không chứa các thiết bị gây cháy nổ, hàng cấm... cung cấp giấy tờ tùy thân để đăng ký tạm trú theo quy định, giữ gìn an ninh trật tự, nếp sống văn hóa đô thị; không tụ tập nhậu nhẹt, cờ bạc và các hành vi vi phạm pháp luật khác.', 'Tôn trọng quy tắc sinh hoạt công cộng, Không được tự ý cải tạo kiếm trúc phòng hoặc trang trí ảnh hưởng tới tường, cột, nền... Nếu có nhu cầu trên phải trao đổi với bên A để được thống nhất'),
(476, 86, NULL, '2024-11-18', '2024-12-18', '2025-02-18', 2, 2, NULL, '2024-11-18', 'Bỏ trống', 1000000, 'Sử dụng phòng đúng mục đích đã thoả thuận, Đảm bảo các thiết bị và sửa chữa các hư hỏng trong phòng trong khi sử dụng. Nếu không sửa chữa thì khi trả phòng, bên A sẽ trừ vào tiền đặt cọc, giá trị cụ thể được tính theo giá thị trường.', 'Trả đủ tiền thuê phòng đúng kỳ hạn đã thỏa thuận, Chỉ sử dụng phòng trọ vào mục đích ở, không chứa các thiết bị gây cháy nổ, hàng cấm... cung cấp giấy tờ tùy thân để đăng ký tạm trú theo quy định, giữ gìn an ninh trật tự, nếp sống văn hóa đô thị; không tụ tập nhậu nhẹt, cờ bạc và các hành vi vi phạm pháp luật khác.', 'Tôn trọng quy tắc sinh hoạt công cộng, Không được tự ý cải tạo kiếm trúc phòng hoặc trang trí ảnh hưởng tới tường, cột, nền... Nếu có nhu cầu trên phải trao đổi với bên A để được thống nhất'),
(477, 87, NULL, '2024-11-18', '2024-11-18', '2025-02-18', 2, 1, NULL, '2024-11-18', 'Bỏ trống', 1000000, 'Sử dụng phòng đúng mục đích đã thoả thuận, Đảm bảo các thiết bị và sửa chữa các hư hỏng trong phòng trong khi sử dụng. Nếu không sửa chữa thì khi trả phòng, bên A sẽ trừ vào tiền đặt cọc, giá trị cụ thể được tính theo giá thị trường.', 'Trả đủ tiền thuê phòng đúng kỳ hạn đã thỏa thuận, Chỉ sử dụng phòng trọ vào mục đích ở, không chứa các thiết bị gây cháy nổ, hàng cấm... cung cấp giấy tờ tùy thân để đăng ký tạm trú theo quy định, giữ gìn an ninh trật tự, nếp sống văn hóa đô thị; không tụ tập nhậu nhẹt, cờ bạc và các hành vi vi phạm pháp luật khác.', 'Tôn trọng quy tắc sinh hoạt công cộng, Không được tự ý cải tạo kiếm trúc phòng hoặc trang trí ảnh hưởng tới tường, cột, nền... Nếu có nhu cầu trên phải trao đổi với bên A để được thống nhất');

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
            NOW(),    
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
            NOW(),                         -- Ngày thu hiện tại
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
(1543, 475, 5),
(1544, 475, 8),
(1545, 475, 10),
(1546, 475, 1),
(1547, 476, 5),
(1548, 476, 8),
(1549, 476, 10),
(1550, 476, 1),
(1551, 477, 5),
(1552, 477, 8),
(1553, 477, 10),
(1554, 477, 1);

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
(258, 475, 516),
(259, 476, 517),
(260, 477, 518),
(261, 477, 519);

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
(24, 91, 39, '2024-11-15'),
(27, 96, 39, '2024-10-28'),
(30, 98, 38, '2024-10-29'),
(32, 97, 39, '2024-10-29'),
(34, 102, 38, '2024-10-30'),
(35, 101, 39, '2024-10-30'),
(36, 99, 39, '2024-10-30'),
(38, 104, 39, '2024-11-12'),
(39, 105, 38, '2024-11-12'),
(40, 86, 39, '2024-11-16'),
(41, 103, 39, '2024-11-16'),
(42, 87, 39, '2024-11-16');

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
  `soluongtonkho` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `equipment`
--

INSERT INTO `equipment` (`id`, `mathietbi`, `tenthietbi`, `giathietbi`, `ngaynhap`, `soluongnhap`, `soluongtonkho`) VALUES
(95, '15247', 'Televison', 1000000, '2024-11-14', 50, 36),
(96, '65478', 'Bình nóng lạnh', 1000000, '2024-11-14', 50, 38),
(97, '78972', 'Giường', 800000, '2024-11-14', 50, 37),
(98, '18252', 'Điều hoà', 1000000, '2024-11-14', 50, 37);

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
(430, 105, 95, 3, '2024-11-15'),
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
(459, 97, 98, 1, '2024-11-15');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `groups`
--

CREATE TABLE `groups` (
  `id` int NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `permission` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `create_at` datetime DEFAULT NULL,
  `update_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `groups`
--

INSERT INTO `groups` (`id`, `name`, `permission`, `create_at`, `update_at`) VALUES
(7, 'Quản lý', NULL, NULL, NULL),
(9, 'Khách thuê', NULL, NULL, NULL);

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
(418, 32, '1e0ed33352222239cfe7326cd55432bbd80cf5f8', '2024-11-02 09:08:19'),
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
(439, 30, '23b61c9e7ce76032c36328368fca84c03b3fc8e8', '2024-11-18 20:59:18');

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
(107, 103, 1000000, 'Thu tiền cọc phòng - Đã thu', '2024-11-18', 0, 2, NULL, 475);

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
  `ngaylaphd` int DEFAULT NULL,
  `chuky` int DEFAULT NULL,
  `ngayvao` date DEFAULT NULL,
  `ngayra` date DEFAULT NULL,
  `trangthai` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `room`
--

INSERT INTO `room` (`id`, `image`, `tenphong`, `dientich`, `tiencoc`, `soluong`, `ngaylaphd`, `chuky`, `ngayvao`, `ngayra`, `trangthai`) VALUES
(86, '', 'Phòng B01', 20, 300000, 1, 1, 1, '2024-12-18', '2025-02-18', 1),
(87, '/datn/uploads/images/tro1%20(3).jpg', 'Phòng A02', 20, 1000000, 2, 1, 5, '2024-11-18', '2025-02-18', 1),
(91, '', 'Phòng A04', 20, 1000000, 0, 1, 1, '2025-01-18', '2024-12-07', 0),
(96, '', 'Phòng B05', 20, 1000000, 0, 1, 1, '2024-11-08', '2024-11-08', 0),
(97, '', 'Phòng A03', 20, 1000000, 0, 1, 1, '2024-10-29', '2024-11-13', 0),
(98, '', 'Phòng A05', 20, 1000000, 0, 1, 1, '2024-11-18', '2024-12-18', 0),
(99, '', 'Phòng B04', 20, 1000000, 0, 1, 1, '2024-11-10', '2025-05-11', 0),
(101, '', 'Phòng B06', 20, 1000000, 0, 1, 1, '2024-11-18', '2025-01-18', 0),
(102, '', 'Phòng A06', 20, 1000000, 0, 1, 1, '2024-11-05', '2024-11-05', 0),
(103, '', 'Phòng A01', 20, 1000000, 1, 1, 1, '2024-11-18', '2025-02-18', 1),
(104, '', 'Phòng B02', 20, 1000000, 0, 1, 1, '2024-11-06', '2025-01-25', 0),
(105, '', 'Phòng B03', 20, 1000000, 0, 1, 1, '2024-12-01', '2025-01-18', 0);

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
  `room_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tenant`
--

INSERT INTO `tenant` (`id`, `tenkhach`, `sdt`, `ngaysinh`, `gioitinh`, `diachi`, `nghenghiep`, `cmnd`, `ngaycap`, `anhmattruoc`, `anhmatsau`, `room_id`) VALUES
(516, 'Nguyễn Văn An', NULL, '2024-08-18', 'Nữ', '123 Đường Lê Lợi, Quận 1, TP. Hồ Chí Minh', NULL, '012345678901', NULL, NULL, NULL, 103),
(517, 'Trần Thị Bích Ngọc', NULL, '2024-08-18', 'Nữ', '45A Đường Nguyễn Trãi, Quận 5, TP. Hồ Chí Minh', NULL, '098765432112', NULL, NULL, NULL, 86),
(518, 'Lê Hoàng Minh', NULL, '2024-11-07', 'Nam', '678 Đường Lê Duẩn, TP. Đà Nẵng', NULL, '112233445566', NULL, NULL, NULL, 87),
(519, 'Phạm Thị Lan', NULL, '2024-09-18', 'Nam', '90B Đường Trần Hưng Đạo, TP. Hà Nội', NULL, '223344556677', NULL, NULL, NULL, 87);

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
(30, 'Nguyễn Ngọc Nguyên', 'ngocnguyen2k02@gmail.com', '$2y$10$nnhAXtCgHgJSZATqG0/R4O/CLwc.lM4dPEeDURjMo2M5rb99iP9iO', 7, 1, '2024-09-03 13:01:36', '5a2505be899db4882ab8a1d2c98974ba6cbb8070', NULL, '2024-10-01'),
(32, 'Nguyễn Ngọc Nguyên', 'ngocnguyen2k981@gmail.com', '$2y$10$tswPy9zKC.oSCKeZinUfl./iySYFG0.4jpRooCj.rrjcA3m.ql28.', 7, 1, NULL, NULL, NULL, '2024-10-30');

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `area_room`
--
ALTER TABLE `area_room`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT cho bảng `bill`
--
ALTER TABLE `bill`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT cho bảng `category_collect`
--
ALTER TABLE `category_collect`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `category_spend`
--
ALTER TABLE `category_spend`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `contract`
--
ALTER TABLE `contract`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=478;

--
-- AUTO_INCREMENT cho bảng `contract_services`
--
ALTER TABLE `contract_services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1555;

--
-- AUTO_INCREMENT cho bảng `contract_tenant`
--
ALTER TABLE `contract_tenant`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=262;

--
-- AUTO_INCREMENT cho bảng `cost`
--
ALTER TABLE `cost`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT cho bảng `cost_room`
--
ALTER TABLE `cost_room`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT cho bảng `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT cho bảng `equipment_room`
--
ALTER TABLE `equipment_room`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=460;

--
-- AUTO_INCREMENT cho bảng `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `login_token`
--
ALTER TABLE `login_token`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=440;

--
-- AUTO_INCREMENT cho bảng `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `receipt`
--
ALTER TABLE `receipt`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT cho bảng `room`
--
ALTER TABLE `room`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT cho bảng `services`
--
ALTER TABLE `services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `tenant`
--
ALTER TABLE `tenant`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=520;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

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
