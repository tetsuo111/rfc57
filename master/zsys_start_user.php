<?php 
require('includes/application_top.php');

if(($_COOKIE['master_id']) && ($_COOKIE['master_permission'])){
	$master_id = $_COOKIE['master_id'];
	$head_master_name = $_COOKIE['master_name'].'様';
}else{
	//$head_master_name = 'ゲスト様';
	tep_redirect('logout.php', '', 'SSL');
}

//▼とび先設定
$form_action_to = basename($_SERVER['PHP_SELF']);

$disabled = 'disabled';

//▼初期ユーザー設定
if($_POST['act'] == 'process'){

	/*----- FS登録 -----*/
	//▼FSid作成
	$fs_array = array(
		'date_create' => 'now()',
		'state'       => '1'
	);
	
	//▼FS登録用
	$ai_fs  = zDBNewUniqueID(TABLE_FS_SETTING,$fs_array,'fs_setting_ai_id','fs_setting_id');	//追加したID
	$new_fs = $ai_fs + 1000;																	//1000からスタート
	$fs_id  = USER_ID_PREFIX.((strlen($new_fs) < 6)? sprintf('%06d', $new_fs):$new_fs);			//6文字未満は0で埋める
	
	
	/*----- User登録 -----*/
	//▼パスワード
	//$pass_word = makeRandStr();							//メール送信用
	$pass_word = '12345';
	$crpt_pass = tep_encrypt_password($pass_word);			//DB登録用
	
	//▼ユーザー情報
	$user_ar = array(
		'user_permission' => 's',
		'fs_id'           => $fs_id,
		'user_email'      => $user_email,
		'user_password'   => $crpt_pass,
		'date_create'     => 'now()',
		'state'           => '1'
	);
	
	//▼DB登録
	$user_id  = zDBNewUniqueID(TABLE_USER,$user_ar,'user_ai_id','user_id');
	
	
	//▼ユーザーステータスレコードを追加
	$wc_st_ar = array(
		'user_id'     => $user_id,
		'date_create' => 'now()',
		'state'       => '1'
	);
	
	//▼ユーザーステータスを事前に作成
	zDBNewUniqueID(TABLE_USER_WC_STATUS,$wc_st_ar,'user_wc_status_ai_id','user_wc_status_id');
	
	
	/*----- FS更新 -----*/
	//▼FSID更新
	$fs_up_array = array(
		'user_id' => $user_id,
		'fs_id'   => $fs_id
	);
	
	$w_fs_set = "`fs_setting_ai_id`='".tep_db_input($ai_fs)."' AND `state`='1'";
	tep_db_perform(TABLE_FS_SETTING,$fs_up_array,'update',$w_fs_set);
	
	
	/*----- Position登録 -----*/
	//▼紹介者ポジション
	$p_inviter = 'null';
	
	//▼登録データ
	$position_ar = array(
		'position_permission'  => 'a',
		'user_id'              => $user_id,
		'fs_id'                => $fs_id,
		'position_inviter'     => $p_inviter,
		'position_date_regi'   => 'now()',		//ポジション登録日
		'position_condition'   => 'a',
		'position_date_active' => 'now()',
		'date_create'          => 'now()',
		'state'                => '1'
	);
	
	
	//▼ポジション
	$p_id  = zDBNewUniqueID(TABLE_POSITION,$position_ar,'position_ai_id','position_id');
	
	//▼紹介コード作成
	$my_code     = MY_CODE_PREFIX.((strlen($p_id) < 6)? sprintf('%06d', $p_id):$p_id);
	
	//▼DB登録
	$ps_up_array = array('position_my_invite_code'=>$my_code);						//登録データ
	$w_ps_set    = "`position_id`='".tep_db_input($p_id)."' AND `state`='1'";		//検索条件
	tep_db_perform(TABLE_POSITION,$ps_up_array,'update',$w_ps_set);					//データ更新
	
	
	/*----- ユニポジション発行 -----*/
	//▼登録用配列
	$uni_array = array(
		'position_id'          => $p_id,
		'p_uni_level_absolute' => '0',
		'p_uni_level_up_list'  => '-'.$p_id.'-',
		'date_create'          => 'now()',
		'state'                => '1'
	);
	
	//▼登録DB
	$db_table = TABLE_P_UNI_LEVEL;
	zDBNewUniqueID($db_table,$uni_array,'p_uni_level_ai_id','p_uni_level_id');
	
	
	$end = 'end';
}


//▼登録確認
if(zCheckUserReg(TABLE_POSITION,'1')){
	
	$query_position =  tep_db_query("
		SELECT
			`user_id`,
			`fs_id`,
			`position_my_invite_code` AS `ivcode`
		FROM `".TABLE_POSITION."`
		WHERE `user_id` = '1'
		AND   `state`   = '1'
	");
	
	$b = tep_db_fetch_array($query_position);
	
	$start_data = '<table class="input_list">';
	$start_data.= '<tr><th>ユーザーID</th><td>'.$b['user_id'].'</td></tr>';
	$start_data.= '<tr><th>会員番号</th><td>'.$b['fs_id'].'</td></tr>';
	$start_data.= '<tr><th>紹介コード</th><td>'.$b['ivcode'].'</td></tr>';
	$start_data.= '</table>';

}else{
	$disabled = '';
}

if($end == 'end'){
	$input_form = '<p>登録しました</p>';
}else{
	//▼登録フォーム
	$input_form = '<form action="'.$form_action_to.'" method="POST">';
	$input_form.= '<input type="hidden" name="act" value="process">';
	$input_form.= '<input type="submit" value="開始ユーザーを登録する" '.$disabled.'>';
	$input_form.= '</form>';
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<?php echo $favicon."\n"; ?>
	<title><?php echo $title;?></title>
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="robots" content="noindex,nofollow,noarchive">
	<meta name="format-detection" content="telephone=no">
	<meta name="format-detection" content="email=no">
	<link rel="stylesheet" type="text/css" href="../css/cssreset.css" media="all">
	<link rel="stylesheet" type="text/css" href="../css/common.css" media="all">
	<link rel="stylesheet" type="text/css" href="../css/master.css" media="all">
</head>
<body id="body">
<div id="wrapper">
	
	<div id="header">
		<?php require('inc_master_header.php');?>
	</div>
	<div id="head_line">
		<?php require('inc_master_head_line.php');?>
	</div>
	
	<div id="content">
		<div class="content_outer">
			<div id="left1">
				<div class="inner">
					<?php require('inc_master_left.php'); ?>
				</div>
			</div>
		
		<div id="left2">
			<div class="inner">
			
				<div class="admin_menu">
					<?php require('inc_master_menu.php');?>
				</div>
				
				<h2>開始ユーザー登録</h2>
				<div class="area1">
					<?php echo $input_form;?>
				</div>
				<div class="spc20">
					<h3>開始ユーザー</h3>
					<?php echo $start_data;?>
				</div>

			</div>
		</div>
	</div>
	
	<div id="footer">
		<?php require('inc_master_footer.php'); ?>
	</div>
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
