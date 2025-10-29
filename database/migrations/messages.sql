-- Messages table for internal communications
CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NULL,
  sender_id INT NOT NULL,
  recipient_id INT NULL, -- null means broadcast to store
  subject VARCHAR(255) NULL,
  body TEXT NOT NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'unread', -- unread|read
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE SET NULL,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE SET NULL
);
