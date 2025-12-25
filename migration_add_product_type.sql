-- Migration script to add product_type column to existing orders table
-- Run this if you already have the orders table created

ALTER TABLE orders 
  DROP FOREIGN KEY IF EXISTS orders_ibfk_2,
  ADD COLUMN product_type VARCHAR(20) DEFAULT 'product' AFTER product_id;

-- Note: Removed the foreign key constraint on product_id since it can reference either products or merch table
