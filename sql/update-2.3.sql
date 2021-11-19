ALTER TABLE users DROP COLUMN grank;
ALTER TABLE device ADD json JSON DEFAULT '{}' AFTER ipaddress, ADD CONSTRAINT CHECK(JSON_VALID(json)); 
ALTER TABLE departments MODIFY department_name VARCHAR(255), MODIFY description VARCHAR(255);
ALTER TABLE departments ADD UNIQUE(department_name);
ALTER TABLE articles MODIFY title VARCHAR(255);
UPDATE users SET status_id=1 WHERE status_id=5;
UPDATE users SET status_id=0 WHERE status_id=6;
UPDATE users SET status_id=0 WHERE status_id=7;
ALTER TABLE users MODIFY status_id BOOLEAN;
ALTER TABLE users CHANGE status_id status BOOLEAN;
DELETE FROM status WHERE type=2;
ALTER TABLE status DROP COLUMN `type`;
RENAME TABLE status TO device_status;


