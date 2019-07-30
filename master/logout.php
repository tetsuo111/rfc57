<?php 
require('includes/application_top.php');
	//master
	tep_cookie_del('master_id');          //$_COOKIE['master_id']
	tep_cookie_del('master_name');        //$_COOKIE['master_name']
	tep_cookie_del('master_permission');  //$_COOKIE['master_permission']
	
	//agent
	tep_cookie_del('admin_id');  		 //$_COOKIE['admin_id']
	tep_cookie_del('admin_permission');  //$_COOKIE['admin_permission']
	tep_cookie_del('admin_name');        //$_COOKIE['admin_name']
	tep_cookie_del('agent_id');          //$_COOKIE['agent_id']
	tep_cookie_del('agent_permission');  //$_COOKIE['agent_permission']
	
	//user
	tep_cookie_del('user_id');          //$_COOKIE['user_id']
	tep_cookie_del('user_name');        //$_COOKIE['user_name']
	tep_cookie_del('user_email');       //$_COOKIE['user_email']
	tep_cookie_del('user_ripple');      //$_COOKIE['user_ripple']
	tep_cookie_del('crypted_password'); //$_COOKIE['crypted_password']
	
	tep_redirect('master_login.php', '', 'SSL');
	
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>