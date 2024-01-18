ALTER TABLE user_cfop DROP COLUMN description;
ALTER TABLE user_cfop MODIFY active BOOLEAN NOT NULL DEFAULT 1;
ALTER TABLE user_cfop MODIFY default_cfop BOOLEAN NOT NULL DEFAULT 1;
UPDATE user_cfop SET cfop=REPLACE(cfop," ","");
UPDATE user_cfop SET cfop=CONCAT(SUBSTRING(cfop,1,1),"-",SUBSTRING(cfop,2,6),"-",SUBSTRING(cfop,7,6),"-",SUBSTRING(cfop,13,6)) WHERE LENGTH(cfop)=19;
UPDATE user_cfop SET cfop=CONCAT(SUBSTRING(cfop,1,1),"-",SUBSTRING(cfop,2,6),"-",SUBSTRING(cfop,7,6),"-",SUBSTRING(cfop,13,6),"-",SUBSTRING(cfop,19,6)) WHERE LENGTH(cfop)=25;
DELETE FROM `user_cfop` WHERE user_id=0;
ALTER TABLE `users` MODIFY department_id INT NOT NULL DEFAULT 0;

