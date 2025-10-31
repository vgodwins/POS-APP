-- Extend messages table to support replies and customer recipients
ALTER TABLE messages
  ADD COLUMN recipient_customer_id INT NULL AFTER recipient_id,
  ADD COLUMN parent_id INT NULL AFTER recipient_customer_id;

-- Foreign key to customers for customer-targeted messages
ALTER TABLE messages
  ADD CONSTRAINT fk_messages_customer
  FOREIGN KEY (recipient_customer_id) REFERENCES customers(id)
  ON DELETE SET NULL;

-- Self-referencing foreign key for threaded replies
ALTER TABLE messages
  ADD CONSTRAINT fk_messages_parent
  FOREIGN KEY (parent_id) REFERENCES messages(id)
  ON DELETE SET NULL;

-- Helpful indexes for querying threads and customer messages
CREATE INDEX idx_messages_parent ON messages(parent_id);
CREATE INDEX idx_messages_customer_recipient ON messages(recipient_customer_id);

