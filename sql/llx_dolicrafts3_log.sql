-- Copyright (C) 2026 Dolicraft
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.

CREATE TABLE IF NOT EXISTS llx_dolicrafts3_log (
    rowid           integer AUTO_INCREMENT PRIMARY KEY,
    entity          integer DEFAULT 1 NOT NULL,
    action          varchar(50) NOT NULL,         -- upload, download, delete, sync, copy
    object_key      varchar(500) NOT NULL,        -- S3 object key
    bucket          varchar(255) NOT NULL,        -- Bucket name
    file_size       bigint DEFAULT 0,             -- File size in bytes
    status          smallint DEFAULT 1 NOT NULL,  -- 1=success, 0=error
    error_message   text,                         -- Error details if failed
    fk_user         integer NOT NULL,             -- User who performed action
    datec           datetime NOT NULL              -- Creation date
) ENGINE=InnoDB;
