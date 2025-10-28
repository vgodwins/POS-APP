-- Add customer_id to vouchers for customer linkage
ALTER TABLE vouchers
  ADD COLUMN customer_id INT NULL AFTER currency_code;

ALTER TABLE vouchers
  ADD CONSTRAINT fk_vouchers_customer
  FOREIGN KEY (customer_id) REFERENCES customers(id)
  ON DELETE SET NULL;

CREATE INDEX idx_vouchers_customer ON vouchers(customer_id);

