-- Copyright (C) 2026 Dolicraft

ALTER TABLE llx_dolicrafts3_log ADD INDEX idx_dolicrafts3_log_entity (entity);
ALTER TABLE llx_dolicrafts3_log ADD INDEX idx_dolicrafts3_log_action (action);
ALTER TABLE llx_dolicrafts3_log ADD INDEX idx_dolicrafts3_log_datec (datec);
ALTER TABLE llx_dolicrafts3_log ADD INDEX idx_dolicrafts3_log_fk_user (fk_user);
