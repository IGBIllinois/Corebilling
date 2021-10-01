#!/usr/bin/env php
<?php
// setting up the web root and server root for
include('../html/includes/config.php');
include('../html/includes/auto_load_classes.php');
include('../html/includes/mysql_connect.php');

$query = "update groups set netid=:netid where group_name=:name limit 1";
$stmt = $db->prepare($query);
$fh = fopen('netids.csv', 'r');

while (($line = fgets($fh)) !== false){
    $line = trim($line);
    $data = str_getcsv($line);
    $stmt->execute(array(':name'=>$data[0], ':netid'=>$data[2]));
}
