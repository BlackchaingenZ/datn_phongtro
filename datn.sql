-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 19, 2024 lúc 11:08 AM
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
-- Cấu trúc bảng cho bảng `bill`
--

CREATE TABLE `bill` (
  `id` int NOT NULL,
  `mahoadon` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `room_id` int DEFAULT NULL,
  `tenant_id` int DEFAULT NULL,
  `chuky` int DEFAULT NULL,
  `songayle` int DEFAULT NULL,
  `tienphong` float DEFAULT NULL,
  `sodiencu` int DEFAULT NULL,
  `sodienmoi` int DEFAULT NULL,
  `img_sodienmoi` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tiendien` float DEFAULT NULL,
  `sonuoccu` int DEFAULT NULL,
  `sonuocmoi` int DEFAULT NULL,
  `img_sonuocmoi` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tiennuoc` float DEFAULT NULL,
  `songuoi` int DEFAULT NULL,
  `tienrac` float DEFAULT NULL,
  `tienmang` float DEFAULT NULL,
  `tongtien` float DEFAULT NULL,
  `sotiendatra` float DEFAULT NULL,
  `sotienconthieu` float DEFAULT NULL,
  `nocu` float DEFAULT NULL,
  `trangthaihoadon` int DEFAULT '0',
  `create_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `bill`
--

INSERT INTO `bill` (`id`, `mahoadon`, `room_id`, `tenant_id`, `chuky`, `songayle`, `tienphong`, `sodiencu`, `sodienmoi`, `img_sodienmoi`, `tiendien`, `sonuoccu`, `sonuocmoi`, `img_sonuocmoi`, `tiennuoc`, `songuoi`, `tienrac`, `tienmang`, `tongtien`, `sotiendatra`, `sotienconthieu`, `nocu`, `trangthaihoadon`, `create_at`) VALUES
(85, '7l2RE', 58, NULL, 1, 0, 1500000, 1, 6, '/datn/uploads/images/Ch%E1%BB%89%20s%E1%BB%91%20%C4%91i%E1%BB%87n/donghodien.jpg', 20000, 1, 5, '/datn/uploads/images/ch%E1%BB%89%20s%E1%BB%91%20n%C6%B0%E1%BB%9Bc/donghonuoc.jpg', 80000, 2, 20000, 50000, 1670000, 0, 1670000, 0, 0, '2024-06-30'),
(87, 'jBpJv', 57, NULL, 1, 0, 1500000, 120, 132, '/datn/uploads/images/Ch%E1%BB%89%20s%E1%BB%91%20%C4%91i%E1%BB%87n/donghodien.jpg', 48000, 210, 212, '/datn/uploads/images/ch%E1%BB%89%20s%E1%BB%91%20n%C6%B0%E1%BB%9Bc/donghonuoc.jpg', 40000, 2, 20000, 50000, 1658000, NULL, 1658000, 0, 0, '2024-06-30'),
(90, 't26KY', 58, NULL, 1, 0, 1500000, 1, 2, '', 4000, 1, 2, '', 20000, 2, 10000, 0, 1534000, NULL, 1534000, 0, 0, '2024-06-14');

--
-- Bẫy `bill`
--
DELIMITER $$
CREATE TRIGGER `after_hoadon_update_status` AFTER UPDATE ON `bill` FOR EACH ROW BEGIN
    IF OLD.trangthaihoadon = 0 AND NEW.trangthaihoadon = 1 THEN
        -- Tạo một phiếu thu mới khi trạng thái hóa đơn chuyển từ 0 (chưa thu) sang 1 (đã thu)
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
    ELSEIF OLD.trangthaihoadon = 0 AND NEW.trangthaihoadon = 2 THEN
        -- Tạo một phiếu thu mới với số tiền đã trả khi trạng thái hóa đơn chuyển từ 0 (chưa thu) sang 2 (đang nợ)
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
    ELSEIF OLD.trangthaihoadon = 2 AND NEW.trangthaihoadon = 1 THEN
        -- Cập nhật lại số tiền đã trả khi trạng thái hóa đơn chuyển từ 2 (đang nợ) sang 1 (đã thu)
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
  `tendanhmuc` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `create_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `category_collect`
--

INSERT INTO `category_collect` (`id`, `tendanhmuc`, `create_at`) VALUES
(1, 'Thu tiền hàng tháng', '2024-05-20'),
(2, 'Thu tiền cọc', '2024-05-21'),
(3, 'Chi phí sửa chữa vật tư', '2024-05-21'),
(4, 'Thu tiền nợ', NULL),
(5, 'Thu tiền kết thúc hợp đồng', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `category_spend`
--

CREATE TABLE `category_spend` (
  `id` int NOT NULL,
  `tendanhmuc` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `create_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `category_spend`
--

INSERT INTO `category_spend` (`id`, `tendanhmuc`, `create_at`) VALUES
(1, 'Chi trả tiền điện', NULL),
(2, 'Chi trả tiền nước', NULL),
(3, 'Chi trả tiền rác', NULL),
(4, 'Chi trả tiền Wifi', NULL),
(5, 'Chi trả tiền hoàn cọc', NULL),
(6, 'Chi trả sửa chữa vật tư', NULL),
(8, 'Chi phí hoa hồng môi giới', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contract`
--

CREATE TABLE `contract` (
  `id` int NOT NULL,
  `room_id` int DEFAULT NULL,
  `tenant_id` int DEFAULT NULL,
  `soluongthanhvien` int DEFAULT NULL,
  `ngaylaphopdong` date DEFAULT NULL,
  `ngayvao` date DEFAULT NULL,
  `ngayra` date DEFAULT NULL,
  `tinhtrangcoc` int DEFAULT NULL,
  `trangthaihopdong` int DEFAULT NULL,
  `create_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `contract`
--

INSERT INTO `contract` (`id`, `room_id`, `tenant_id`, `soluongthanhvien`, `ngaylaphopdong`, `ngayvao`, `ngayra`, `tinhtrangcoc`, `trangthaihopdong`, `create_at`) VALUES
(63, 58, 58, NULL, '2024-05-20', '2024-05-20', '2024-10-20', 1, 1, '2024-06-13'),
(64, 57, 57, NULL, '2024-05-25', '2024-05-25', '2024-10-25', 1, 1, '2024-06-13'),
(65, 56, 51, NULL, '2024-05-12', '2024-05-12', '2024-09-12', 1, 1, '2024-06-13');

--
-- Bẫy `contract`
--
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
-- Cấu trúc bảng cho bảng `equipment`
--

CREATE TABLE `equipment` (
  `id` int NOT NULL,
  `tenthietbi` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `giathietbi` float DEFAULT NULL,
  `ngaynhap` date DEFAULT NULL,
  `soluongphanbo` int DEFAULT NULL,
  `soluongtonkho` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `equipment`
--

INSERT INTO `equipment` (`id`, `tenthietbi`, `giathietbi`, `ngaynhap`, `soluongphanbo`, `soluongtonkho`) VALUES
(59, 'Giường', 1000000, '2024-10-01', 6, 4),
(60, 'Điều hoà', 1000000, '2024-10-07', 4, 6),
(61, 'Tủ lạnh', 1000000, '2024-10-22', 3, 2),
(62, 'Televison', 1000000, '2024-10-09', 2, 2),
(63, 'Bình nóng lạnh', 1000000, '2024-10-23', 6, 5),
(65, 'Bàn ghế', 1000000, '2024-10-10', 1, 2),
(70, 'Lò vi sóng', 800000, '2024-10-10', NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `equipment_room`
--

CREATE TABLE `equipment_room` (
  `id` int NOT NULL,
  `room_id` int DEFAULT NULL,
  `equipment_id` int DEFAULT NULL,
  `thoigiancap` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `equipment_room`
--

INSERT INTO `equipment_room` (`id`, `room_id`, `equipment_id`, `thoigiancap`) VALUES
(2, 58, 59, '2024-09-10'),
(3, 50, 63, '2024-09-10'),
(11, 79, 61, NULL),
(12, 77, 59, NULL),
(13, 77, 62, NULL),
(18, 79, 59, NULL),
(20, 59, 65, NULL),
(22, 76, 61, NULL),
(24, 78, 60, NULL),
(25, 81, 65, NULL),
(26, 81, 60, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `groups`
--

CREATE TABLE `groups` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `permission` text COLLATE utf8mb4_general_ci,
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
  `token` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `create_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `login_token`
--

INSERT INTO `login_token` (`id`, `user_id`, `token`, `create_at`) VALUES
(345, 17, '65c0254eeca0c1770539e31a62788fd7ee81e3cd', '2024-06-13 07:47:19'),
(348, 17, '8e6f8fca0fef96a504b2701d52e884fabb7d3727', '2024-06-14 09:10:02'),
(349, 17, '1d935ff3b0ef82f7801b0dab5fbd200fd48e48ab', '2024-06-14 13:53:06'),
(350, 17, '75850956e9292ea88b4b5fbc99ba24e2c217a53e', '2024-06-14 14:03:55'),
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
(367, 30, 'f6b865ab10c561653e7e1dcbea83ddd1f10be186', '2024-10-19 16:04:17');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payment`
--

CREATE TABLE `payment` (
  `id` int NOT NULL,
  `room_id` int DEFAULT NULL,
  `sotien` float DEFAULT NULL,
  `ghichu` text COLLATE utf8mb4_general_ci,
  `ngaychi` date DEFAULT NULL,
  `phuongthuc` int DEFAULT NULL,
  `danhmucchi_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `payment`
--

INSERT INTO `payment` (`id`, `room_id`, `sotien`, `ghichu`, `ngaychi`, `phuongthuc`, `danhmucchi_id`) VALUES
(12, NULL, 250000, 'chi tiền nước', '2024-06-13', 0, 2);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `receipt`
--

CREATE TABLE `receipt` (
  `id` int NOT NULL,
  `room_id` int DEFAULT NULL,
  `sotien` float DEFAULT NULL,
  `ghichu` text COLLATE utf8mb4_general_ci,
  `ngaythu` date DEFAULT NULL,
  `phuongthuc` int DEFAULT NULL,
  `danhmucthu_id` int DEFAULT NULL,
  `bill_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `rental_history`
--

CREATE TABLE `rental_history` (
  `id` int NOT NULL,
  `contract_id` int DEFAULT NULL,
  `room_id` int DEFAULT NULL,
  `tenant_id` int DEFAULT NULL,
  `soluongthanhvien` int DEFAULT NULL,
  `ngaylaphopdong` date DEFAULT NULL,
  `ngayvao` date DEFAULT NULL,
  `ngayra` date DEFAULT NULL,
  `ngaythanhly` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `rental_history`
--

INSERT INTO `rental_history` (`id`, `contract_id`, `room_id`, `tenant_id`, `soluongthanhvien`, `ngaylaphopdong`, `ngayvao`, `ngayra`, `ngaythanhly`) VALUES
(10, 62, 50, NULL, NULL, '2024-05-09', '2024-05-09', '2024-06-12', '2024-06-13');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `room`
--

CREATE TABLE `room` (
  `id` int NOT NULL,
  `image` varchar(300) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tenphong` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dientich` int DEFAULT NULL,
  `giathue` float DEFAULT NULL,
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

INSERT INTO `room` (`id`, `image`, `tenphong`, `dientich`, `giathue`, `tiencoc`, `soluong`, `ngaylaphd`, `chuky`, `ngayvao`, `ngayra`, `trangthai`) VALUES
(50, '/datn/uploads/images/%E1%BA%A3nh%20ph%C3%B2ng/tro3.jpg', 'Phòng 01', 14, 1800000, 900000, 0, 30, 1, '2024-05-09', '2024-06-12', 0),
(56, '/datn/uploads/images/%E1%BA%A3nh%20ph%C3%B2ng/tro1.jpg', 'Phòng 04', 14, 1800000, 900000, 2, 30, 1, '2024-05-12', '2024-09-12', 1),
(57, '/datn/uploads/images/%E1%BA%A3nh%20ph%C3%B2ng/tro10.jpg', 'Phòng 03', 12, 1500000, 700000, 2, 30, 1, '2024-05-25', '2024-10-25', 1),
(58, '/datn/uploads/images/%E1%BA%A3nh%20ph%C3%B2ng/tro5.jpg', 'Phòng 02', 12, 1500000, 700000, 1, 30, 1, '2024-05-20', '2024-10-20', 1),
(59, '/datn/uploads/images/%E1%BA%A3nh%20ph%C3%B2ng/tro9.jpg', 'Phòng 05', 13, 1600000, 800000, 0, 30, 1, '0000-00-00', '0000-00-00', 0),
(76, '/datn/uploads/images/%E1%BA%A3nh%20ph%C3%B2ng/tro6.jpg', 'Phòng 06', 14, 1700000, 700000, 0, 0, 0, '0000-00-00', '0000-00-00', 0),
(77, '/datn/uploads/images/%E1%BA%A3nh%20ph%C3%B2ng/tro11.jpg', 'Phòng 07', 14, 1700000, 700000, 0, 30, 1, NULL, NULL, 0),
(78, '/datn/uploads/images/%E1%BA%A3nh%20ph%C3%B2ng/tro12.jpg', 'Phòng 08', 12, 1500000, 500000, 0, 30, 1, NULL, NULL, 0),
(79, '/datn/uploads/images/%E1%BA%A3nh%20ph%C3%B2ng/tro14.jpg', 'Phòng 09', 14, 1700000, 600000, 0, 30, 1, NULL, NULL, 0),
(80, '/datn/uploads/images/%E1%BA%A3nh%20ph%C3%B2ng/tro15.jpg', 'Phòng 10', 16, 2200000, 800000, 0, 30, 1, NULL, NULL, 0),
(81, '', 'Phòng 11', 20, 2000000, 1000000, 0, 10, 1, NULL, NULL, 0);

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
  `tendichvu` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `donvitinh` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `giadichvu` float DEFAULT NULL,
  `create_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `services`
--

INSERT INTO `services` (`id`, `tendichvu`, `donvitinh`, `giadichvu`, `create_at`) VALUES
(1, 'Tiền điện', 'KWh', 4000, NULL),
(5, 'Tiền nước', 'khoi', 20000, NULL),
(8, 'Tiền rác', 'nguoi', 10000, NULL),
(10, 'Tiền Wifi', 'thang', 50000, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `student`
--

CREATE TABLE `student` (
  `id` int NOT NULL,
  `thumb` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fullname` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `birthday` datetime DEFAULT NULL,
  `sex` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `class_id` int DEFAULT NULL,
  `create_at` datetime DEFAULT NULL,
  `update_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `student`
--

INSERT INTO `student` (`id`, `thumb`, `fullname`, `birthday`, `sex`, `address`, `class_id`, `create_at`, `update_at`) VALUES
(125, '/student_software/uploads/images/avatar2.jpg', 'Ngô Hoàng Nam', '0000-00-00 00:00:00', 'Nam', 'Lê Chân - Hải Phòng', 3, NULL, NULL),
(126, '/student_software/uploads/images/429792649_928615315324031_4079866059838075128_n.jpg', 'Ngô Hoàng Nam', '0000-00-00 00:00:00', 'Nữ', 'Lê Chân - Hải Phòng', 3, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tenant`
--

CREATE TABLE `tenant` (
  `id` int NOT NULL,
  `tenkhach` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sdt` int DEFAULT NULL,
  `ngaysinh` date DEFAULT NULL,
  `gioitinh` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `diachi` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nghenghiep` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cmnd` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ngayvao` date DEFAULT NULL,
  `ngaycap` date DEFAULT NULL,
  `anhmattruoc` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `anhmatsau` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `zalo` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `room_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tenant`
--

INSERT INTO `tenant` (`id`, `tenkhach`, `sdt`, `ngaysinh`, `gioitinh`, `diachi`, `nghenghiep`, `cmnd`, `ngayvao`, `ngaycap`, `anhmattruoc`, `anhmatsau`, `zalo`, `room_id`) VALUES
(47, 'Trịnh Tiến Hoàng', 378646861, '2024-05-23', 'Nam', 'Kênh Dương - Lạch Tray - HP', 'Sinh viên', '031200007165', '2024-05-11', '2024-05-02', '/datn/uploads/images/pre4.png', '/datn/uploads/images/cmndafter.jpg', 'https://www.facebook.com/profile.php?id=100010590132155', 56),
(48, 'Phạm Bảo Ngọc', 365522748, '2001-05-01', 'Nam', 'Quán Nam - Lê Chân - Hải Phòng', 'Sinh viên', '031345678988', '2024-06-01', '2024-05-02', '/datn/uploads/images/minhtuan.jpg', '/datn/uploads/images/cmndafter.jpg', 'https://www.facebook.com/profile.php?id=100004480365007', 57),
(51, 'Nguyễn Phương Loan', 355926306, '2002-04-11', 'Nữ', 'Hoàng Minh Thảo - Hải Phòng', 'Kỹ sư vi mạch', '031374558862', '2024-06-02', '2021-10-20', '/datn/uploads/images/pre10.jpg', '/datn/uploads/images/cmndafter.jpg', '#', 56),
(57, 'Nguyễn Trọng Đức', 378646861, '2024-06-13', 'Nam', 'Quán Nam - Lê Chân - Hải Phòng', 'Sinh viên', '0312000011668', '2024-05-18', '2024-06-11', '/datn/uploads/images/minhtuan.jpg', '/datn/uploads/images/cmndafter.jpg', '', 57),
(58, 'Trần Hồng Ngọc', 465732823, '2024-02-15', 'Nữ', 'Hồng Bàng - Hải Phòng', 'Sinh viên', '099994638763', '2024-06-05', '2023-02-02', '/datn/uploads/images/pre1.jpg', '/datn/uploads/images/cmndafter.jpg', '', 58);

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
  `fullname` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `group_id` int DEFAULT NULL,
  `status` int DEFAULT '0',
  `last_activity` datetime DEFAULT NULL,
  `forget_token` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `room_id` int DEFAULT '0',
  `create_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `group_id`, `status`, `last_activity`, `forget_token`, `room_id`, `create_at`) VALUES
(17, 'Đào Văn Thu', 'daothu3107.dvl@gmail.com', '$2y$10$NhU.DKFPu.4Lf8or.qESNON7RCNQaMmAnjz6rEyxkNN.9fVqiWyaK', 7, 1, NULL, '233da87f2e80a7bb8a5b0f637775b73c2793aaac', NULL, '2024-05-24'),
(25, 'Tiến Hoàng', 'hoang@gmail.com', '$2y$10$DpEdWlfOLCiOdjlVcMbqOeXfxw2c.hFvilewC6gMdB/fIBys/5kBC', 9, 1, NULL, NULL, 50, '2024-06-12'),
(26, 'Nguyễn Diệu Linh', 'dieulinh@gmail.com', '$2y$10$CuQUP.gXsPUnm7wWpKarSuxv5N8uSs7zrfyg1gojdCNMN4904/bJ2', 9, 1, NULL, NULL, 58, '2024-06-13'),
(27, 'Phạm Bảo Ngọc', 'Ngoc87009@st.vimaru.edu.vn', '$2y$10$ywKrfcS2axeD9LrLkjCOlOX/LMNYjP3YlE4PycZZGjCUqrMNbJ5Te', 9, 1, NULL, NULL, 57, '2024-06-13'),
(28, 'Nguyễn Minh Tuấn', 'tuan86351@st.vimaru.edu.vn', '$2y$10$tSkMLRqfSJ6GljF6NXZaR.wEbtH/VmDO2B.eyK9sOGkEUWi2J5UC.', 9, 1, NULL, NULL, 56, '2024-06-13'),
(30, 'Nguyễn Ngọc Nguyên', 'ngocnguyen2k02@gmail.com', '$2y$10$nnhAXtCgHgJSZATqG0/R4O/CLwc.lM4dPEeDURjMo2M5rb99iP9iO', 7, 1, '2024-09-03 13:01:36', '5a2505be899db4882ab8a1d2c98974ba6cbb8070', 76, '2024-10-01'),
(31, NULL, NULL, NULL, NULL, 0, NULL, NULL, 76, NULL);

--
-- Chỉ mục cho các bảng đã đổ
--

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
  ADD KEY `room_id` (`room_id`),
  ADD KEY `tenant_id` (`tenant_id`);

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
  ADD KEY `bill_id` (`bill_id`);

--
-- Chỉ mục cho bảng `rental_history`
--
ALTER TABLE `rental_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contract_id` (`contract_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `tenant_id` (`tenant_id`);

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
-- Chỉ mục cho bảng `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

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
-- AUTO_INCREMENT cho bảng `bill`
--
ALTER TABLE `bill`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT cho bảng `category_collect`
--
ALTER TABLE `category_collect`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `category_spend`
--
ALTER TABLE `category_spend`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `contract`
--
ALTER TABLE `contract`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT cho bảng `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT cho bảng `equipment_room`
--
ALTER TABLE `equipment_room`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT cho bảng `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `login_token`
--
ALTER TABLE `login_token`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=368;

--
-- AUTO_INCREMENT cho bảng `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `receipt`
--
ALTER TABLE `receipt`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT cho bảng `rental_history`
--
ALTER TABLE `rental_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `room`
--
ALTER TABLE `room`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT cho bảng `services`
--
ALTER TABLE `services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT cho bảng `student`
--
ALTER TABLE `student`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT cho bảng `tenant`
--
ALTER TABLE `tenant`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Các ràng buộc cho các bảng đã đổ
--

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
  ADD CONSTRAINT `contract_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `room` (`id`),
  ADD CONSTRAINT `contract_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `tenant` (`id`);

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
  ADD CONSTRAINT `receipt_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `room` (`id`);

--
-- Các ràng buộc cho bảng `rental_history`
--
ALTER TABLE `rental_history`
  ADD CONSTRAINT `rental_history_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `room` (`id`),
  ADD CONSTRAINT `rental_history_ibfk_3` FOREIGN KEY (`tenant_id`) REFERENCES `tenant` (`id`);

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
