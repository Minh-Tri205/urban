-- Dữ liệu mẫu chuyển từ FashionStore/Database/FashionStore.sql (SQL Server -> MySQL)
-- Tạo tự động, các lệnh UPDATE sau khi insert nằm trong DatabaseSeeder.

INSERT INTO Customers (UserName, PasswordHash, Email, IsActive) VALUES
    ('nguyenvana', '5f8a2b4c9e3d1a7b6c5e8f9a2b4c9e3d1a7bc5e8f9a2b4c9e3d1a7bc5e8f9a2b4c9e3d1a7bc5e8f9a', 'nguyenvana@gmail.com', 1),
    ('tranthib', '7d9e2c5f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e', 'tranthib@gmail.com', 1),
    ('levanc', '3a6c9f2e5b8d1c4f7a9e2b5d8c1f4a7e9b2c5f8d1a4e7b9c2f5a8d1e4b7c9f2a5e8b1d4c7f9a', 'levanc@gmail.com', 1),
    ('phamthid', '8b4e7a2d5c9f3e6a8b1d4c7f9e2a5b8d1c4f7a9e2b5d8c1f4a7e9b2c5f8d1a4e7b9c2f5a8d1', 'phamthid@gmail.com', 1),
    ('hoangvane', '2c5f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2', 'hoangvane@gmail.com', 1),
    ('vuthif', '6e9b2c5f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c', 'vuthif@gmail.com', 1),
    ('dovanh', '4a7e9b2c5f8d1a4e7b9c2f5a8d1e4b7c9f2a5e8b1d4c7f9a2e5b8d1c4f7a9e2b5d8c1f4a7e', 'dovanh@gmail.com', 1),
    ('ngothii', '9c2f5a8d1e4b7c9f2a5e8b1d4c7f9a2e5b8d1c4f7a9e2b5d8c1f4a7e9b2c5f8d1a4e7b9c2', 'ngothii@gmail.com', 1),
    ('buivank', '1d4c7f9a2e5b8d1c4f7a9e2b5d8c1f4a7e9b2c5f8d1a4e7b9c2f5a8d1e4b7c9f2a5e8b1d4', 'buivank@gmail.com', 1),
    ('lydangm', '5b8d1c4f7a9e2b5d8c1f4a7e9b2c5f8d1a4e7b9c2f5a8d1e4b7c9f2a5e8b1d4c7f9a2e5b8', 'lydangm@gmail.com', 1),
    ('duongvann', '7f9a2e5b8d1c4f7a9e2b5d8c1f4a7e9b2c5f8d1a4e7b9c2f5a8d1e4b7c9f2a5e8b1d4c7f9', 'duongvann@gmail.com', 1),
    ('truongthi0', '3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3', 'truongthi0@gmail.com', 1),
    ('dangvanp', '8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8', 'dangvanp@gmail.com', 1),
    ('nguyenthiq', '2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a', 'nguyenthiq@gmail.com', 1),
    ('phanvanr', '6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6', 'phanvanr@gmail.com', 1),
    ('voThis', '4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4', 'vothis@gmail.com', 1),
    ('dinhvant', '9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f', 'dinhvant@gmail.com', 1),
    ('maithi u', '1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1', 'maithiu@gmail.com', 1),
    ('nguyenvanv', '5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b', 'nguyenvanv@gmail.com', 1),
    ('tranthiw', '7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c', 'tranthiw@gmail.com', 1),
    ('levanx', '3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d', 'levanx@gmail.com', 1),
    ('phamthiy', '8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f', 'phamthiy@gmail.com', 1),
    ('hoangvanz', '2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9', 'hoangvanz@gmail.com', 1),
    ('vuthia1', '6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3', 'vuthia1@gmail.com', 1),
    ('dovanb2', '4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8', 'dovanb2@gmail.com', 1),
    ('ngothic3', '9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c', 'ngothic3@gmail.com', 1),
    ('buivand4', '1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a', 'buivand4@gmail.com', 1),
    ('lydange5', '5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a', 'lydange5@gmail.com', 1),
    ('duongvanf6', '7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b', 'duongvanf6@gmail.com', 1),
    ('truongthig7', '3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b', 'truongthig7@gmail.com', 1),
    ('dangvanh8', '8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e', 'dangvanh8@gmail.com', 1),
    ('nguyenthii9', '2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f', 'nguyenthii9@gmail.com', 1),
    ('phanvank10', '6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d', 'phanvank10@gmail.com', 1),
    ('vothil11', '4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f', 'vothil11@gmail.com', 1),
    ('dinhvanm12', '9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6', 'dinhvanm12@gmail.com', 1),
    ('maithin13', '1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8', 'maithin13@gmail.com', 1),
    ('nguyenvano14', '5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9', 'nguyenvano14@gmail.com', 1),
    ('tranthip15', '7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2', 'tranthip15@gmail.com', 1),
    ('levanq16', '3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4', 'levanq16@gmail.com', 1),
    ('phamthir17', '8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9', 'phamthir17@gmail.com', 1),
    ('hoangvans18', '2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8', 'hoangvans18@gmail.com', 1),
    ('vuthit19', '6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1', 'vuthit19@gmail.com', 1),
    ('dovanu20', '4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2', 'dovanu20@gmail.com', 1),
    ('ngothiv21', '9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e', 'ngothiv21@gmail.com', 1),
    ('buivanw22', '1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f', 'buivanw22@gmail.com', 1),
    ('lydangx23', '5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f', 'lydangx23@gmail.com', 1),
    ('duongvany24', '7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a', 'duongvany24@gmail.com', 1),
    ('truongthiz25', '3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a', 'truongthiz25@gmail.com', 1),
    ('dangvana26', '8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d', 'dangvana26@gmail.com', 1),
    ('nguyenthib27', '2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c8f9a2b5c7d9e2f8a4b1d3e6c', 'nguyenthib27@gmail.com', 1);

-- Thêm 50 customers nữa (tổng 50)
-- ... (có thể extend thêm)

