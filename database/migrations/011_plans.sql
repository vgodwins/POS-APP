-- Plans table for subscriptions (store-level and app-level)
CREATE TABLE IF NOT EXISTS plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) UNIQUE NOT NULL,
  name VARCHAR(120) NOT NULL,
  level ENUM('store','app') NOT NULL,
  period ENUM('monthly','yearly') NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  currency_code VARCHAR(3) NOT NULL DEFAULT 'NGN',
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default plans (amounts are illustrative; adjust as needed)
INSERT IGNORE INTO plans(code,name,level,period,amount,currency_code) VALUES
('store_monthly_basic','Store Monthly','store','monthly',3000,'NGN'),
('store_yearly_basic','Store Yearly','store','yearly',30000,'NGN'),
('app_monthly_basic','App Monthly','app','monthly',5000,'NGN'),
('app_yearly_basic','App Yearly','app','yearly',50000,'NGN');

