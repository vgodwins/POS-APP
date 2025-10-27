-- Track user login activity timestamps
ALTER TABLE users
  ADD COLUMN last_login_at DATETIME NULL;

