<?php
require_once 'includes/header.inc.php';

if(!$login_user->isAdmin()){
        echo html::error_message("You do not have permission to view this page.","403 Forbidden");
        require_once 'includes/footer.inc.php';
        exit;
}
?>

<h3>About</h3>
<div class='row'>
<div class='col-md-8 col-lg-8 col-xl-8'>
<table class='table table-bordered table-condensed'>
<tr><td>Code Website</td></td><td><a href='<?php echo settings::get_codewebsite_url(); ?>' target='_blank'><?php echo settings::get_codewebsite_url(); ?></a></td></tr>
<tr><td>App Version</td><td><?php echo settings::get_version(); ?></td></tr>
<tr><td>Webserver Version</td><td><?php echo \IGBIllinois\Helper\functions::get_webserver_version(); ?></td></tr>
<tr><td>MySQL Version</td><td><?php echo $db->getAttribute(\PDO::ATTR_SERVER_VERSION); ?></td>
<tr><td>PHP Version</td><td><?php echo phpversion(); ?></td></tr>
<tr><td>PHP Extensions</td><td><?php 
$extensions_string = "";
foreach (\IGBIllinois\Helper\functions::get_php_extensions() as $row) {
	$extensions_string .= implode(", ",$row) . "<br>";
}
echo $extensions_string;
 ?></td></tr>

</table>
</div>
</div>
<div class='row'>
<h3>Settings</h3>
<div class='col-md-8 col-lg-8 col-xl-8'>
<table class='table table-bordered table-condensed'>
<tr><td>LDAP_HOST</td><td><?php echo settings::get_ldap_host(); ?></td></tr>
<tr><td>LDAP_BASE_DN</td><td><?php echo settings::get_ldap_base_dn(); ?></td></tr>
<tr><td>LDAP_PEOPLE_DN</td><td><?php echo LDAP_PEOPLE_DN; ?></td></tr>
<tr><td>LDAP_GROUP_DN</td><td><?php echo LDAP_GROUP_DN; ?></td></tr>
<tr><td>LDAP_PORT</td><td><?php echo settings::get_ldap_port(); ?></td></tr>
<tr><td>LDAP_SSL</td><td><?php echo settings::get_ldap_ssl() ? 'true' : 'false';  ?></td></tr>
<tr><td>LDAP_TLS</td><td><?php echo settings::get_ldap_tls() ? 'true' : 'false'; ?></td></tr>
<tr><td>TIMEZONE</td><td><?php echo settings::get_timezone(); ?></td></tr>
<tr><td>LDAPMAN_API_ENABLED</td><td><?php echo LDAPMAN_API_ENABLED ? 'true' : 'false'; ?></td></tr>
<tr><td>LDAPMAN_API_URL</td><td><?php echo LDAPMAN_API_URL; ?></td></tr>
<tr><td>LDAPMAN_DEVICE_PREFIX</td><td><?php echo LDAPMAN_DEVICE_PREFIX; ?></td></tr>
<tr><td>LDAPMAN_PI_PREFIX</td><td><?php echo LDAPMAN_PI_PREFIX; ?></td></tr>
<tr><td>DATASERVER_ENABLED</td><td><?php echo settings::get_dataserver_enabled() ? 'true' : 'false'; ?></td></tr>
<tr><td>DATASERVER_ROOT_DIR</td><td><?php echo settings::get_dataserver_root_dir(); ?></td></tr>
<tr><td>DB_HOST</td><td><?php echo DB_HOST; ?></td></tr>
<tr><td>DB_NAME</td><td><?php echo DB_NAME; ?></td></tr>
<tr><td>TITLE</td><td><?php echo settings::get_title(); ?></td></tr>
<tr><td>SESSION_TIMEOUT (seconds)</td><td><?php echo settings::get_session_timeout(); ?></td></tr>
<tr><td>CAL_DEFAULT_COLOR</td>
	<td>
	<svg width="20" height="20"><rect width="20" height="20" style="fill:<?php echo CAL_DEFAULT_COLOR; ?>;stroke-width:3;stroke:rgb(0,0,0)" /></svg>
	&nbsp;<?php echo strtoupper(CAL_DEFAULT_COLOR); ?>
	</td>
</tr>
<tr><td>CAL_TRAINING_COLOR</td>
	<td>
	<svg width="20" height="20"><rect width="20" height="20" style="fill:<?php echo CAL_TRAINING_COLOR; ?>;stroke-width:3;stroke:rgb(0,0,0)" /></svg>
	&nbsp;<?php echo strtoupper(CAL_TRAINING_COLOR); ?>
	</td>
</tr>
<tr><td>CAL_MISSED_COLOR</td>
	<td>
	<svg width="20" height="20"><rect width="20" height="20" style="fill:<?php echo CAL_MISSED_COLOR; ?>;stroke-width:3;stroke:rgb(0,0,0)" /></svg>
	&nbsp;<?php echo strtoupper(CAL_MISSED_COLOR); ?>
        </td>
</tr>
<tr><td>CAL_ROOM_COLOR</td>
	<td>
	<svg width="20" height="20"><rect width="20" height="20" style="fill:<?php echo CAL_ROOM_COLOR; ?>;stroke-width:3;stroke:rgb(0,0,0)" /></svg>
	&nbsp;<?php echo strtoupper(CAL_ROOM_COLOR); ?>
        </td>
</tr>
<tr><td>RESERVE_ROOM</td><td><?php echo RESERVE_ROOM ? 'true' : 'false'; ?></td></tr>
<tr><td>USER_EXCEPTIONS_ARRAY</td><td><?php echo implode(',',USER_EXCEPTIONS_ARRAY); ?></td></tr>
<tr><td>ENABLE_LOG</td><td><?php echo settings::get_log_enabled() ? 'true' : 'false'; ?></td></tr>
<tr><td>PASSWORD_RESET_URL</td><td><?php echo settings::get_password_reset_url(); ?></td></tr>
<tr><td>NEWS_AGE (days)</td><td><?php echo settings::get_news_age(); ?></td></tr>
<tr><td>SMTP_HOST</td><td><?php echo settings::get_smtp_host(); ?></td></tr>
<tr><td>SMTP_PORT</td><td><?php echo settings::get_smtp_port(); ?></td></tr>
<tr><td>SMTP_USERNAME</td><td><?php echo settings::get_smtp_username(); ?></td></tr>
<tr><td>FROM</td><td><?php echo settings::get_from_email(); ?></td></tr>
<tr><td>FROM_NAME</td><td><?php echo settings::get_from_name(); ?></td></tr>
<tr><td>SU_USER</td><td><?php echo settings::get_su_user(); ?></td></tr>
</table>
</div>
</div>
<?php
require_once 'includes/footer.inc.php';

?>
