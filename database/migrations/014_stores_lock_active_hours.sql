-- Add lock flag and active hours to stores
ALTER TABLE stores
  ADD COLUMN locked TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN active_hours_start TIME NULL,
  ADD COLUMN active_hours_end TIME NULL;

