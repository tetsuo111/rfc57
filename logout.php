<?php 
require('includes/application_top.php');
	
	tep_cookie_del('user_id');				//$_COOKIE['user_id']
	tep_cookie_del('user_name');			//$_COOKIE['user_name']
	tep_cookie_del('user_permission');		//$_COOKIE['user_permission']
	tep_cookie_del('fs_id');				//$_COOKIE['fs_id']
	tep_cookie_del('position_id');			//$_COOKIE['position_id']
	tep_cookie_del('crypted_password');		//$_COOKIE['crypted_password']
	
	//リダイレクト
	tep_redirect('../');
	
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>