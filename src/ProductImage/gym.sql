CREATE DATABASE IF NOT EXISTS gym CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gym;

-- T?o b?ng Location
CREATE TABLE Location (
    location_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    description TEXT
);

-- T?o b?ng Vendors
CREATE TABLE Vendors (
    vendor_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    contact VARCHAR(100),
    address VARCHAR(255)
);

-- T?o b?ng Equipment
CREATE TABLE Equipment (
    equipment_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    type VARCHAR(50),
    brand VARCHAR(50),
    purchase_date DATE,
    warranty_expiry DATE,
    location_id INT,
    vendor_id INT,
    status VARCHAR(20),
    FOREIGN KEY (location_id) REFERENCES Location(location_id),
    FOREIGN KEY (vendor_id) REFERENCES Vendors(vendor_id)
);


INSERT INTO Location (name, description) VALUES
('Khu Cardio', 'Khu v?c t?p luy?n tim m?ch và v?n ??ng toàn thân'),
('Khu C? B?ng', 'Dành cho các d?ng c? h? tr? t?p b?ng'),
('Khu Kh?i ??ng / Dây', 'Dây t?p, dây ?àn h?i, dây nh?y,...'),
('Khu T?', 'D?ng c? t? tay, t? chuông, Kettlebell,...'),
('Khu Xà / Th?ng B?ng', 'Khu v?c có xà ??n, xà kép và thi?t b? h? tr? c? th?');


INSERT INTO Vendors (name, contact, address) VALUES
('TRX Training', 'support@trxtraining.com', 'San Francisco, CA, USA'),
('??i Nam Sport', 'info@dainamsport.vn', 'Hà N?i, Vi?t Nam'),
('AB Carver', 'contact@abcarver.com', 'Los Angeles, CA, USA'),
('GoodFit', 'support@goodfit.vn', 'H? Chí Minh, Vi?t Nam'),
('POPO Fitness', 'sales@popofit.com', 'Thâm Quy?n, Trung Qu?c'),
('BlitzWolf', 'support@blitzwolf.com', 'H?ng Kông'),
('Outtobe', 'info@outtobe.com', 'Th??ng H?i, Trung Qu?c'),
('MDBuddy', 'info@mdbuddy.com', 'Qu?ng Châu, Trung Qu?c'),
('Brosman', 'support@brosman.vn', '?à N?ng, Vi?t Nam'),
('Livepro', 'contact@livepro.cn', 'B?c Kinh, Trung Qu?c'),
('Bowflex', 'support@bowflex.com', 'Vancouver, WA, USA');


INSERT INTO Equipment (name, type, brand, purchase_date, warranty_expiry, location_id, vendor_id, status) VALUES
('B? dây t?p kháng l?c ?a n?ng TRX Suspension Training', 'Dây kháng l?c', 'TRX', '2024-01-15', '2026-01-15', 3, 1, 'Ho?t ??ng'),
('Bóng T? ??i Nam Sport', 'Bóng t?', '??i Nam Sport', '2023-12-10', '2025-12-10', 3, 2, 'Ho?t ??ng'),
('Con l?n t?p b?ng AB Carver Pro', 'Con l?n b?ng', 'AB Carver', '2024-02-20', '2026-02-20', 2, 3, 'Ho?t ??ng'),
('Con l?n t?p b?ng AB Roller có lò xo tr? l?c GoodFit', 'Con l?n b?ng', 'GoodFit', '2024-03-10', '2026-03-10', 2, 4, 'Ho?t ??ng'),
('Dây ?àn h?i th? hình POPO YGT1', 'Dây ?àn h?i', 'POPO', '2023-11-05', '2025-11-05', 3, 5, 'Ho?t ??ng'),
('Dây Nh?y BW-JR1 K? Thu?t S? BlitzWolf', 'Dây nh?y', 'BlitzWolf', '2024-04-01', '2025-04-01', 3, 6, 'Ho?t ??ng'),
('Dây t?p th? d?c Outtobe', 'Dây t?p th? d?c', 'Outtobe', '2024-03-15', '2026-03-15', 3, 7, 'Ho?t ??ng'),
('?ng l?n MDBuddy MDF019', '?ng l?n', 'MDBuddy', '2024-01-25', '2026-01-25', 2, 8, 'Ho?t ??ng'),
('T? chuông Brosman', 'T? chuông', 'Brosman', '2023-10-01', '2025-10-01', 4, 9, 'Ho?t ??ng'),
('T? Kettlebell Livepro LP8043', 'T? Kettlebell', 'Livepro', '2023-09-20', '2025-09-20', 4, 10, 'Ho?t ??ng'),
('T? tay Bowflex SelectTech 552', 'T? tay ?i?u ch?nh', 'Bowflex', '2024-02-01', '2026-02-01', 4, 11, 'Ho?t ??ng'),
('Xà ??n th?ng b?ng GoodFit GF201PU', 'Xà ??n', 'GoodFit', '2024-03-05', '2026-03-05', 5, 4, 'Ho?t ??ng');