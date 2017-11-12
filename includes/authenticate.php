<?php

$authen = new LdapAuth(LDAP_HOST,LDAP_PEOPLE_DN,LDAP_GROUP_DN, LDAP_SSL, LDAP_PORT);

$logonerror=" ";
if (isset($_POST['logout']))
{
                unset($_SESSION['username']);
                unset($_SESSION['userid']);
                unset($_SESSION['password']);
                unset($_SESSION['usertype']);
		unset($_SESSION['newuser']);
}

if(!isset($_SESSION['userid'])) {
        if (isset($_POST['Login'])){
                if($authen->Authenticate($_POST['username'],$_POST['password'],""))
                {
			$statusActive = 5;
			$statusHidden = 6;
                        $queryUserID = "SELECT ID FROM users WHERE username=\"".$_POST['username']."\" AND (statusid=".$statusActive." OR statusid=".$statusHidden.")";
                        $userID = $sqlDataBase->singleQuery($queryUserID);

                        if($userID)
                        {
                                $authenUser = new User($sqlDataBase);
                                $authenUser->LoadUser($userID);

                                $_SESSION['userid'] = $authenUser->GetID();
                                $_SESSION['username'] = $authenUser->GetUserName();
                                $_SESSION['password'] = $_POST['password'];
                                $_SESSION['usertype'] = $authenUser->GetUserTypeID();
				$_SESSION['newuser'] = 0;
                        }
                        else {
                                $logonerror=$logonerror."Your account wasn't activated for this website, please contact <b><a href=\"mailto:lheil@uiuc.edu\"><font color=\"red\" size=2>Lori Heil</font></a></b> to be activated.1";
				$_SESSION['username']= $_POST['username'];
				$_SESSION['newuser'] = 1;
                        }
                }
                else{
                        $logonerror=$logonerror."<b class=b>No match was found.<br />Please try again!</b>";
                }
        }
}
else
{
        if($authen->Authenticate($_SESSION['username'],$_SESSION['password'],""))
        {
        }
        else
        {
                unset($_SESSION['username']);
                unset($_SESSION['userid']);
                unset($_SESSION['password']);
                unset($_SESSION['usertype']);
        }
}

?>
