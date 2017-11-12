<?php
class Calendar
{
	private $sqlDataBase;
	private $deviceID;
	private $user;
	private $userType;
	private $Url;
	private $filterTraining;
	public function __construct(SQLDataBase $sqlDataBase)
	{
		$this->sqlDataBase = $sqlDataBase;
		$this->deviceID=0;
		$this->userID=0;
		$this->Url="";
		$filterTraining=0;
	}
	
	public function __destruct()
	{
		
	}

	public function Device($deviceID)
	{
		$this->deviceID=$deviceID;
	}
	
	public function User(User $user)
	{
		$this->user = $user;
	}
	
	public function URL($Url)
	{
		$this->Url = $Url;
	}
	public function calendar($month,$year)
        {
         	$month_name = Date('F',mktime(0, 0, 0, $month, 1, $year));

         	$this_month = Date('n',mktime(0, 0, 0, $month, 1, $year));
         	$next_month = Date('n',mktime(0, 0, 0, $month + 1, 1, $year));

         	//Find out when this month starts and ends.
         	$first_week_day =  Date('w',mktime(0, 0, 0, $month, 1, $year));;
         	$days_in_this_month = Date('t',mktime(0, 0, 0, $month, 1, $year));

		$calendar_html = "<script language=\"JavaScript\" src=\"includes/qTip.js\" type=\"text/JavaScript\"></script>";
         	$calendar_html .= "<table cellpadding=\"0\" cellspacing=\"3\" width=\"100%\">";
        	$calendar_html .= "<tr class=\"title\"><td colspan=\"7\" align=\"center\">" .
                           $month_name . " " . $year . "</td></tr>";
		if($first_week_day > 0)
		{
			$calendar_html .= "<tr class=\"title\"><td width=\"15%\"> Sunday </td><td width=\"14%\"> Monday </td><td width=\"14%\"> Tuesday </td><td width=\"14%\"> Wednesday </td><td width=\"14%\"> Thursday </td><td width=\"14%\"> Friday </td><td width=\"15%\"> Saturday </td></tr>";
		}
         	$calendar_html .= "<tr>";

         	//Fill the first week of the month with the appropriate number of blanks.
         	for($week_day = 0; $week_day < $first_week_day; $week_day++)
          	{
            		$calendar_html .= "<td> </td>";
            	}

         	$week_day = $first_week_day;
         	for($day_counter = 1; $day_counter <= $days_in_this_month; $day_counter++)
            	{
            		$week_day %= 7;
				
            		if($week_day == 0)
               			//$calendar_html .= "</tr><tr class=\"title\"><td> Sunday </td><td> Monday </td><td> Tuesday </td><td> Wednesday </td><td> Thursday </td><td> Friday </td><td> Saturday </td></tr><tr>";
				$calendar_html .= "</tr><tr class=\"title\"><td width=\"15%\"> Sunday </td><td width=\"14%\"> Monday </td><td width=\"14%\"> Tuesday </td><td width=\"14%\"> Wednesday </td><td width=\"14%\"> Thursday </td><td width=\"14%\"> Friday </td><td width=\"15%\"> Saturday </td></tr>";
			
               		$calendar_html .= "<td align=\"center\"><table width=\"100%\" cellpadding=\"0\" cellspacing=\"1\"><tr class=\"title\"><td>" . $day_counter . "</td></tr>";
		
			$dayEvents = $this->DateEvents($day_counter,$this_month,$year);
			$deviceBox = 0;	
			if($dayEvents)
			{
				$rowNum=0;
				foreach($dayEvents as $id=>$assoc)
				{
					if(($this->deviceID==0 && $assoc['deviceid']!=$deviceBox) || $this->deviceID > 0)
					{
						$deviceBox = $assoc['deviceid'];
						$calendar_html .= "</td></tr><tr class=\"d".($rowNum % 2)."\"><td>";
						
						if($this->deviceID == 0)
						{
							$calendar_html .="<font color=\"#a5c519\" size=\"3\">".$assoc['fdname']."</font><br>";
						}
						$rowNum++;
					}
					
					if($assoc['training']==1)
					{
						$calendar_html .= "<a href=\"#\" title=\"<center>Reservation Information</center><center>(Training)</center><br>Name: ".$assoc['first']." ".$assoc['last']."<br>Device: ".$assoc['fdname']."<br>Description: ".$assoc['description']."<br>E-Mail: ".$assoc['email']."\" style=\"color:#ff9145;font-size:14px;text-decoration:none;\">".Date('G:i',strtotime($assoc['starttime']))."-".Date('G:i',strtotime($assoc['stoptime']))."</a><br>";
					}
					else
					{
						$calendar_html .= "<a href=\"#\" title=\"<center>Reservation Information</center><br>Name: ".$assoc['first']." ".$assoc['last']."<br>Device: ".$assoc['fdname']."<br>Description: ".$assoc['description']."<br>E-Mail: ".$assoc['email']."\" style=\"font-size:14px;text-decoration:none;\">".Date('G:i',strtotime($assoc['starttime']))."-".Date('G:i',strtotime($assoc['stoptime']))."</a><br>";
					}

					if($this->user->GetUserTypeID()==1 || $this->user->GetID()==$assoc['userid'])
                                        {
                                                $calendar_html .= "".$assoc['username']."<br>";
						if($assoc['description']!="")
						{
                                                	$calendar_html .= $assoc['description']."<br>";
						}
                                                $calendar_html .= "<a href=\"".$this->Url."&del=".$assoc['ID']."&device=".$assoc['deviceid']."&month=".$this_month."&year=".$year."\">Delete</a><br><br>";
                                        }
					
					if($this->deviceID==0)
					{
						$calendar_html .= " ";
					}
					if(($this->deviceID==0 && $assoc['deviceid']!=$deviceBox) || $this->deviceID > 0)
					{	
						$calendar_html .= "</td></tr>";
					}
					
					
				}
			}
			$calendar_html .= "</table>";
            		$week_day++;
            	}

         	$calendar_html .= "</td></tr>";
         	$calendar_html .= "</table>";

         	return($calendar_html);
        }