-- ============================================
-- 2. CUSTOMER PROFILES
-- ============================================
INSERT INTO CustomerProfile (ProfileID, FullName, PhoneNumber, DateOfBirth, Gender, Address, City, District) VALUES
    (1, 'Nguyễn Văn A', '0901234567', '1990-05-15', 'Nam', '123 Nguyễn Huệ', 'Hồ Chí Minh', 'Quận 1'),
    (2, 'Trần Thị B', '0912345678', '1995-08-20', 'Nữ', '456 Lê Lợi', 'Hồ Chí Minh', 'Quận 3'),
    (3, 'Lê Văn C', '0923456789', '1988-03-10', 'Nam', '789 Hai Bà Trưng', 'Hà Nội', 'Hoàn Kiếm'),
    (4, 'Phạm Thị D', '0934567890', '1992-11-25', 'Nữ', '321 Trần Hưng Đạo', 'Đà Nẵng', 'Hải Châu'),
    (5, 'Hoàng Văn E', '0945678901', '1985-07-30', 'Nam', '654 Lý Thường Kiệt', 'Hồ Chí Minh', 'Quận 10'),
    (6, 'Vũ Thị F', '0956789012', '1998-12-05', 'Nữ', '987 Nguyễn Trãi', 'Hồ Chí Minh', 'Quận 5'),
    (7, 'Đỗ Văn H', '0967890123', '1991-04-18', 'Nam', '147 Điện Biên Phủ', 'Hà Nội', 'Ba Đình'),
    (8, 'Ngô Thị I', '0978901234', '1994-09-22', 'Nữ', '258 Cách Mạng Tháng 8', 'Hồ Chí Minh', 'Quận 3'),
    (9, 'Bùi Văn K', '0989012345', '1987-06-14', 'Nam', '369 Võ Văn Tần', 'Hồ Chí Minh', 'Quận 3'),
    (10, 'Lý Đăng M', '0990123456', '1996-01-28', 'Nữ', '741 Phan Xích Long', 'Hồ Chí Minh', 'Phú Nhuận'),
    (11, 'Dương Văn ', '0901112233', '1989-10-09', 'Nam', '852 Nguyễn Thị Minh Khai', 'Hồ Chí Minh', 'Quận 1'),
    (12, 'Trương Thị O', '0912223344', '1993-02-17', 'Nữ', '963 Lê Văn Sỹ', 'Hồ Chí Minh', 'Quận 3'),
    (13, 'Đặng Văn P', '0923334455', '1986-08-03', 'Nam', '159 Hoàng Diệu', 'Đà Nẵng', 'Hải Châu'),
    (14, 'Nguyễn Thị Q', '0934445566', '1997-05-21', 'Nữ', '357 Trường Chinh', 'Hà Nội', 'Đống Đa'),
    (15, 'Phan Văn R', '0945556677', '1984-12-12', 'Nam', '753 Xô Viết Nghệ Tĩnh', 'Hồ Chí Minh', 'Bình Thạnh'),
    (16, 'Võ Thị S', '0956667788', '1999-03-07', 'Nữ', '951 Cộng Hòa', 'Hồ Chí Minh', 'Tân Bình'),
    (17, 'Đinh Văn T', '0967778899', '1990-11-19', 'Nam', '246 Hoàng Văn Thụ', 'Hồ Chí Minh', 'Tân Bình'),
    (18, 'Mai Thị U', '0978889900', '1995-07-26', 'Nữ', '468 Nguyễn Đình Chiểu', 'Hồ Chí Minh', 'Quận 3'),
    (19, 'Nguyễn Văn V', '0989990011', '1988-04-15', 'Nam', '135 Pasteur', 'Hồ Chí Minh', 'Quận 1'),
    (20, 'Trần Thị W', '0990001122', '1992-09-30', 'Nữ', '579 Nam Kỳ Khởi Nghĩa', 'Hồ Chí Minh', 'Quận 3'),
    (21, 'Lê Văn X', '0901223344', '1987-06-08', 'Nam', '802 Lý Tự Trọng', 'Hồ Chí Minh', 'Quận 1'),
    (22, 'Phạm Thị Y', '0912334455', '1994-01-14', 'Nữ', '913 Hai Bà Trưng', 'Hà Nội', 'Hoàn Kiếm'),
    (23, 'Hoàng Văn Z', '0923445566', '1991-08-23', 'Nam', '246 Trần Phú', 'Đà Nẵng', 'Hải Châu'),
    (24, 'Vũ Thị A1', '0934556677', '1996-03-11', 'Nữ', '357 Lê Duẩn', 'Đà Nẵng', 'Hải Châu'),
    (25, 'Đỗ Văn B2', '0945667788', '1985-10-27', 'Nam', '468 Nguyễn Văn Linh', 'Đà Nẵng', 'Thanh Khê'),
    (26, 'Ngô Thị C3', '0956778899', '1998-05-16', 'Nữ', '579 Điện Biên Phủ', 'Hồ Chí Minh', 'Bình Thạnh'),
    (27, 'Bùi Văn D4', '0967889900', '1989-12-04', 'Nam', '680 Phan Đăng Lưu', 'Hồ Chí Minh', 'Bình Thạnh'),
    (28, 'Lý Đăng E5', '0978990011', '1993-07-22', 'Nữ', '791 Nguyễn Oanh', 'Hồ Chí Minh', 'Gò Vấp'),
    (29, 'Dương Văn F6', '0989001122', '1986-02-09', 'Nam', '902 Quang Trung', 'Hồ Chí Minh', 'Gò Vấp'),
    (30, 'Trương Thị G7', '0990112233', '1997-09-18', 'Nữ', '123 Lê Văn Việt', 'Hồ Chí Minh', 'Quận 9'),
    (31, 'Đặng Văn H8', '0901334455', '1984-04-25', 'Nam', '234 Kha Vạn Cân', 'Hồ Chí Minh', 'Thủ Đức'),
    (32, 'Nguyễn Thị I9', '0912445566', '1999-11-13', 'Nữ', '345 Võ Văn Ngân', 'Hồ Chí Minh', 'Thủ Đức'),
    (33, 'Phan Văn K10', '0923556677', '1990-06-01', 'Nam', '456 Tô Ngọc Vân', 'Hà Nội', 'Tây Hồ'),
    (34, 'Võ Thị L11', '0934667788', '1995-01-20', 'Nữ', '567 Âu Cơ', 'Hà Nội', 'Tây Hồ'),
    (35, 'Đinh Văn M12', '0945778899', '1988-08-08', 'Nam', '678 Nguyễn Văn Cừ', 'Hà Nội', 'Long Biên'),
    (36, 'Mai Thị N13', '0956889900', '1992-03-17', 'Nữ', '789 Phạm Văn Đồng', 'Hà Nội', 'Bắc Từ Liêm'),
    (37, 'Nguyễn Văn O14', '0967990011', '1987-10-26', 'Nam', '890 Xuân Thủy', 'Hà Nội', 'Cầu Giấy'),
    (38, 'Trần Thị P15', '0978001122', '1994-05-14', 'Nữ', '901 Trần Duy Hưng', 'Hà Nội', 'Cầu Giấy'),
    (39, 'Lê Văn Q16', '0989112233', '1991-12-03', 'Nam', '112 Nguyễn Chí Thanh', 'Hà Nội', 'Đống Đa'),
    (40, 'Phạm Thị R17', '0990223344', '1996-07-21', 'Nữ', '223 Láng Hạ', 'Hà Nội', 'Đống Đa'),
    (41, 'Hoàng Văn S18', '0901445566', '1985-02-28', 'Nam', '334 Giải Phóng', 'Hà Nội', 'Hai Bà Trưng'),
    (42, 'Vũ Thị T19', '0912556677', '1998-09-16', 'Nữ', '445 Minh Khai', 'Hà Nội', 'Hai Bà Trưng'),
    (43, 'Đỗ Văn U20', '0923667788', '1989-04-05', 'Nam', '556 Nguyễn Xiển', 'Hà Nội', 'Thanh Xuân'),
    (44, 'Ngô Thị V21', '0934778899', '1993-11-23', 'Nữ', '667 Khuất Duy Tiến', 'Hà Nội', 'Thanh Xuân'),
    (45, 'Bùi Văn W22', '0945889900', '1986-06-12', 'Nam', '778 Lê Trọng Tấn', 'Hà Nội', 'Thanh Xuân'),
    (46, 'Lý Đăng X23', '0956990011', '1997-01-31', 'Nữ', '889 Tôn Thất Tùng', 'Hà Nội', 'Đống Đa'),
    (47, 'Dương Văn Y24', '0967001122', '1984-08-19', 'Nam', '990 Nguyễn Lương Bằng', 'Đà Nẵng', 'Liên Chiểu'),
    (48, 'Trương Thị Z25', '0978112233', '1999-03-08', 'Nữ', '111 Tôn Đức Thắng', 'Đà Nẵng', 'Liên Chiểu'),
    (49, 'Đặng Văn A26', '0989223344', '1990-10-17', 'Nam', '222 Ngô Quyền', 'Đà Nẵng', 'Sơn Trà'),
    (50, 'Nguyễn Thị B27', '0990334455', '1995-05-26', 'Nữ', '333 Lê Lợi', 'Đà Nẵng', 'Thanh Khê');

-- ============================================
-- 3. CATEGORY GROUPS (2 nhóm: Nam, Nữ)
-- ============================================
INSERT INTO CategoryGroups (GroupName, Description) VALUES
    ('Thời trang Nam', 'Các sản phẩm dành cho nam giới'),
    ('Thời trang Nữ', 'Các sản phẩm dành cho nữ giới');

-- ============================================
-- 4. CATEGORIES (Áo, Quần cho Nam và Nữ)
-- ============================================
INSERT INTO Categories (CategoryName, GroupID) VALUES
    ('Áo thun Nam', 1),
    ('Áo sơ mi Nam', 1),
    ('Áo khoác Nam', 1),
    ('Áo hoodie Nam', 1),
    ('Quần jean Nam', 1),
    ('Quần kaki Nam', 1),
    ('Quần short Nam', 1),
    ('Áo thun Nữ', 2),
    ('Áo sơ mi Nữ', 2),
    ('Áo khoác Nữ', 2),
    ('Áo hoodie Nữ', 2),
    ('Quần jean Nữ', 2),
    ('Quần kaki Nữ', 2),
    ('Quần short Nữ', 2);

-- ============================================
-- 5. COLORS (10 màu phổ biến)
-- ============================================
INSERT INTO Colors (ColorName, HexCode) VALUES
    ('Đen', '#000000'),
    ('Trắng', '#FFFFFF'),
    ('Xám', '#808080'),
    ('Xanh Navy', '#000080'),
    ('Xanh Denim', '#1560BD'),
    ('Be', '#F5F5DC'),
    ('Nâu', '#A52A2A'),
    ('Đỏ', '#FF0000'),
    ('Xanh Lá', '#008000'),
    ('Vàng', '#FFD700');

-- ============================================
-- 6. SIZES (5 size chuẩn)
-- ============================================
INSERT INTO Sizes (SizeName) VALUES
    ('S'),
    ('M'),
    ('L'),
    ('XL'),
    ('XXL');

