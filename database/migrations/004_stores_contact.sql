-- Extend stores with contact fields and branding
ALTER TABLE stores ADD COLUMN address VARCHAR(255) NULL;
ALTER TABLE stores ADD COLUMN phone VARCHAR(30) NULL;
ALTER TABLE stores ADD COLUMN logo_url VARCHAR(255) NULL;