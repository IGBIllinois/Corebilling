CREATE TABLE data_cost(
	data_cost_id INT NOT NULL AUTO_INCREMENT,
	data_cost_type VARCHAR(255),
	data_cost_value DECIMAL(30,7),
	data_cost_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	data_cost_enabled BOOLEAN DEFAULT TRUE,
	PRIMARY KEY (data_cost_id)
);
CREATE TABLE data_dir (
        data_dir_id INT NOT NULL AUTO_INCREMENT,
        data_dir_path VARCHAR(255),
        data_dir_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_dir_enabled BOOLEAN DEFAULT TRUE,
        data_dir_default BOOLEAN DEFAULT FALSE,
        PRIMARY KEY (data_dir_id)
);
CREATE TABLE data_usage (
	data_usage_id INT NOT NULL AUTO_INCREMENT,
	data_usage_data_dir_id INT REFERENCES data_dir(data_dir_id),
	data_usage_cfop_id INT REFERENCES cfops(cfop_id),
	data_usage_bytes BIGINT UNSIGNED,
	data_usage_files BIGINT UNSIGNED,
	data_usage_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (data_usage_id)
);
CREATE TABLE data_bill (
        data_bill_id INT NOT NULL AUTO_INCREMENT,
        data_bill_data_dir_id INT REFERENCES data_dir(data_dir_id),
        data_bill_data_cost_id INT REFERENCES data_cost(data_cost_id),
        data_bill_cfop_id INT REFERENCES cfops(cfop_id),
        data_bill_date TIMESTAMP,
        data_bill_avg_bytes BIGINT(20) DEFAULT 0,
        data_bill_total_cost DECIMAL(30,7),
        data_bill_billed_cost DECIMAL(30,7),
        PRIMARY KEY(data_bill_id)
);