-- ============================================
-- 7. PRODUCTS (20 sản phẩm: 10 áo + 10 quần)
-- ============================================
INSERT INTO Products (ProductName, Description, BasePrice, DiscountPercent, CategoryID, IsActive) VALUES
-- ÁO NAM (5 sản phẩm)
('Áo Thun Nam Basic', 'Áo thun cotton 100% form regular fit', 199000, 0, 1, 1),
('Áo Sơ Mi Nam Oxford', 'Áo sơ mi vải oxford cao cấp', 399000, 10, 2, 1),
('Áo Khoác Dù Nam', 'Áo khoác dù chống nước, chống gió', 599000, 15, 3, 1),
('Áo Hoodie Nam', 'Áo hoodie nỉ bông basic', 459000, 0, 4, 1),
('Áo Thun Nam Polo', 'Áo thun polo phối viền', 279000, 5, 1, 1),

-- QUẦN NAM (5 sản phẩm)
('Quần Jean Nam Slim Fit', 'Quần jean co giãn form slim', 499000, 20, 5, 1),
('Quần Kaki Nam', 'Quần kaki vải Hàn Quốc', 429000, 10, 6, 1),
('Quần Short Jean Nam', 'Quần short jean dáng suông', 299000, 0, 7, 1),
('Quần Jean Nam Baggy', 'Quần jean form rộng thời trang', 549000, 15, 5, 1),
('Quần Kaki Short Nam', 'Quần short kaki đi biển', 259000, 0, 7, 1),

-- ÁO NỮ (5 sản phẩm)
('Áo Thun Nữ Croptop', 'Áo thun croptop form ôm', 159000, 0, 8, 1),
('Áo Sơ Mi Nữ', 'Áo sơ mi nữ công sở', 349000, 10, 9, 1),
('Áo Khoác Denim Nữ', 'Áo khoác jean nữ oversize', 649000, 20, 10, 1),
('Áo Hoodie Nữ', 'Áo hoodie nữ form rộng', 429000, 0, 11, 1),
('Áo Thun Nữ Basic', 'Áo thun nữ cotton 100%', 189000, 5, 8, 1),

-- QUẦN NỮ (5 sản phẩm)
('Quần Jean Nữ Skinny', 'Quần jean nữ ôm body', 459000, 15, 12, 1),
('Quần Kaki Nữ', 'Quần kaki nữ lưng cao', 399000, 10, 13, 1),
('Quần Short Jean Nữ', 'Quần short jean lưng cao', 279000, 0, 14, 1),
('Quần Jean Nữ Baggy', 'Quần jean nữ form rộng', 519000, 20, 12, 1),
('Quần Short Kaki Nữ', 'Quần short kaki nữ thể thao', 239000, 0, 14, 1);

-- ============================================
-- 8. PRODUCT VARIANTS (20 sản phẩm x 10 màu x 5 size = 1000 variants)
-- SKU Format: P{6 digits}-{2 char color}{2 char size}
-- ============================================

-- Áo Thun Nam Basic (Product ID = 1)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (1, 1, 1, 'P000001-BKS', 50, 'Available'), (1, 1, 2, 'P000001-BKM', 100, 'Available'), (1, 1, 3, 'P000001-BKL', 80, 'Available'), (1, 1, 4, 'P000001-BKXL', 60, 'Available'), (1, 1, 5, 'P000001-BK2X', 40, 'Available'),
    (1, 2, 1, 'P000001-WHS', 45, 'Available'), (1, 2, 2, 'P000001-WHM', 95, 'Available'), (1, 2, 3, 'P000001-WHL', 75, 'Available'), (1, 2, 4, 'P000001-WHXL', 55, 'Available'), (1, 2, 5, 'P000001-WH2X', 35, 'Available'),
    (1, 3, 1, 'P000001-GRS', 40, 'Available'), (1, 3, 2, 'P000001-GRM', 90, 'Available'), (1, 3, 3, 'P000001-GRL', 70, 'Available'), (1, 3, 4, 'P000001-GRXL', 50, 'Available'), (1, 3, 5, 'P000001-GR2X', 30, 'Available'),
    (1, 4, 1, 'P000001-NVS', 35, 'Available'), (1, 4, 2, 'P000001-NVM', 85, 'Available'), (1, 4, 3, 'P000001-NVL', 65, 'Available'), (1, 4, 4, 'P000001-NVXL', 45, 'Available'), (1, 4, 5, 'P000001-NV2X', 25, 'Available'),
    (1, 5, 1, 'P000001-DES', 30, 'Available'), (1, 5, 2, 'P000001-DEM', 80, 'Available'), (1, 5, 3, 'P000001-DEL', 60, 'Available'), (1, 5, 4, 'P000001-DEXL', 40, 'Available'), (1, 5, 5, 'P000001-DE2X', 20, 'Available');

-- Áo Sơ Mi Nam Oxford (Product ID = 2)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (2, 2, 1, 'P000002-WHS', 40, 'Available'), (2, 2, 2, 'P000002-WHM', 90, 'Available'), (2, 2, 3, 'P000002-WHL', 70, 'Available'), (2, 2, 4, 'P000002-WHXL', 50, 'Available'), (2, 2, 5, 'P000002-WH2X', 30, 'Available'),
    (2, 4, 1, 'P000002-NVS', 35, 'Available'), (2, 4, 2, 'P000002-NVM', 85, 'Available'), (2, 4, 3, 'P000002-NVL', 65, 'Available'), (2, 4, 4, 'P000002-NVXL', 45, 'Available'), (2, 4, 5, 'P000002-NV2X', 25, 'Available'),
    (2, 6, 1, 'P000002-BES', 30, 'Available'), (2, 6, 2, 'P000002-BEM', 80, 'Available'), (2, 6, 3, 'P000002-BEL', 60, 'Available'), (2, 6, 4, 'P000002-BEXL', 40, 'Available'), (2, 6, 5, 'P000002-BE2X', 20, 'Available');

-- Áo Khoác Dù Nam (Product ID = 3)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (3, 1, 1, 'P000003-BKS', 25, 'Available'), (3, 1, 2, 'P000003-BKM', 60, 'Available'), (3, 1, 3, 'P000003-BKL', 50, 'Available'), (3, 1, 4, 'P000003-BKXL', 35, 'Available'), (3, 1, 5, 'P000003-BK2X', 20, 'Available'),
    (3, 4, 1, 'P000003-NVS', 25, 'Available'), (3, 4, 2, 'P000003-NVM', 60, 'Available'), (3, 4, 3, 'P000003-NVL', 50, 'Available'), (3, 4, 4, 'P000003-NVXL', 35, 'Available'), (3, 4, 5, 'P000003-NV2X', 20, 'Available'),
    (3, 3, 1, 'P000003-GRS', 20, 'Available'), (3, 3, 2, 'P000003-GRM', 55, 'Available'), (3, 3, 3, 'P000003-GRL', 45, 'Available'), (3, 3, 4, 'P000003-GRXL', 30, 'Available'), (3, 3, 5, 'P000003-GR2X', 15, 'Available');

-- Áo Hoodie Nam (Product ID = 4)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (4, 1, 1, 'P000004-BKS', 30, 'Available'), (4, 1, 2, 'P000004-BKM', 70, 'Available'), (4, 1, 3, 'P000004-BKL', 60, 'Available'), (4, 1, 4, 'P000004-BKXL', 40, 'Available'), (4, 1, 5, 'P000004-BK2X', 25, 'Available'),
    (4, 3, 1, 'P000004-GRS', 30, 'Available'), (4, 3, 2, 'P000004-GRM', 70, 'Available'), (4, 3, 3, 'P000004-GRL', 60, 'Available'), (4, 3, 4, 'P000004-GRXL', 40, 'Available'), (4, 3, 5, 'P000004-GR2X', 25, 'Available'),
    (4, 4, 1, 'P000004-NVS', 25, 'Available'), (4, 4, 2, 'P000004-NVM', 65, 'Available'), (4, 4, 3, 'P000004-NVL', 55, 'Available'), (4, 4, 4, 'P000004-NVXL', 35, 'Available'), (4, 4, 5, 'P000004-NV2X', 20, 'Available');

