ALTER TABLE rates ENGINE innodb;
ALTER TABLE status ENGINE innodb;
ALTER TABLE user_cfop ENGINE innodb;
ALTER TABLE user_roles ENGINE innodb;
DROP TABLE old_event_info;
DROP TABLE old_session;
DROP TABLE old_users;
DROP VIEW user_list;
ALTER TABLE articles MODIFY created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE articles ADD enabled BOOLEAN DEFAULT 1;
ALTER TABLE device ADD ipaddress VARCHAR(15) DEFAULT "";
ALTER TABLE device ADD time_created DATETIME;
UPDATE device SET time_created='0000-00-00 00:00:00';
ALTER TABLE device MODIFY time_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE device MODIFY device_name VARCHAR(255) NOT NULL;
ALTER TABLE device MODIFY full_device_name VARCHAR(255) NOT NULL;
ALTER TABLE user_cfop MODIFY created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE groups ADD time_created DATETIME;
UPDATE groups SET time_created='0000-00-00 00:00:00';
ALTER TABLE groups MODIFY time_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE device_rate ADD time_created DATETIME;
UPDATE device_rate SET time_created='0000-00-00 00:00:00';
ALTER TABLE device_rate MODIFY time_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

