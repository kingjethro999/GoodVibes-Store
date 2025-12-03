USE goodvibes_db;

-- Table for Users
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('super_admin', 'user') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Super Admin Account
INSERT INTO users (username, email, password, role)
VALUES (
  'Admin',
  'admin@goodvibes.com',
  MD5('admin123'), -- default password (you can change later)
  'super_admin'
);

-- Table for Products
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_name VARCHAR(255) NOT NULL,
  category VARCHAR(100),
  description TEXT,
  price DECIMAL(10,2),
  image VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Example Product
INSERT INTO products (product_name, category, description, price, image)
VALUES
('Vibe Street Hoodie', 'Fashion', 'Premium streetwear hoodie with artistic design.', 14500, 'uploads/hoodie.jpg'),
('Good Energy Tee', 'T-Shirt', 'Minimal design, high comfort cotton tee.', 8500, 'uploads/tshirt.jpg');

-- Table for Orders (optional but useful)
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  product_id INT,
  quantity INT DEFAULT 1,
  total_price DECIMAL(10,2),
  shipping_address TEXT,
  shipping_phone VARCHAR(100),
  shipping_email VARCHAR(100),
  shipping_name VARCHAR(100),
  shipping_phone2 VARCHAR(100),
  shipping_email2 VARCHAR(100),
  shipping_name2 VARCHAR(100),
  order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pending','completed','cancelled') DEFAULT 'pending',
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Table for Feedback / Contact Messages
CREATE TABLE feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(150),
  message TEXT,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

  -- table for the reciepts
CREATE TABLE reciepts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT,
  receipt_image VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id)
);