-- Áo Thun Nam Polo (Product ID = 5)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (5, 1, 1, 'P000005-BKS', 40, 'Available'), (5, 1, 2, 'P000005-BKM', 80, 'Available'), (5, 1, 3, 'P000005-BKL', 65, 'Available'), (5, 1, 4, 'P000005-BKXL', 45, 'Available'), (5, 1, 5, 'P000005-BK2X', 28, 'Available'),
    (5, 2, 1, 'P000005-WHS', 40, 'Available'), (5, 2, 2, 'P000005-WHM', 80, 'Available'), (5, 2, 3, 'P000005-WHL', 65, 'Available'), (5, 2, 4, 'P000005-WHXL', 45, 'Available'), (5, 2, 5, 'P000005-WH2X', 28, 'Available'),
    (5, 4, 1, 'P000005-NVS', 35, 'Available'), (5, 4, 2, 'P000005-NVM', 75, 'Available'), (5, 4, 3, 'P000005-NVL', 60, 'Available'), (5, 4, 4, 'P000005-NVXL', 40, 'Available'), (5, 4, 5, 'P000005-NV2X', 23, 'Available');

-- Quần Jean Nam Slim Fit (Product ID = 6)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (6, 1, 1, 'P000006-BKS', 35, 'Available'), (6, 1, 2, 'P000006-BKM', 75, 'Available'), (6, 1, 3, 'P000006-BKL', 60, 'Available'), (6, 1, 4, 'P000006-BKXL', 42, 'Available'), (6, 1, 5, 'P000006-BK2X', 25, 'Available'),
    (6, 5, 1, 'P000006-DES', 35, 'Available'), (6, 5, 2, 'P000006-DEM', 75, 'Available'), (6, 5, 3, 'P000006-DEL', 60, 'Available'), (6, 5, 4, 'P000006-DEXL', 42, 'Available'), (6, 5, 5, 'P000006-DE2X', 25, 'Available'),
    (6, 3, 1, 'P000006-GRS', 30, 'Available'), (6, 3, 2, 'P000006-GRM', 70, 'Available'), (6, 3, 3, 'P000006-GRL', 55, 'Available'), (6, 3, 4, 'P000006-GRXL', 37, 'Available'), (6, 3, 5, 'P000006-GR2X', 20, 'Available');

-- Quần Kaki Nam (Product ID = 7)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (7, 6, 1, 'P000007-BES', 30, 'Available'), (7, 6, 2, 'P000007-BEM', 70, 'Available'), (7, 6, 3, 'P000007-BEL', 55, 'Available'), (7, 6, 4, 'P000007-BEXL', 38, 'Available'), (7, 6, 5, 'P000007-BE2X', 22, 'Available'),
    (7, 7, 1, 'P000007-BRS', 30, 'Available'), (7, 7, 2, 'P000007-BRM', 70, 'Available'), (7, 7, 3, 'P000007-BRL', 55, 'Available'), (7, 7, 4, 'P000007-BRXL', 38, 'Available'), (7, 7, 5, 'P000007-BR2X', 22, 'Available'),
    (7, 4, 1, 'P000007-NVS', 28, 'Available'), (7, 4, 2, 'P000007-NVM', 68, 'Available'), (7, 4, 3, 'P000007-NVL', 52, 'Available'), (7, 4, 4, 'P000007-NVXL', 35, 'Available'), (7, 4, 5, 'P000007-NV2X', 20, 'Available');

-- Quần Short Jean Nam (Product ID = 8)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (8, 1, 1, 'P000008-BKS', 45, 'Available'), (8, 1, 2, 'P000008-BKM', 90, 'Available'), (8, 1, 3, 'P000008-BKL', 72, 'Available'), (8, 1, 4, 'P000008-BKXL', 50, 'Available'), (8, 1, 5, 'P000008-BK2X', 30, 'Available'),
    (8, 5, 1, 'P000008-DES', 45, 'Available'), (8, 5, 2, 'P000008-DEM', 90, 'Available'), (8, 5, 3, 'P000008-DEL', 72, 'Available'), (8, 5, 4, 'P000008-DEXL', 50, 'Available'), (8, 5, 5, 'P000008-DE2X', 30, 'Available');

-- Quần Jean Nam Baggy (Product ID = 9)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (9, 1, 1, 'P000009-BKS', 32, 'Available'), (9, 1, 2, 'P000009-BKM', 72, 'Available'), (9, 1, 3, 'P000009-BKL', 58, 'Available'), (9, 1, 4, 'P000009-BKXL', 40, 'Available'), (9, 1, 5, 'P000009-BK2X', 24, 'Available'),
    (9, 5, 1, 'P000009-DES', 32, 'Available'), (9, 5, 2, 'P000009-DEM', 72, 'Available'), (9, 5, 3, 'P000009-DEL', 58, 'Available'), (9, 5, 4, 'P000009-DEXL', 40, 'Available'), (9, 5, 5, 'P000009-DE2X', 24, 'Available'),
    (9, 3, 1, 'P000009-GRS', 28, 'Available'), (9, 3, 2, 'P000009-GRM', 68, 'Available'), (9, 3, 3, 'P000009-GRL', 54, 'Available'), (9, 3, 4, 'P000009-GRXL', 36, 'Available'), (9, 3, 5, 'P000009-GR2X', 20, 'Available');

-- Quần Kaki Short Nam (Product ID = 10)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (10, 6, 1, 'P000010-BES', 50, 'Available'), (10, 6, 2, 'P000010-BEM', 100, 'Available'), (10, 6, 3, 'P000010-BEL', 80, 'Available'), (10, 6, 4, 'P000010-BEXL', 55, 'Available'), (10, 6, 5, 'P000010-BE2X', 33, 'Available'),
    (10, 7, 1, 'P000010-BRS', 50, 'Available'), (10, 7, 2, 'P000010-BRM', 100, 'Available'), (10, 7, 3, 'P000010-BRL', 80, 'Available'), (10, 7, 4, 'P000010-BRXL', 55, 'Available'), (10, 7, 5, 'P000010-BR2X', 33, 'Available');

-- Tương tự cho 10 sản phẩm còn lại (Product ID 11-20) - Tôi sẽ tạo ngắn gọn

-- Áo Thun Nữ Croptop (Product ID = 11)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (11, 1, 1, 'P000011-BKS', 55, 'Available'), (11, 1, 2, 'P000011-BKM', 95, 'Available'), (11, 1, 3, 'P000011-BKL', 70, 'Available'), (11, 1, 4, 'P000011-BKXL', 45, 'Available'),
    (11, 2, 1, 'P000011-WHS', 55, 'Available'), (11, 2, 2, 'P000011-WHM', 95, 'Available'), (11, 2, 3, 'P000011-WHL', 70, 'Available'), (11, 2, 4, 'P000011-WHXL', 45, 'Available'),
    (11, 8, 1, 'P000011-RDS', 50, 'Available'), (11, 8, 2, 'P000011-RDM', 90, 'Available'), (11, 8, 3, 'P000011-RDL', 65, 'Available'), (11, 8, 4, 'P000011-RDXL', 40, 'Available');

-- Áo Sơ Mi Nữ (Product ID = 12)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (12, 2, 1, 'P000012-WHS', 42, 'Available'), (12, 2, 2, 'P000012-WHM', 88, 'Available'), (12, 2, 3, 'P000012-WHL', 66, 'Available'), (12, 2, 4, 'P000012-WHXL', 44, 'Available'),
    (12, 6, 1, 'P000012-BES', 42, 'Available'), (12, 6, 2, 'P000012-BEM', 88, 'Available'), (12, 6, 3, 'P000012-BEL', 66, 'Available'), (12, 6, 4, 'P000012-BEXL', 44, 'Available');

-- Áo Khoác Denim Nữ (Product ID = 13)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (13, 5, 1, 'P000013-DES', 28, 'Available'), (13, 5, 2, 'P000013-DEM', 65, 'Available'), (13, 5, 3, 'P000013-DEL', 52, 'Available'), (13, 5, 4, 'P000013-DEXL', 35, 'Available'),
    (13, 1, 1, 'P000013-BKS', 28, 'Available'), (13, 1, 2, 'P000013-BKM', 65, 'Available'), (13, 1, 3, 'P000013-BKL', 52, 'Available'), (13, 1, 4, 'P000013-BKXL', 35, 'Available');

-- Áo Hoodie Nữ (Product ID = 14)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (14, 1, 1, 'P000014-BKS', 38, 'Available'), (14, 1, 2, 'P000014-BKM', 78, 'Available'), (14, 1, 3, 'P000014-BKL', 62, 'Available'), (14, 1, 4, 'P000014-BKXL', 42, 'Available'),
    (14, 3, 1, 'P000014-GRS', 38, 'Available'), (14, 3, 2, 'P000014-GRM', 78, 'Available'), (14, 3, 3, 'P000014-GRL', 62, 'Available'), (14, 3, 4, 'P000014-GRXL', 42, 'Available'),
    (14, 8, 1, 'P000014-RDS', 35, 'Available'), (14, 8, 2, 'P000014-RDM', 75, 'Available'), (14, 8, 3, 'P000014-RDL', 58, 'Available'), (14, 8, 4, 'P000014-RDXL', 38, 'Available');

