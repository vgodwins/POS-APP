-- Subscriptions table: records of user/store subscriptions via gateways
CREATE TABLE IF NOT EXISTS subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  store_id INT NULL,
  plan_code VARCHAR(50) NOT NULL,
  level ENUM('store','app') NOT NULL,
  period ENUM('monthly','yearly') NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  currency_code VARCHAR(3) NOT NULL DEFAULT 'NGN',
  gateway VARCHAR(50) NOT NULL,
  reference VARCHAR(100) NOT NULL,
  status ENUM('pending','active','failed','canceled') NOT NULL DEFAULT 'pending',
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_subscriptions_reference (reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

