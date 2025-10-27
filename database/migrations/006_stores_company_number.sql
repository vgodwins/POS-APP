-- Add company number to stores for receipt branding
ALTER TABLE stores
  ADD COLUMN company_number VARCHAR(50) NULL;