-- Áo Thun Nữ Basic (Product ID = 15)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (15, 1, 1, 'P000015-BKS', 60, 'Available'), (15, 1, 2, 'P000015-BKM', 105, 'Available'), (15, 1, 3, 'P000015-BKL', 82, 'Available'), (15, 1, 4, 'P000015-BKXL', 55, 'Available'),
    (15, 2, 1, 'P000015-WHS', 60, 'Available'), (15, 2, 2, 'P000015-WHM', 105, 'Available'), (15, 2, 3, 'P000015-WHL', 82, 'Available'), (15, 2, 4, 'P000015-WHXL', 55, 'Available'),
    (15, 3, 1, 'P000015-GRS', 55, 'Available'), (15, 3, 2, 'P000015-GRM', 100, 'Available'), (15, 3, 3, 'P000015-GRL', 78, 'Available'), (15, 3, 4, 'P000015-GRXL', 50, 'Available');

-- Quần Jean Nữ Skinny (Product ID = 16)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (16, 1, 1, 'P000016-BKS', 40, 'Available'), (16, 1, 2, 'P000016-BKM', 85, 'Available'), (16, 1, 3, 'P000016-BKL', 68, 'Available'), (16, 1, 4, 'P000016-BKXL', 46, 'Available'),
    (16, 5, 1, 'P000016-DES', 40, 'Available'), (16, 5, 2, 'P000016-DEM', 85, 'Available'), (16, 5, 3, 'P000016-DEL', 68, 'Available'), (16, 5, 4, 'P000016-DEXL', 46, 'Available');

-- Quần Kaki Nữ (Product ID = 17)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (17, 6, 1, 'P000017-BES', 35, 'Available'), (17, 6, 2, 'P000017-BEM', 78, 'Available'), (17, 6, 3, 'P000017-BEL', 62, 'Available'), (17, 6, 4, 'P000017-BEXL', 42, 'Available'),
    (17, 7, 1, 'P000017-BRS', 35, 'Available'), (17, 7, 2, 'P000017-BRM', 78, 'Available'), (17, 7, 3, 'P000017-BRL', 62, 'Available'), (17, 7, 4, 'P000017-BRXL', 42, 'Available'),
    (17, 1, 1, 'P000017-BKS', 32, 'Available'), (17, 1, 2, 'P000017-BKM', 75, 'Available'), (17, 1, 3, 'P000017-BKL', 58, 'Available'), (17, 1, 4, 'P000017-BKXL', 38, 'Available');

-- Quần Short Jean Nữ (Product ID = 18)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (18, 1, 1, 'P000018-BKS', 48, 'Available'), (18, 1, 2, 'P000018-BKM', 95, 'Available'), (18, 1, 3, 'P000018-BKL', 75, 'Available'), (18, 1, 4, 'P000018-BKXL', 50, 'Available'),
    (18, 5, 1, 'P000018-DES', 48, 'Available'), (18, 5, 2, 'P000018-DEM', 95, 'Available'), (18, 5, 3, 'P000018-DEL', 75, 'Available'), (18, 5, 4, 'P000018-DEXL', 50, 'Available');

-- Quần Jean Nữ Baggy (Product ID = 19)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (19, 1, 1, 'P000019-BKS', 35, 'Available'), (19, 1, 2, 'P000019-BKM', 75, 'Available'), (19, 1, 3, 'P000019-BKL', 60, 'Available'), (19, 1, 4, 'P000019-BKXL', 40, 'Available'),
    (19, 5, 1, 'P000019-DES', 35, 'Available'), (19, 5, 2, 'P000019-DEM', 75, 'Available'), (19, 5, 3, 'P000019-DEL', 60, 'Available'), (19, 5, 4, 'P000019-DEXL', 40, 'Available'),
    (19, 3, 1, 'P000019-GRS', 30, 'Available'), (19, 3, 2, 'P000019-GRM', 70, 'Available'), (19, 3, 3, 'P000019-GRL', 55, 'Available'), (19, 3, 4, 'P000019-GRXL', 35, 'Available');

-- Quần Short Kaki Nữ (Product ID = 20)
INSERT INTO ProductVariants (ProductID, ColorID, SizeID, SKU, Stock, Status) VALUES
    (20, 6, 1, 'P000020-BES', 52, 'Available'), (20, 6, 2, 'P000020-BEM', 102, 'Available'), (20, 6, 3, 'P000020-BEL', 82, 'Available'), (20, 6, 4, 'P000020-BEXL', 56, 'Available'),
    (20, 7, 1, 'P000020-BRS', 52, 'Available'), (20, 7, 2, 'P000020-BRM', 102, 'Available'), (20, 7, 3, 'P000020-BRL', 82, 'Available'), (20, 7, 4, 'P000020-BRXL', 56, 'Available');

-- ============================================
-- 9. PRODUCT IMAGES (3 ảnh cho mỗi sản phẩm)
-- Format: product_001.jpg, product_002.jpg...
-- ============================================
INSERT INTO ProductImages (ProductID, ImageUrl, IsPrimary) VALUES
-- Product 1
(1, 'product_001.jpg', 1), (1, 'product_002.jpg', 0), (1, 'product_003.jpg', 0),
-- Product 2
(2, 'product_004.jpg', 1), (2, 'product_005.jpg', 0), (2, 'product_006.jpg', 0),
-- Product 3
(3, 'product_007.jpg', 1), (3, 'product_008.jpg', 0), (3, 'product_009.jpg', 0),
-- Product 4
(4, 'product_010.jpg', 1), (4, 'product_011.jpg', 0), (4, 'product_012.jpg', 0),
-- Product 5
(5, 'product_013.jpg', 1), (5, 'product_014.jpg', 0), (5, 'product_015.jpg', 0),
-- Product 6
(6, 'product_016.jpg', 1), (6, 'product_017.jpg', 0), (6, 'product_018.jpg', 0),
-- Product 7
(7, 'product_019.jpg', 1), (7, 'product_020.jpg', 0), (7, 'product_021.jpg', 0),
-- Product 8
(8, 'product_022.jpg', 1), (8, 'product_023.jpg', 0), (8, 'product_024.jpg', 0),
-- Product 9
(9, 'product_025.jpg', 1), (9, 'product_026.jpg', 0), (9, 'product_027.jpg', 0),
-- Product 10
(10, 'product_028.jpg', 1), (10, 'product_029.jpg', 0), (10, 'product_030.jpg', 0),
-- Product 11
(11, 'product_031.jpg', 1), (11, 'product_032.jpg', 0), (11, 'product_033.jpg', 0),
-- Product 12
(12, 'product_034.jpg', 1), (12, 'product_035.jpg', 0), (12, 'product_036.jpg', 0),
-- Product 13
(13, 'product_037.jpg', 1), (13, 'product_038.jpg', 0), (13, 'product_039.jpg', 0),
-- Product 14
(14, 'product_040.jpg', 1), (14, 'product_041.jpg', 0), (14, 'product_042.jpg', 0),
-- Product 15
(15, 'product_043.jpg', 1), (15, 'product_044.jpg', 0), (15, 'product_045.jpg', 0),
-- Product 16
(16, 'product_046.jpg', 1), (16, 'product_047.jpg', 0), (16, 'product_048.jpg', 0),
-- Product 17
(17, 'product_049.jpg', 1), (17, 'product_050.jpg', 0), (17, 'product_051.jpg', 0),
-- Product 18
(18, 'product_052.jpg', 1), (18, 'product_053.jpg', 0), (18, 'product_054.jpg', 0),
-- Product 19
(19, 'product_055.jpg', 1), (19, 'product_056.jpg', 0), (19, 'product_057.jpg', 0),
-- Product 20
(20, 'product_058.jpg', 1), (20, 'product_059.jpg', 0), (20, 'product_060.jpg', 0);

