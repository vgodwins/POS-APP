-- Add cost price and status to products
ALTER TABLE products
  ADD COLUMN cost_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  ADD COLUMN status ENUM('valid','expired','damaged','returned') NOT NULL DEFAULT 'valid';

