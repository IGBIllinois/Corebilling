ALTER TABLE users CHANGE date_added time_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER certified;
ALTER TABLE access_control ADD time_created DATETIME;
UPDATE access_control SET time_created='0000-00-00 00:00:00';
ALTER TABLE access_control MODIFY time_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE groups ADD UNIQUE(netid);
ALTER TABLE groups ADD enabled BOOLEAN DEFAULT 1;
ALTER TABLE users ADD UNIQUE(user_name);

CREATE TABLE data_cost(
	data_cost_id INT NOT NULL AUTO_INCREMENT,
	data_cost_value DECIMAL(30,7),
	data_cost_time_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	data_cost_enabled BOOLEAN DEFAULT TRUE,
	PRIMARY KEY (data_cost_id)
);
CREATE TABLE data_dir (
        data_dir_id INT NOT NULL AUTO_INCREMENT,
	data_dir_group_id INT REFERENCES groups(id),
	data_dir_user_id INT REFERENCES users(id),
        data_dir_path VARCHAR(255),
        data_dir_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_dir_enabled BOOLEAN DEFAULT TRUE,
	data_dir_exists BOOLEAN DEFAULT FALSE,
        PRIMARY KEY (data_dir_id),
	UNIQUE(data_dir_path)
);
CREATE TABLE data_usage (
	data_usage_id INT NOT NULL AUTO_INCREMENT,
	data_usage_data_dir_id INT REFERENCES data_dir(data_dir_id),
	data_usage_bytes BIGINT UNSIGNED,
	data_usage_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (data_usage_id)
);
CREATE TABLE data_bill (
        data_bill_id INT NOT NULL AUTO_INCREMENT,
        data_bill_data_dir_id INT REFERENCES data_dir(data_dir_id),
        data_bill_data_cost_id INT REFERENCES user_cfop(id),
	data_bill_group_id INT REFERENCES groups(id),
	data_bill_user_id INT REFERENCES users(id),
        data_bill_cfop_id INT REFERENCES cfops(cfop_id),
        data_bill_date TIMESTAMP,
        data_bill_avg_bytes BIGINT(20) DEFAULT 0,
        data_bill_total_cost DECIMAL(30,7),
        data_bill_billed_cost DECIMAL(30,7),
        PRIMARY KEY(data_bill_id)
);

INSERT INTO data_cost(data_cost_value) VALUES(0.00);