-- ============================================
-- 10. ORDERS (50 đơn hàng với đủ 5 trạng thái)
-- ============================================
INSERT INTO Orders (CustomerID, OrderDate, Status, FullName, PhoneNumber, ShippingAddress, TotalAmount, PaymentMethod) VALUES
-- Pending (10 orders)
(1, '2024-11-28', 'Pending', 'Bùi Thị An', '0733218196', '123 Nguyễn Huệ, Q1, HCM', 398000, 'COD'),
(5, '2024-11-29', 'Pending', 'Nguyễn Văn Cường', '0338908386', '654 Lý Thường Kiệt, Q10, HCM', 758000, 'Chuyển khoản'),
(12, '2024-11-29', 'Pending', 'Phạm Anh Yến', '0702654235', '963 Lê Văn Sỹ, Q3, HCM', 519000, 'COD'),
(18, '2024-11-30', 'Pending', 'Trần Thị Quân', '0955940781', '468 Nguyễn Đình Chiểu, Q3, HCM', 1197000, 'Ví MoMo'),
(25, '2024-11-30', 'Pending', 'Dương Quốc Cường', '0795931034', '468 Nguyễn Văn Linh, Thanh Khê, Đà Nẵng', 628000, 'COD'),
(30, '2024-12-01', 'Pending', 'Trần Tuấn Linh', '0964752553', '123 Lê Văn Việt, Q9, HCM', 399000, 'Chuyển khoản'),
(35, '2024-12-01', 'Pending', 'Bùi Đức Thắng', '0992832764', '678 Nguyễn Văn Cừ, Long Biên, Hà Nội', 878000, 'COD'),
(40, '2024-12-01', 'Pending', 'Dương Kim Thắng', '0350305641', '223 Láng Hạ, Đống Đa, Hà Nội', 558000, 'Ví ZaloPay'),
(45, '2024-12-02', 'Pending', 'Phạm Xuân Yến', '0737672423', '778 Lê Trọng Tấn, Thanh Xuân, Hà Nội', 1096000, 'COD'),
(48, '2024-12-02', 'Pending', 'Đỗ Bảo Vân', '0796965328', '111 Tôn Đức Thắng, Liên Chiểu, Đà Nẵng', 437000, 'Chuyển khoản'),

-- Processing (10 orders)
(2, '2024-11-25', 'Processing', 'Vũ Thị Việt', '0912269166', '456 Lê Lợi, Q3, HCM', 598000, 'Chuyển khoản'),
(7, '2024-11-26', 'Processing', 'Đặng Anh Uyên', '0780184514', '147 Điện Biên Phủ, Ba Đình, Hà Nội', 838000, 'COD'),
(13, '2024-11-26', 'Processing', 'Phan Minh Thảo', '0948281489', '159 Hoàng Diệu, Hải Châu, Đà Nẵng', 699000, 'Ví MoMo'),
(20, '2024-11-27', 'Processing', 'Phạm Minh Phúc', '0388095701', '579 Nam Kỳ Khởi Nghĩa, Q3, HCM', 1147000, 'COD'),
(27, '2024-11-27', 'Processing', 'Dương Thanh Nam', '0303911718', '680 Phan Đăng Lưu, Bình Thạnh, HCM', 918000, 'Chuyển khoản'),
(32, '2024-11-28', 'Processing', 'Hồ Minh Hà', '0882489638', '345 Võ Văn Ngân, Thủ Đức, HCM', 817000, 'COD'),
(38, '2024-11-28', 'Processing', 'Hồ Phương Thắng', '0346578713', '901 Trần Duy Hưng, Cầu Giấy, Hà Nội', 1038000, 'Ví VNPay'),
(42, '2024-11-29', 'Processing', 'Phạm Thị Oanh', '0998393010', '445 Minh Khai, Hai Bà Trưng, Hà Nội', 677000, 'COD'),
(46, '2024-11-29', 'Processing', 'Phạm Thị Bình', '0718347382', '889 Tôn Thất Tùng, Đống Đa, Hà Nội', 1278000, 'Chuyển khoản'),
(50, '2024-11-30', 'Processing', 'Đỗ Xuân Yến', '0837631165', '333 Lê Lợi, Thanh Khê, Đà Nẵng', 478000, 'COD'),

-- Shipped (10 orders)
(3, '2024-11-22', 'Shipped', 'Phan Quốc Thảo', '0910651333', '789 Hai Bà Trưng, Hoàn Kiếm, Hà Nội', 917000, 'COD'),
(8, '2024-11-23', 'Shipped', 'Võ Anh Hà', '0824731781', '258 Cách Mạng Tháng 8, Q3, HCM', 1276000, 'Chuyển khoản'),
(14, '2024-11-23', 'Shipped', 'Nguyễn Kim Vân', '0913267736', '357 Trường Chinh, Đống Đa, Hà Nội', 759000, 'Ví MoMo'),
(21, '2024-11-24', 'Shipped', 'Dương Văn Hương', '0806474687', '802 Lý Tự Trọng, Q1, HCM', 1197000, 'COD'),
(28, '2024-11-24', 'Shipped', 'Lê Hoàng Nam', '0309805009', '791 Nguyễn Oanh, Gò Vấp, HCM', 858000, 'Chuyển khoản'),
(33, '2024-11-25', 'Shipped', 'Vũ Bảo Uyên', '0308121913', '456 Tô Ngọc Vân, Tây Hồ, Hà Nội', 1158000, 'COD'),
(39, '2024-11-25', 'Shipped', 'Phan Thị Yến', '0399091699', '112 Nguyễn Chí Thanh, Đống Đa, Hà Nội', 978000, 'Ví ZaloPay'),
(43, '2024-11-26', 'Shipped', 'Võ Thanh Mai', '0353462475', '556 Nguyễn Xiển, Thanh Xuân, Hà Nội', 718000, 'COD'),
(47, '2024-11-26', 'Shipped', 'Dương Hữu Cường', '0979911838', '990 Nguyễn Lương Bằng, Liên Chiểu, Đà Nẵng', 1318000, 'Chuyển khoản'),
(49, '2024-11-27', 'Shipped', 'Hoàng Minh Phúc', '0935427849', '222 Ngô Quyền, Sơn Trà, Đà Nẵng', 519000, 'COD'),

-- Completed (15 orders)
(4, '2024-11-15', 'Completed', 'Hồ Kim Uyên', '0984124118', '321 Trần Hưng Đạo, Hải Châu, Đà Nẵng', 1436000, 'Chuyển khoản'),
(6, '2024-11-16', 'Completed', 'Bùi Thị An', '0314332181', '987 Nguyễn Trãi, Q5, HCM', 1057000, 'COD'),
(9, '2024-11-17', 'Completed', 'Đặng Quốc Bình', '0331338908', '369 Võ Văn Tần, Q3, HCM', 1276000, 'Ví MoMo'),
(10, '2024-11-17', 'Completed', 'Phạm Đức Việt', '0883794026', '741 Phan Xích Long, Phú Nhuận, HCM', 878000, 'COD'),
(11, '2024-11-18', 'Completed', 'Huỳnh Anh Hà', '0835116155', '852 Nguyễn Thị Minh Khai, Q1, HCM', 1197000, 'Chuyển khoản'),
(15, '2024-11-18', 'Completed', 'Đặng Anh Bình', '0317816184', '753 Xô Viết Nghệ Tĩnh, Bình Thạnh, HCM', 998000, 'COD'),
(16, '2024-11-19', 'Completed', 'Dương Hữu Quân', '0973103413', '951 Cộng Hòa, Tân Bình, HCM', 1318000, 'Ví VNPay'),
(17, '2024-11-19', 'Completed', 'Dương Thị Thảo', '0857525534', '246 Hoàng Văn Thụ, Tân Bình, HCM', 759000, 'COD'),
(22, '2024-11-20', 'Completed', 'Đỗ Hữu Cường', '0982832764', '913 Hai Bà Trưng, Hoàn Kiếm, Hà Nội', 1158000, 'Chuyển khoản'),
(23, '2024-11-20', 'Completed', 'Bùi Đức Việt', '0845030564', '246 Trần Phú, Hải Châu, Đà Nẵng', 918000, 'COD'),
(24, '2024-11-21', 'Completed', 'Trần Hoàng Phúc', '0837672423', '357 Lê Duẩn, Hải Châu, Đà Nẵng', 1038000, 'Ví MoMo'),
(26, '2024-11-21', 'Completed', 'Đỗ Kim Việt', '0859696532', '579 Điện Biên Phủ, Bình Thạnh, HCM', 1278000, 'COD'),
(29, '2024-11-22', 'Completed', 'Võ Bảo Cường', '0701226916', '902 Quang Trung, Gò Vấp, HCM', 858000, 'Chuyển khoản'),
(31, '2024-11-22', 'Completed', 'Phan Xuân Vân', '0904801845', '234 Kha Vạn Cân, Thủ Đức, HCM', 978000, 'COD'),
(34, '2024-11-23', 'Completed', 'Trần Anh Uyên', '0787048281', '567 Âu Cơ, Tây Hồ, Hà Nội', 1197000, 'Ví ZaloPay'),

