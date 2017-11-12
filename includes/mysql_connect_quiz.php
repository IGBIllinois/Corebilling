<?php
function get_quiz_users () {
@define ('DB_USER','instweb');
@define ('DB_PASSWORD','1n$tpa$$');
@define ('DB_HOST','localhost');
@define ('DB_NAME','igb_instru');

@$dbc=mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die('Could not connect to MySQL:'.mysql_error());
mysql_select_db(DB_NAME,$dbc) OR die ('could not select the database: '.mysql_error());












return $users;


}
?>
