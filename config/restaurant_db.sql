-- ตาราง admin
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);
-- ตารางอาหาร
CREATE TABLE foods (
    food_id INT AUTO_INCREMENT PRIMARY KEY,
    food_name VARCHAR(100),
    price FLOAT,
    discount FLOAT DEFAULT 0,
    status ENUM('available','out') DEFAULT 'available'
);
    ALTER TABLE foods 
    ADD COLUMN image VARCHAR(255) AFTER price;
-- เพิ่ม admin เริ่มต้น
INSERT INTO admins (username, password)
VALUES ('admin', MD5('1234'));
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    total DECIMAL(10,2),
    status VARCHAR(20) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    food_id INT,
    qty INT,
    note TEXT,
    price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
);
ALTER TABLE orders 
ADD COLUMN slip_image VARCHAR(255) AFTER status;