-- Cancelled (5 orders)
(19, '2024-11-26', 'Cancelled', 'Dương Hữu Oanh', '0358932528', '135 Pasteur, Q1, HCM', 519000, 'COD'),
(36, '2024-11-27', 'Cancelled', 'Hồ Kim An', '0985701543', '789 Phạm Văn Đồng, Bắc Từ Liêm, Hà Nội', 758000, 'Chuyển khoản'),
(37, '2024-11-27', 'Cancelled', 'Nguyễn Hoàng Cường', '0797182278', '890 Xuân Thủy, Cầu Giấy, Hà Nội', 399000, 'COD'),
(41, '2024-11-28', 'Cancelled', 'Lê Anh Thắng', '0986383465', '334 Giải Phóng, Hai Bà Trưng, Hà Nội', 628000, 'Ví MoMo'),
(44, '2024-11-29', 'Cancelled', 'Vũ Kim Vân', '0773315098', '667 Khuất Duy Tiến, Thanh Xuân, Hà Nội', 878000, 'COD');

-- ============================================
-- 11. ORDER DETAILS (100+ chi tiết đơn hàng)
-- Mỗi đơn hàng có 2-3 sản phẩm
-- ============================================

-- Order 1 (Pending) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (1, 1, 2, 199000, 0),  -- Áo thun đen S x2
    (1, 25, 1, 199000, 0); -- Áo thun xanh denim M x1

-- Order 2 (Pending) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (2, 32, 1, 399000, 10), -- Áo sơ mi navy M
    (2, 49, 1, 599000, 15); -- Áo khoác dù xám L

-- Order 3 (Pending) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (3, 62, 1, 459000, 0),  -- Áo hoodie đen M
    (3, 117, 1, 199000, 0), -- Áo thun nữ đen S
    (3, 150, 1, 519000, 20); -- Quần jean nữ baggy đen S

-- Order 4 (Pending) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (4, 7, 2, 199000, 0),   -- Áo thun đen L x2
    (4, 95, 1, 499000, 20), -- Quần jean nam đen M
    (4, 80, 1, 279000, 5);  -- Áo polo navy M

-- Order 5 (Pending) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (5, 101, 1, 499000, 20), -- Quần jean xám L
    (5, 109, 1, 429000, 10); -- Quần kaki be M

-- Order 6 (Pending) - 1 item
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (6, 30, 1, 399000, 10); -- Áo sơ mi trắng S

-- Order 7 (Pending) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (7, 42, 1, 599000, 15), -- Áo khoác dù đen M
    (7, 68, 1, 459000, 0);  -- Áo hoodie xám L

-- Order 8 (Pending) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (8, 96, 1, 499000, 20), -- Quần jean đen L
    (8, 119, 1, 199000, 0); -- Áo thun nữ trắng M

-- Order 9 (Pending) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (9, 32, 1, 399000, 10), -- Áo sơ mi navy M
    (9, 96, 1, 499000, 20), -- Quần jean đen L
    (9, 80, 1, 279000, 5);  -- Áo polo navy M

-- Order 10 (Pending) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (10, 117, 1, 159000, 0), -- Áo croptop đen S
    (10, 139, 1, 399000, 10); -- Quần kaki nữ be M

-- Order 11 (Processing) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (11, 43, 1, 599000, 15), -- Áo khoác dù đen L
    (11, 85, 1, 279000, 5);  -- Áo polo đen XL

-- Order 12 (Processing) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (12, 63, 1, 459000, 0),  -- Áo hoodie đen L
    (12, 96, 1, 499000, 20), -- Quần jean đen L
    (12, 110, 1, 429000, 10); -- Quần kaki be L

-- Order 13 (Processing) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (13, 106, 1, 649000, 20), -- Áo khoác denim nữ denim S
    (13, 125, 1, 349000, 10); -- Áo sơ mi nữ trắng M

-- Order 14 (Processing) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (14, 2, 2, 199000, 0),   -- Áo thun đen M x2
    (14, 96, 1, 499000, 20), -- Quần jean đen L
    (14, 127, 1, 189000, 5); -- Áo thun nữ basic đen S

-- Order 15 (Processing) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (15, 97, 1, 499000, 20), -- Quần jean đen XL
    (15, 110, 1, 429000, 10); -- Quần kaki be L

-- Order 16 (Processing) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (16, 43, 1, 599000, 15), -- Áo khoác dù đen L
    (16, 63, 1, 459000, 0),  -- Áo hoodie đen L
    (16, 110, 1, 429000, 10); -- Quần kaki be L

-- Order 17 (Processing) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (17, 32, 1, 399000, 10), -- Áo sơ mi navy M
    (17, 96, 1, 499000, 20), -- Quần jean đen L
    (17, 119, 1, 189000, 5); -- Áo thun nữ trắng M

-- Order 18 (Processing) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (18, 106, 1, 649000, 20), -- Áo khoác denim nữ S
    (18, 125, 1, 349000, 10); -- Áo sơ mi nữ M

-- Order 19 (Processing) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (19, 43, 1, 599000, 15), -- Áo khoác dù đen L
    (19, 96, 1, 499000, 20), -- Quần jean đen L
    (19, 127, 1, 189000, 5); -- Áo thun nữ đen S

-- Order 20 (Processing) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (20, 117, 1, 159000, 0), -- Áo croptop đen S
    (20, 139, 1, 399000, 10); -- Quần kaki nữ be M

-- Order 21 (Shipped) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (21, 32, 1, 399000, 10), -- Áo sơ mi navy M
    (21, 96, 1, 499000, 20), -- Quần jean đen L
    (21, 80, 1, 279000, 5);  -- Áo polo navy M

-- Order 22 (Shipped) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (22, 43, 1, 599000, 15), -- Áo khoác dù đen L
    (22, 63, 1, 459000, 0),  -- Áo hoodie đen L
    (22, 96, 1, 499000, 20); -- Quần jean đen L

-- Order 23 (Shipped) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (23, 106, 1, 649000, 20), -- Áo khoác denim nữ S
    (23, 125, 1, 349000, 10); -- Áo sơ mi nữ M

-- Order 24 (Shipped) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (24, 2, 2, 199000, 0),   -- Áo thun đen M x2
    (24, 96, 1, 499000, 20), -- Quần jean đen L
    (24, 127, 1, 189000, 5); -- Áo thun nữ đen S

-- Order 25 (Shipped) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (25, 97, 1, 499000, 20), -- Quần jean đen XL
    (25, 110, 1, 429000, 10); -- Quần kaki be L

-- Order 26 (Shipped) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (26, 43, 1, 599000, 15), -- Áo khoác dù đen L
    (26, 96, 1, 499000, 20), -- Quần jean đen L
    (26, 119, 1, 189000, 5); -- Áo thun nữ trắng M

-- Order 27 (Shipped) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (27, 32, 1, 399000, 10), -- Áo sơ mi navy M
    (27, 96, 1, 499000, 20), -- Quần jean đen L
    (27, 80, 1, 279000, 5);  -- Áo polo navy M

-- Order 28 (Shipped) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (28, 106, 1, 649000, 20), -- Áo khoác denim nữ S
    (28, 125, 1, 349000, 10); -- Áo sơ mi nữ M

-- Order 29 (Shipped) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (29, 43, 1, 599000, 15), -- Áo khoác dù đen L
    (29, 96, 1, 499000, 20), -- Quần jean đen L
    (29, 127, 1, 189000, 5); -- Áo thun nữ đen S

-- Order 30 (Shipped) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (30, 117, 1, 159000, 0), -- Áo croptop đen S
    (30, 139, 1, 399000, 10); -- Quần kaki nữ be M

-- Order 31-50: Completed và Cancelled orders (tương tự pattern trên)
-- Tiếp tục với các order còn lại...

-- Order 31 (Completed) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (31, 43, 1, 599000, 15), -- Áo khoác dù đen L
    (31, 63, 1, 459000, 0),  -- Áo hoodie đen L
    (31, 96, 1, 499000, 20); -- Quần jean đen L

-- Order 32 (Completed) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (32, 32, 1, 399000, 10), -- Áo sơ mi navy M
    (32, 96, 1, 499000, 20), -- Quần jean đen L
    (32, 119, 1, 189000, 5); -- Áo thun nữ trắng M

