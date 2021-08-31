#!/usr/bin/env php
<?php
ini_set("display_errors",1);
include('../html/includes/config.php');
include('../html/includes/auto_load_classes.php');
include('../html/includes/mysql_connect.php');

$query = "replace into user_demographics (user_id, edu_level, gender, underrepresented) SELECT id as user_id, :edu as edu_level, :gender as gender, :under as underrepresented from users where user_name=:username";
$stmt = $db->prepare($query);
$selstmt = $db->prepare('select * from users where user_name=:username');
$fh = fopen('demographics.csv','r');

while (($line = fgets($fh)) !== false) {
	$line = trim($line);
	$data = explode(',',$line);
	$stmt->execute(array(':username'=>$data[0],':gender'=>$data[1],':under'=>$data[2],':edu'=>$data[3]));
}

fclose($fh);