	private function DateEvents($day,$month,$year)
	{
		$conditionTraining="";
		if($this->filterTraining == 1)
		{
			$conditionTraining=" AND training=1";
		}

		if($this->deviceID==0)
		{
			$queryEvents="SELECT e.ID, d.devicename, d.fdname, e.deviceid, u.username, u.first, u.last, u.email, e.userid, e.description, e.start AS starttime, e.stop AS stoptime, e.training FROM event_info e, device d, users u WHERE MONTH(e.start)=".$month." AND YEAR(e.start)=".$year." AND DAY(e.start)=".$day." AND u.ID=e.userid AND d.ID=e.deviceid".$conditionTraining." ORDER BY e.deviceid, e.start";
		}
		else
		{
			$queryEvents="SELECT e.ID, d.devicename, d.fdname, e.deviceid, u.username, u.first, u.last, u.email, e.userid, e.description, e.start AS starttime, e.stop AS stoptime, e.training FROM event_info e, device d, users u WHERE MONTH(e.start)=".$month." AND YEAR(e.start)=".$year." AND DAY(e.start)=".$day." AND u.ID=e.userid AND d.ID=e.deviceid AND e.deviceid=".$this->deviceID.$conditionTraining." ORDER BY e.start";
		}
		$events = $this->sqlDataBase->query($queryEvents);
		return $events;
	}

	public function AddEvent($startTimeStamp, $endTimeStamp, $description, $deviceID, $userID, $train)
	{
		if($this->CheckEventConflicts($deviceID, $startTimeStamp, $endTimeStamp))
		{
			$queryAddEvent="INSERT INTO event_info (deviceid,userid,start,stop,description,training,date_created) VALUES(".$deviceID.",".$userID.",\"".$startTimeStamp."\",\"".$endTimeStamp."\",\"".$description."\",".$train.",NOW())";
			$this->sqlDataBase->insertQuery($queryAddEvent);
		}
		else
		{
			echo "<font color=\"red\">Notice: Reservation conflict found for ".$startTimeStamp." <-> ".$endTimeStamp." please try a different time slot</font><br>";
			return 0;
		}
	}

	public function DeleteEvent($eventId)
	{
		$queryEventStart = "SELECT start,date_created FROM event_info WHERE ID=".$eventId;
		$startDate = $this->sqlDataBase->query($queryEventStart);
		$oneDaySeconds = 24*60*60;
		$oneHourSeconds = 60*60;
		if($this->AuthorizeEventAction($eventId))
		{
			/*
			if(abs((time()-strtotime($startDate[0]['start']))) < $oneDaySeconds && abs(time()-strtotime($startDate[0]['date_created'])) > 60*60 )
			{
				$subject = "Core Facilities Notice";
				$message = "Notice: Calendar reservation has been deleted within 24 hours of reservation date.\r\n\r\n";
				$this->EmailEventWarning($subject,$message,$eventId);
			}
			*/
			$queryDelEvent = "DELETE FROM event_info WHERE ID=".$eventId;
                        $this->sqlDataBase->nonSelectQuery($queryDelEvent);
		}
		else
		{
			echo "<font color=\"red\">Notice: Failed to authorize or event does not exist</font>";
		}
	}	
	