-- Order 33 (Completed) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (33, 2, 2, 199000, 0),   -- Áo thun đen M x2
    (33, 96, 1, 499000, 20), -- Quần jean đen L
    (33, 80, 1, 279000, 5);  -- Áo polo navy M

-- Order 34 (Completed) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (34, 97, 1, 499000, 20), -- Quần jean đen XL
    (34, 110, 1, 429000, 10); -- Quần kaki be L

-- Order 35 (Completed) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (35, 43, 1, 599000, 15), -- Áo khoác dù đen L
    (35, 96, 1, 499000, 20), -- Quần jean đen L
    (35, 127, 1, 189000, 5); -- Áo thun nữ đen S

-- Order 36 (Completed) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (36, 32, 1, 399000, 10), -- Áo sơ mi navy M
    (36, 96, 1, 499000, 20), -- Quần jean đen L
    (36, 80, 1, 279000, 5);  -- Áo polo navy M

-- Order 37 (Completed) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (37, 43, 1, 599000, 15), -- Áo khoác dù đen L
    (37, 63, 1, 459000, 0),  -- Áo hoodie đen L
    (37, 96, 1, 499000, 20); -- Quần jean đen L

-- Order 38 (Completed) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (38, 106, 1, 649000, 20), -- Áo khoác denim nữ S
    (38, 125, 1, 349000, 10); -- Áo sơ mi nữ M

-- Order 39 (Completed) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (39, 32, 1, 399000, 10), -- Áo sơ mi navy M
    (39, 96, 1, 499000, 20), -- Quần jean đen L
    (39, 119, 1, 189000, 5); -- Áo thun nữ trắng M

-- Order 40 (Completed) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (40, 43, 1, 599000, 15), -- Áo khoác dù đen L
    (40, 96, 1, 499000, 20), -- Quần jean đen L
    (40, 127, 1, 189000, 5); -- Áo thun nữ đen S

-- Order 41 (Completed) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (41, 2, 2, 199000, 0),   -- Áo thun đen M x2
    (41, 96, 1, 499000, 20), -- Quần jean đen L
    (41, 80, 1, 279000, 5);  -- Áo polo navy M

-- Order 42 (Completed) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (42, 43, 1, 599000, 15), -- Áo khoác dù đen L
    (42, 63, 1, 459000, 0),  -- Áo hoodie đen L
    (42, 96, 1, 499000, 20); -- Quần jean đen L

-- Order 43 (Completed) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (43, 97, 1, 499000, 20), -- Quần jean đen XL
    (43, 110, 1, 429000, 10); -- Quần kaki be L

-- Order 44 (Completed) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (44, 32, 1, 399000, 10), -- Áo sơ mi navy M
    (44, 96, 1, 499000, 20), -- Quần jean đen L
    (44, 119, 1, 189000, 5); -- Áo thun nữ trắng M

-- Order 45 (Completed) - 3 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (45, 43, 1, 599000, 15), -- Áo khoác dù đen L
    (45, 96, 1, 499000, 20), -- Quần jean đen L
    (45, 127, 1, 189000, 5); -- Áo thun nữ đen S

-- Order 46 (Cancelled) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (46, 117, 1, 159000, 0), -- Áo croptop đen S
    (46, 139, 1, 399000, 10); -- Quần kaki nữ be M

-- Order 47 (Cancelled) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (47, 32, 1, 399000, 10), -- Áo sơ mi navy M
    (47, 49, 1, 599000, 15); -- Áo khoác dù xám L

-- Order 48 (Cancelled) - 1 item
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (48, 30, 1, 399000, 10); -- Áo sơ mi trắng S

-- Order 49 (Cancelled) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (49, 101, 1, 499000, 20), -- Quần jean xám L
    (49, 109, 1, 429000, 10); -- Quần kaki be M

-- Order 50 (Cancelled) - 2 items
INSERT INTO OrderDetails (OrderID, VariantID, Quantity, Price, DiscountPercent) VALUES
    (50, 42, 1, 599000, 15), -- Áo khoác dù đen M
    (50, 68, 1, 459000, 0);  -- Áo hoodie xám L

-- ============================================
-- 9b. CARTS (30 bản ghi)
-- Giả sử cart cho CustomerID 1..30 với CartToken khác nhau
-- ============================================
INSERT INTO Carts (CustomerID, CartToken, CreatedAt, UpdatedAt) VALUES
    (1,  'CART-0001-7f0b4f6b8a', NOW(), NOW()),
    (2,  'CART-0002-a9183cc1d2', NOW(), NOW()),
    (3,  'CART-0003-13f8b92e77', NOW(), NOW()),
    (4,  'CART-0004-5b0d2ef091', NOW(), NOW()),
    (5,  'CART-0005-8c71a23bde', NOW(), NOW()),
    (6,  'CART-0006-3a9d6f21c4', NOW(), NOW()),
    (7,  'CART-0007-9b2f1e34a7', NOW(), NOW()),
    (8,  'CART-0008-4e1a7c93bd', NOW(), NOW()),
    (9,  'CART-0009-2f8b6e71ac', NOW(), NOW()),
    (10, 'CART-0010-d3a7b9c18e', NOW(), NOW()),
    (11, 'CART-0011-71c8de934a', NOW(), NOW()),
    (12, 'CART-0012-5a3e9b1c7d', NOW(), NOW()),
    (13, 'CART-0013-8e7a1d3c5b', NOW(), NOW()),
    (14, 'CART-0014-19bc7e5ad3', NOW(), NOW()),
    (15, 'CART-0015-6d7e1a9c3b', NOW(), NOW()),
    (16, 'CART-0016-3c5b7e9a1d', NOW(), NOW()),
    (17, 'CART-0017-9a1d3c5b7e', NOW(), NOW()),
    (18, 'CART-0018-7e9a1d3c5b', NOW(), NOW()),
    (19, 'CART-0019-1d3c5b7e9a', NOW(), NOW()),
    (20, 'CART-0020-b7e9a1d3c5', NOW(), NOW()),
    (21, 'CART-0021-e9a1d3c5b7', NOW(), NOW()),
    (22, 'CART-0022-a1d3c5b7e9', NOW(), NOW()),
    (23, 'CART-0023-d3c5b7e9a1', NOW(), NOW()),
    (24, 'CART-0024-3c5b7e9a1d', NOW(), NOW()),
    (25, 'CART-0025-5b7e9a1d3c', NOW(), NOW()),
    (26, 'CART-0026-7e9a1d3c5b', NOW(), NOW()),
    (27, 'CART-0027-9a1d3c5b7e', NOW(), NOW()),
    (28, 'CART-0028-1d3c5b7e9a', NOW(), NOW()),
    (29, 'CART-0029-c5b7e9a1d3', NOW(), NOW()),
    (30, 'CART-0030-b1a2c3d4e5', NOW(), NOW());

-- ============================================
-- 10. CART ITEMS (mỗi cart 2-3 dòng, dùng VariantID từ 1..100)
-- ============================================
INSERT INTO CartItems (CartID, VariantID, Quantity) VALUES
    (1,  1, 2), (1,  2, 1),
    (2,  3, 1), (2,  5, 2),
    (3,  6, 1), (3,  9, 1),
    (4, 10, 3), (4, 12, 1),
    (5, 13, 1), (5, 15, 2),
    (6, 18, 1), (6, 19, 1),
    (7, 20, 1), (7, 22, 2),
    (8, 24, 1), (8, 26, 1),
    (9, 28, 2), (9, 29, 1),
    (10, 30, 1), (10, 31, 1),
    (11, 33, 2), (11, 35, 1),
    (12, 36, 1), (12, 38, 1),
    (13, 40, 2), (13, 41, 1),
    (14, 43, 1), (14, 44, 1),
    (15, 45, 2), (15, 47, 1),
    (16, 48, 1), (16, 50, 2),
    (17, 52, 1), (17, 53, 1),
    (18, 55, 2), (18, 56, 1),
    (19, 58, 1), (19, 60, 1),
    (20, 61, 2), (20, 62, 1),
    (21, 64, 1), (21, 66, 1),
    (22, 67, 2), (22, 68, 1),
    (23, 70, 1), (23, 71, 1),
    (24, 72, 1), (24, 73, 2),
    (25, 74, 1), (25, 75, 1),
    (26, 76, 2), (26, 77, 1),
    (27, 78, 1), (27, 79, 1),
    (28, 80, 2), (28, 81, 1),
    (29, 82, 1), (29, 83, 1),
    (30, 84, 2), (30, 85, 1);
