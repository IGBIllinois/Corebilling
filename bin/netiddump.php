#!/usr/bin/env php
<?php
chdir(dirname(__FILE__));

$include_paths = array('../libs');
set_include_path(get_include_path() . ":" . implode(':',$include_paths));

function my_autoloader($class_name) {
        if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}
spl_autoload_register('my_autoloader');

require_once '../conf/app.inc.php';
require_once '../conf/config.inc.php';
require_once '../vendor/autoload.php';

date_default_timezone_set(settings::get_timezone());

$query = "update groups set netid=:netid where group_name=:name limit 1";
$stmt = $db->prepare($query);
$fh = fopen('netids.csv', 'r');

while (($line = fgets($fh)) !== false){
    $line = trim($line);
    $data = str_getcsv($line);
    $stmt->execute(array(':name'=>$data[0], ':netid'=>$data[2]));
}
