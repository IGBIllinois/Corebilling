CREATE TABLE `access_control` (
  `user_id` int(10) unsigned NOT NULL DEFAULT 0,
  `device_id` int(11) NOT NULL,
  `time_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`device_id`)
)\p;

CREATE TABLE `articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `text` text NOT NULL,
  `title` text NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `enabled` BOOLEAN DEFAULT 1,
  PRIMARY KEY (`id`)
)\p;

CREATE TABLE `departments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `department_name` longtext DEFAULT NULL,
  `description` varchar(45) NOT NULL,
  `department_code` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
)\p;

CREATE TABLE `device` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `full_device_name` varchar(255) NOT NULL,
  `status_id` int(11) DEFAULT NULL,
  `loggeduser` int(10) NOT NULL DEFAULT 0,
  `lasttick` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `unauthorized` varchar(45) DEFAULT NULL,
  `device_token` varchar(32) DEFAULT NULL,
  `ldap_group` varchar(64) DEFAULT NULL,
  `ipaddress` VARCHAR(15) DEFAULT "",
  `time_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)\p;

CREATE TABLE `device_rate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rate` float NOT NULL,
  `device_id` int(10) unsigned DEFAULT NULL,
  `rate_id` int(11) DEFAULT NULL,
  `min_use_time` int(11) NOT NULL,
  `rate_type_id` int(11) NOT NULL,
  `time_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)\p;

CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `department_id` int(10) unsigned DEFAULT NULL,
  `netid` varchar(255) DEFAULT NULL,
  `time_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)\p;


CREATE TABLE `rate_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rate_type_name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
)\p;

CREATE TABLE `rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rate_name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
)\p;

CREATE TABLE `reservation_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `stop` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `description` text NOT NULL,
  `training` int(10) unsigned NOT NULL DEFAULT 0,
  `date_created` datetime NOT NULL,
  `deleted` int(11) NOT NULL DEFAULT 0,
  `finished_early` datetime DEFAULT NULL,
  `master_reservation_id` int(10) unsigned DEFAULT NULL,
  `staff_notes` text DEFAULT NULL,
  PRIMARY KEY (`id`)
)\p;

CREATE TABLE `session` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `stop` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `device_id` int(10) unsigned DEFAULT NULL,
  `elapsed` int(10) unsigned NOT NULL DEFAULT 0,
  `rate` float NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `cfop_id` int(11) DEFAULT NULL,
  `min_use_time` int(11) NOT NULL,
  `rate_type_id` int(11) NOT NULL,
  `rate_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`stop`)
)\p;

CREATE TABLE `status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `statusname` varchar(45) NOT NULL,
  `type` int(11) NOT NULL,
  PRIMARY KEY (`id`)
)\p;

CREATE TABLE `user_cfop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `cfop` varchar(45) NOT NULL,
  `description` text NOT NULL,
  `active` int(11) NOT NULL,
  `default_cfop` int(11) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created` (`created`)
)\p;

CREATE TABLE `user_groups` (
  `user_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`group_id`)
)\p;

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
)\p;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(32) DEFAULT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `first` varchar(45) NOT NULL DEFAULT '',
  `last` varchar(45) NOT NULL DEFAULT '',
  `grank` int(10) unsigned NOT NULL DEFAULT 0,
  `rate` varchar(45) NOT NULL DEFAULT '',
  `hidden` tinyint(1) DEFAULT 0,
  `rate_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `user_role_id` int(11) DEFAULT NULL,
  `secure_key` varchar(45) DEFAULT NULL,
  `certified` int(11) NOT NULL DEFAULT 0,
  `time_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)\p;

CREATE TABLE `user_demographics` (
  `user_id` int(11) unsigned NOT NULL,
  `edu_level` varchar(128) DEFAULT NULL,
  `gender` varchar(64) DEFAULT NULL,
  `underrepresented` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `user_demographics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)\p;

CREATE VIEW access_control_hr AS
SELECT `users`.`user_name` AS `user_name`,`device`.`full_device_name` AS `full_device_name` from ((`access_control` join `users` on(`users`.`id` = `access_control`.`user_id`)) join `device` on(`device`.`id` = `access_control`.`device_id`))\p;

INSERT INTO rate_types(rate_type_name) VALUES('Continuous')\p;
INSERT INTO rate_types(rate_type_name) VALUES('Monthly')\p;
INSERT INTO status(statusname,type) VALUES('Online',1)\p;
INSERT INTO status(statusname,type) VALUES('Repair',1)\p;
INSERT INTO status(statusname,type) VALUES('Do Not Track',1)\p;
INSERT INTO status(statusname,type) VALUES('Offline',1)\p;
INSERT INTO status(statusname,type) VALUES('Active',2)\p;
INSERT INTO status(statusname,type) VALUES('Hidden',2)\p;
INSERT INTO status(statusname,type) VALUES('Disabled',2)\p;

INSERT INTO user_roles(role_name) VALUES('Admin')\p;
INSERT INTO user_roles(role_name) VALUES('Supervisor')\p;
INSERT INTO user_roles(role_name) VALUES('User')\p;


