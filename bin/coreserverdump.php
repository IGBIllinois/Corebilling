<?php
ini_set("display_errors",1);
include('../html/includes/config.php');
include('../html/includes/auto_load_classes.php');
include('../html/includes/mysql_connect.php');

$allGroups = Group::getAllGroups($db);
$selectedGroup = new Group($db);

foreach($allGroups as $group){
    if($group['netid'] !== null){
        $gid = LDAPMAN_PI_PREFIX.$group['netid'];
        $pi = $group['netid'];

        $selectedGroup->load($group['id']);
        $activeUsers = false;
        foreach($selectedGroup->getMembers() as $member){
            if($member['status_id'] == User::ACTIVE) {
                $username = $member['user_name'];
                $activeUsers = true;
                if ( $username !== $pi ) {
                    echo "mkcoredir -g $gid -p $pi -u $username\n";
                }
            }
        }
        if($activeUsers){
            echo "mkcoredir -g $gid -p $pi -u $pi\n";
        }
    }
}