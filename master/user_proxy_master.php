<?php 
require('includes/application_top.php');


if(($_COOKIE['master_id']) && ($_COOKIE['master_permission'])){
	$master_id = $_COOKIE['master_id'];
	$head_master_name = $_COOKIE['master_name'].'様';
}else{
	//$head_master_name = 'ゲスト様';
	tep_redirect('logout.php', '', 'SSL');
}

//▼代理ログイン設定
if(($_GET['memberid'])AND($_GET['zed'] == 'proxy')){

	$memberid = $_GET['memberid'];

	$user_query = tep_db_query("
		SELECT 
			`memberid`,
			`name1`,
			`name2`,
			`bctype`
		FROM `".TABLE_MEM00000."`
		WHERE `memberid` = '".tep_db_input($memberid)."' 
	");
		
	if (!tep_db_num_rows($user_query) ) {
		
		echo "該当するユーザー情報がありません";
		
	} else {
		
		//▼会員情報
		$user = tep_db_fetch_array($user_query);
		$memberid    = $user['memberid'];
		$user_name   = $user['name1'].' '.$user['name2'];
		$user_bctype = $user['bctype'];
		
		//▼ポジション情報
		$query_p = tep_db_query("
			SELECT 
				`position_id` AS `pid`
			FROM `".TABLE_POSITION."`
			WHERE `memberid` = '".tep_db_input($memberid)."' 
			AND   `state`    = '1'
		");
		$p = tep_db_fetch_array($query_p);
		
		
		//▼クッキーを設定する
		tep_cookie_set('user_id'     ,$memberid);		//$_COOKIE['memberid']
		tep_cookie_set('position_id' ,$p['pid']);		//$_COOKIE['position_id']
		
		if($user_bctype){
			tep_cookie_set('bctype' ,$user_bctype);		//$_COOKIE['bctype']
		}
		
		if(trim($user_name)){
			tep_cookie_set('user_name',$user_name);
		}else{
			tep_cookie_set('user_name','未登録者');
		}
		
		tep_redirect('../my/index.php', '', 'SSL');
	}

}else{
	echo "該当するユーザー情報がありません";
}
?>