	private function EmailEventWarning($subject,$message,$eventId)
	{
		$userTypeAdmin = 1;
		$queryUserEventInfo = "SELECT ei.start,ei.stop,ei.description, u.email,u.first,u.last, d.fdname, u.username FROM event_info ei, users u, device d WHERE ei.ID=".$eventId." AND u.ID=ei.userid AND d.ID=ei.deviceid AND u.usertypeid!=1";
		$userEventInfo = $this->sqlDataBase->query($queryUserEventInfo);

		if(isset($userEventInfo))
		{
			$queryUserAdmins = "SELECT email,ID FROM users WHERE usertypeid=".$userTypeAdmin;
			$adminUsers = $this->sqlDataBase->query($queryUserAdmins);
			//Email the user the event blongs too
			$to      = $userEventInfo[0]['email'];
			$headers = 'From: core-training@igb.illinois.edu' . "\r\n" .
    			'Reply-To: core-training@igb.illinois.edu' . "\r\n" .
    			'X-Mailer: PHP/' . phpversion();
			$subject .= " for ".$userEventInfo[0]['username'];
			$message .= "Reservation Information:\r\n\r\nUser Name: ".$userEventInfo[0]['first']." ".$userEventInfo[0]['last'].
					"\r\nDevice Name: ".$userEventInfo[0]['fdname'].
					"\r\nStart: ".$userEventInfo[0]['start'].
					"\r\nEnd:".$userEventInfo[0]['stop'].
					"\r\nDescription:".$userEventInfo[0]['description'].
					"\r\n\r\nCurrently the LSM 710, LSM700, Real Time PCR and Imaris license are heavily used.  Users who fail to keep their appointments or cancel more than three appointments a month with less than 24 hours notice will be charged for future reserved time.".
					"\r\n\r\nWe would appreciate it if everyone would be considerate of other people's time when using the calendars. ";
			mail($to, $subject, $message, $headers);
		
			//Email all administrators of this even happening
			foreach($adminUsers as $id=>$adminUser)
			{	
				$to      = $adminUser['email'];
        	        	$headers = 'From: core-training@igb.illinois.edu' . "\r\n" .
        	        	'Reply-To: core-training@igb.illinois.edu' . "\r\n" .
        	        	'X-Mailer: PHP/' . phpversion();
        	        	mail($to, $subject, $message, $headers);
			}
		}
	}

 	private function CheckEventConflicts($deviceid, $starttime, $finishtime)
	{
		$queryConflicts = "SELECT COUNT(*) FROM event_info WHERE deviceid=".$deviceid." AND DAY(start)=DAY('".$starttime."') AND MONTH(start)=MONTH('".$starttime."') AND YEAR(start)=YEAR('".$starttime."') AND ((TIME_TO_SEC(TIME(start))<TIME_TO_SEC(TIME('".$finishtime."')) AND TIME_TO_SEC(TIME('".$finishtime."'))<TIME_TO_SEC(TIME(stop))) OR (TIME_TO_SEC(TIME(start))<TIME_TO_SEC(TIME('".$starttime."')) AND TIME_TO_SEC(TIME('".$starttime."'))<TIME_TO_SEC(TIME(stop))) OR (TIME_TO_SEC(TIME('".$starttime."'))<=TIME_TO_SEC(TIME(start)) AND TIME_TO_SEC(TIME(stop))<=TIME_TO_SEC(TIME('".$finishtime."'))))";
		$conflicts = $this->sqlDataBase->singleQuery($queryConflicts);
		
		if($conflicts == 0 && strtotime($starttime) < strtotime($finishtime))
		{
			return 1;
		}
		else
		{
			return 0;
		}
		
	}

	private function AuthorizeEventAction($eventID)
	{
		$queryEventUserID = "SELECT userid FROM event_info WHERE ID=".$eventID;
		$userid = $this->sqlDataBase->singleQuery($queryEventUserID);
		if($userid == $this->user->GetID() || $this->user->GetUserTypeID()==1)
		{
			return 1;
		}
		else
		{
			return 0;
		}
		

	}

	public function SetFilterTraining()
	{
		$this->filterTraining=1;
	}
	
}	

?>
