<?php 
require('includes/application_top.php');

////logout
tep_cookie_del('user_id');          //$_COOKIE['user_id']
tep_cookie_del('user_name');        //$_COOKIE['user_name']
tep_cookie_del('user_email');       //$_COOKIE['user_email']
tep_cookie_del('user_ripple');      //$_COOKIE['user_ripple']
tep_cookie_del('user_permission');  //$_COOKIE['user_permission']
tep_cookie_del('crypted_password'); //$_COOKIE['crypted_password']

$ee_id = $_GET['ee_id'];
$email = $_GET['email'];
if($ee_id && $email){
	
	$edit_query = tep_db_query("
		SELECT 
			`user_edit_email_id`
		FROM `user_edit_email` 
		WHERE `state` = '1' 
		AND `user_edit_email_id` = '".tep_db_input($ee_id)."' 
		AND `user_email`   = '".tep_db_input($email)."' 
	");
	if (tep_db_num_rows($edit_query) ) {
		$edit = tep_db_fetch_array($edit_query);
		
		$user_edit_email_id = $edit['user_edit_email_id'];
		////変更完了したデータ
		$sql_data_array = array(
			'date_update' => 'now()',
			'state'       => 'a'
		);
		tep_db_perform('user_edit_email', $sql_data_array, 'update', " `user_edit_email_id` = '".tep_db_input($user_edit_email_id)."'");
		
		tep_redirect(HTTP_SERVER.'/'.IOU_L.'/login.php', '', 'SSL');
	}else{
		tep_redirect(HTTP_SERVER.'/404.php', '', 'SSL');
	}

}else{
	tep_redirect(HTTP_SERVER.'/404.php', '', 'SSL');
}

require(DIR_WS_INCLUDES . 'application_bottom.php');
?>