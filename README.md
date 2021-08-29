IGB Core Facilities Instrument Tracking
====================
[![Build Status](https://www.travis-ci.com/IGBIllinois/CoreBilling.svg?branch=master)](https://www.travis-ci.com/IGBIllinois/CoreBilling)

* Web interface to schedule, track, and bill instrument usage by tracking user logins and session times.
## Features 
 * Instrument scheduling (Using the [Full Calendar](http://fullcalendar.io/) plugin)
 ...Users can reserve time on instruments and keep track of their own reservations
 * Permissions (Pages,Instrument Schedules)
 ...Allow or deny access to instruments by roles, groups, or individual users
 * Active Directory Integration / LDAP (easily modified)
 ...Users log in with their existing IGB credentials
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
* MySQL/MariaDB >= 5.5

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
CREATE USER 'posting_log'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT SELECT,INSERT,DELETE,UPDATE ON corebilling.* to 'corebilling'@'localhost';
```
* Import database structure
```
mysql -u root -p corebilling < sql/corebilling.sql
```
* Add apache config to point to html directory
```
Alias /corebilling /var/www/corebilling/html
<Directory /var/www/corebilling/html>
	Allowoverride none
	Require all granted
</Directory>
```
* Copy html/includes/config.default.php to html/includes/config.php
```
cp html/includes/config.default.php html/includes/config.php
```
* Edit html/includes/config.php for your setup
* Done
