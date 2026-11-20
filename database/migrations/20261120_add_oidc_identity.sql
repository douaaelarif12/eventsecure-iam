ALTER TABLE users
  ADD COLUMN IF NOT EXISTS oidc_subject VARCHAR(255) DEFAULT NULL AFTER role,
  ADD COLUMN IF NOT EXISTS identity_provider VARCHAR(50) DEFAULT NULL AFTER oidc_subject,
  ADD COLUMN IF NOT EXISTS last_login_at DATETIME DEFAULT NULL AFTER identity_provider;

CREATE UNIQUE INDEX IF NOT EXISTS uq_users_oidc_identity
  ON users (identity_provider, oidc_subject);
