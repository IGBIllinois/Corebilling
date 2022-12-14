IGB Core Facilities Instrument Tracking
====================
[![Build Status](https://github.com/IGBIllinois/Corebilling/actions/workflows/main.yml/badge.svg)](https://github.com/IGBIllinois/Corebilling/actions/workflows/main.yml)

* Web interface to schedule, track, and bill instrument usage by tracking user logins and session times.
## Features 
 * Instrument scheduling (Using the [Full Calendar](http://fullcalendar.io/) plugin)
 ...Users can reserve time on instruments and keep track of their own reservations
 * Permissions (Pages,Instrument Schedules)
 ...Allow or deny access to instruments by roles, groups, or individual users
 * Active Directory Integration / LDAP (easily modified)
 ...Users log in with their existing LDAP/AD credentials
 * User Groups
 ...Users can be organized in groups. Group supervisors can monitor billing for the users in their group.
 * View which devices are in use and by whom
 * Dynamic Usage Statistics (Pie Charts/Graphs)
 * Rate Groups
 ...Different user groups can be assigned different billing rates.
 * Rate Types Continuous/Monthly
 ...Users can be billed for the time they use (in minutes), or on a monthly basis.
 * Individual Billing
 ...Users can view their own bills. Group supervisors can view the bill of anyone in their group. Admins can view and edit all bills.
 * Facility Billing
 ...Admins can view all billing events within a period of time, filter by name, netid, instrument, or group, and export to spreadsheet.
 * News Page
 * Used with [https://github.com/IGBIllinois/CoreBillingService](https://github.com/IGBIllinois/CoreBillingService) to retrieve logged in users on Window Machines

## Requirements
* Apache
* PHP 7.2 or higher
* PHP ldap module
* PHP json module
* PHP pdo module
* PHP mysqlnd module
* MySQL/MariaDB >= 5.5
* PHP Composer

## Installation
* Git clone repository or download a tag released at [https://github.com/IGBIllinois/Corebilling/releases](https://github.com/IGBIllinois/Corebilling/releases)
```
git clone https://github.com/IGBIllinois/Corebilling.git
```
* Create mysql database
```
CREATE DATABASE corebilling CHARACTER SET utf8;
```
* Create mysql user with insert,update,select,delete privileges on the database
```
CREATE USER 'corebilling'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT SELECT,INSERT,DELETE,UPDATE ON corebilling.* to 'corebilling'@'localhost';
```
* Import database structure
```
mysql -u root -p corebilling < sql/corebilling.sql
```
* Add initial Admin User
```
INSERT INTO users(user_name,user_role_id) VALUES('<USERNAME>',1);
```
* Add apache config to point to html directory
```
Alias /corebilling /var/www/corebilling/html
<Directory /var/www/corebilling/html>
	Allowoverride All
	Require all granted
</Directory>
```
* Copy html/includes/config.default.php to html/includes/config.php
```
cp html/includes/config.default.php html/includes/config.php
```
* Edit html/includes/config.php for your setup
* If enabling log file, set permissions on the log folder for the apache user to read/write.
```
chown apache.apache log
```
* Enable log rotation by copying conf/log_rotate.conf.dist to /etc/logrotate.d/corebilling.  Adjust the log folder in the file
```
cp conf/log_rotate.conf.dist /etc/logrotate.d/corebilling
```
* Install composer packages
```
composer install
```
* To Enable creation of data folders, need to allow apache user have sudo to a local user.  This local user needs to have ssh access to the data storage machine.
* An example sudoers file is at conf/www/sudoer_www.dist.  This can be placed in /etc/sudoers.d/
* Change the paths to the install location of Core Billing
```
Cmnd_Alias COREAPP = /var/www/corebilling/bin/addCoreServerDir.sh /var/www/corebilling/bin/CoreServerDirExists.sh
apache localhost = (coreapp) NOPASSWD: COREAPP
Defaults:apache !requiretty

```
* On The data storage machine, create a local user
* An example sudoers files is at conf/data_sudoers.dist.  This can be placed in /etc/sudoers.d/
```
Cmnd_Alias COREAPP = /var/www/corebilling/bin/mkcoredir /var/www/corebilling/bin/dirExists.sh
coreapp localhost=(root) NOPASSWD: COREAPP
```
* Create ssh keys between your web server and data storage machine.
* To enable calculation of data usage, copy the example /conf/cron.dist to /etc/cron.d/corebilling and edit the paths in the file
```
cp /var/www/corebilling/etc/cron.dist /etc/cron.d/corebilling
```
* Done
