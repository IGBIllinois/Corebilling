<?php
	if(isset($_SESSION['userid'])) {
	include "./includes/mysql_connect.php";
?>

<h4><center>Device Setup Instructions</center></h4>
<div id="semenu">
<br />
Step 1: <br />
Add the device to the device list on the website using the <a class="calendar" href="./administration.php?subm=2" /> Edit Device Menu</a><br />, make sure you enter the computer name into the Device ID text field with no mistakes.
Download the <a class="calendar" href="./downloads/mysql-connector-odbc-3.51.12-win32.msi">ODBC drivers</a> and install them on the windows machine.
Use all the default settings for the installation. (If your system is 64bit don't bother downloading this driver, just install the x64 scripts instead)
<br />
<br />
Step 2: <br />
Go to Start >> Control Panel >> Administrative Tools >> Data Source (ODBC) Shortcut, select the System DSN Tab (if you are using an x64 XP you will have to go to C:\WINDOWS\SysWOW64\odbcad32.exe to bring up the ODBC menu instead of the control panel)

Good luck. 
.<br /><br />
Click on the "Add..." tab and select "MySQL ODBC 3.51 Driver" from the list and click finish (should be one of the last entries). <br /><br />
Fill in the information as depicted below: <br /><br />
<table>
<tr>
<td width="150">
Data Source Name:<br />
Description:<br />
Server:<br />
User:<br />
Password:<br />
Database:<br />
</td>
<td>
MyWebUser <br />
connect to mysql <br />
core.igb.uiuc.edu <br />
devicelogon <br />
<a class="calendar" href="http://www.igb.uiuc.edu/cnrg/contact.php">(Contact CNRG)</a><br />
igb_instru <br />
</td>
</tr>
</table>
<br />
Click Test to make sure the configuration works then click OK. <br /><br />

Step 3: <br />
Download the <a class="calendar" href="./downloads/scripts.zip">logon/logoff scripts 32bit</a>  or  <a class="calendar" href="./downloads/scripts_x64.zip">logon/logoff scripts 64bit</a><br />

Go to Start >> Run paste this path C:\WINDOWS\system32\GroupPolicy\User\Scripts and click OK. <br />Now unzip the scripts.zip file and drop Logon.vbs into the Logon folder and Logoff.vbs into the logoff folder.
<br />

Go to Start >> Run and type in gpedit.msc and hit OK. Expand User Configuration >> Windows Settings and select Scripts (Logon/Logoff). <br /><br />

Double Click the Logon option on the right pane and select "Add..." click on "Browse..." select the Logon.vbs file and click Open then Click OK on both menus. Do the same for the logoff script <br /><br />
(Double Click the Logoff option on the right pane and select "Add..." click on "Browse..." select the Logoff.vbs file and click Open then Click OK on both menus.)
<br />
<br>
In the Group Policy window go to Computer Configuration >> Administrative Templates >> Syste >> User Profiles
<br>
Selelct "Only allow local user pofiles" from the right selection and enable it.
<br />
<br>

Step 4:
Go into Start >> Controlo Panel >> Administrative Tools >> Computer Management >> Local Users and Groups >>Groups and select Administrators Click Add.. type in the white text area IGB\Domain users and hit OK.
</div>


<?php
} else {
	include "./denied.php";
}
?>

