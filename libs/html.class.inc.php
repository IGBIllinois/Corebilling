<?php

class html {
	public static function error_message($msg,$title=""){
		$alert = '<div class="alert alert-danger">';
		if($title != ""){
			$alert .= '<h3 class="alert-heading">'.$title.'</h3>';
		}
		$alert .= $msg.'</div>';
		return $alert;
	}
	public static function success_message($msg, $title=""){
		$alert = '<div class="alert alert-success">';
		if($title != ""){
			$alert .= '<h3 class="alert-heading">'.$title.'</h3>';
		}
		$alert .= $msg.'</div>';
		return $alert;
	}

	public static function get_url_navigation($url,$start_date,$end_date,$get_array = array()) {
	        $previous_end_date = date('Ymd',strtotime('-1 second', strtotime($start_date)));
        	$previous_start_date = substr($previous_end_date,0,4) . substr($previous_end_date,4,2) . "01";
	        $next_start_date = date('Ymd',strtotime('+1 day', strtotime($end_date)));
        	$next_end_date = date('Ymd',strtotime('-1 second',strtotime('+1 month',strtotime($next_start_date))));
	        $next_get_array = array_merge(array('start_date'=>$next_start_date,'end_date'=>$next_end_date),$get_array);
        	$previous_get_array = array_merge(array('start_date'=>$previous_start_date,'end_date'=>$previous_end_date),$get_array);
	        $back_url = $_SERVER['PHP_SELF'] . "?" . http_build_query($previous_get_array);
        	$forward_url = $_SERVER['PHP_SELF'] . "?" . http_build_query($next_get_array);
	        return array('back_url'=>$back_url,'forward_url'=>$forward_url);

	}
}
