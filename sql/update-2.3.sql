ALTER TABLE users DROP COLUMN grank;
ALTER TABLE device ADD json JSON DEFAULT '{}' AFTER ipaddress, ADD CONSTRAINT CHECK(JSON_VALID(json)); 
ALTER TABLE departments MODIFY department_name VARCHAR(255), MODIFY description VARCHAR(255);
ALTER TABLE departments ADD UNIQUE(department_name);
ALTER TABLE articles MODIFY title VARCHAR(255);
