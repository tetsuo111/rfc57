<?php
//愛用者登録用(new_registrationにパラメーターで渡して分岐したほうがいいけど、暫定）
require('includes/application_top.php');

if($_COOKIE['user_id']){
	$onLogin = 'login';
}

//▼とび先
$form_action_to = basename($_SERVER['PHP_SELF']);


//▼値の取得
$ivitation_code = ($_POST['ivitation_code'])? $_POST['ivitation_code']:$_GET['fivcd'];
$user_email     = $_POST['user_email'];
$keep_inv       = ($_GET['fivcd'])? 'readonly':'';
$cont_set       = ($_GET['fivcd'])? '?fivcd='.$_GET['fivcd'] :'';


if(($_POST['act'] == 'new')AND($_POST['act_send'])){
	
	//▼メールアドレスの確認
	$query_email = tep_db_query("
		SELECT 
			`memberid`
		FROM `".TABLE_MEM00000."` 
		WHERE `email` = '".tep_db_input($user_email)."'
	");
	
	if(tep_db_num_rows($query_email)){
		
		$end      = '';
		$err_text = '<p class="alert">すでに登録済みのメールアドレスです。</p>';
		$err_text.= '<p class="alert">最初からやり直してください。</p>';
		
	}else{
		
		/*----- 直接の紹介者 -----*/
		//▼紹介者PositionID
		$query_iv = tep_db_query("
			SELECT 
				`position_id`,
				`memberid`
			FROM `".TABLE_POSITION."` 
			WHERE `state` = '1' 
			AND   `position_my_invite_code` = '".tep_db_input($ivitation_code)."'
		");
		
		$pp = tep_db_fetch_array($query_iv);
		$p_inviter = $pp['position_id'];
		$p_invmem  = $pp['memberid'];
		
		
		/*----- 会員番号発行 -----*/
		//▼FSid
		$fs_ar = array(	'date_create' => 'now()','state'       => '1');
		tep_db_perform(TABLE_FS_SETTING,$fs_ar);
		$fs_id = tep_db_insert_id();	//新規会員番号	1000から開始
		
		$mem_id  = $fs_id * 1000;
		$mem_id_ar['memberid'] = $mem_id;
		$fs_set  = "`fs_setting_id`='".tep_db_input($fs_id)."' AND `state`='1'";
		tep_db_perform(TABLE_FS_SETTING,$mem_id_ar,'update',$fs_set);
		
		
		/*----- Member情報 -----*/
		//▼新規会員情報登録
		$mem_ar = array(
			'memberid' => $mem_id,		//会員ID
			'email'    => $user_email,	//メールアドレス
			'chain'    => $p_invmem,		//直接の紹介者
			'bctype' => 21 //愛用者
		);

		//メールテスト時コメントアウト
		tep_db_perform(TABLE_MEM00000,$mem_ar);
		
		
		//▼ログインID
		$new_fs   = $fs_id + 1000;
		$login_id = USER_ID_PREFIX.((strlen($new_fs) < 6)? sprintf('%06d', $new_fs):$new_fs);		//6文字未満は0で埋める
		
		//パスワード8桁
		//▼パスワード
		$pass_word = makeRandStr();							//メール送信用
		$crpt_pass = tep_encrypt_password($pass_word);		//DB登録用
		
		//▼ユーザー情報追加
		$mem_up_ar = array('login_id' => $login_id,'pasword'  => $crpt_pass);
		$w_set     = "`memberid`='".$mem_id."'";
		tep_db_perform(TABLE_MEM00000,$mem_up_ar,'update',$w_set);
		
		
		/*----- Userステータス -----*/
		//▼ユーザーステータスレコードを追加
		$wc_st_ar = array(
			'memberid'    => $mem_id,
			'date_create' => 'now()',
			'state'       => '1'
		);
		
		//▼ユーザーステータス登録
		zDBNewUniqueID(TABLE_USER_WC_STATUS,$wc_st_ar,'user_wc_status_ai_id','user_wc_status_id');
		
		
		/*----- Position登録 -----*/
		//▼登録データ
		$position_ar = array(
			'position_permission' => 'a',
			'memberid'            => $mem_id,
			'position_inviter'    => $p_inviter,	//紹介者ポジションID
			'position_date_regi'  => 'now()',		//ポジション登録日
			'date_create'         => 'now()',
			'state'               => '1'			//メールアドレス確認後に1に変更
		);
		
		//▼ポジション
		$p_id    = zDBNewUniqueID(TABLE_POSITION,$position_ar,'position_ai_id','position_id');
		
		//▼紹介コード作成
		$my_code = MY_CODE_PREFIX.((strlen($p_id) < 6)? sprintf('%06d', $p_id):$p_id);
		
		//▼DB登録
		$ps_up_array = array('position_my_invite_code'=>$my_code,'state'=>'0');		//登録データ
		$w_ps_set    = "`position_id`= '".$p_id."' AND `state`= '1'";				//検索条件
		tep_db_perform(TABLE_POSITION,$ps_up_array,'update',$w_ps_set);				//データ更新
		
		
		/*----- 終了処理 -----*/
		//▼メール内容
		$Einv_code = $ivitation_code;	//紹介者コード
		$Eemail    = $user_email;		//登録Eメール
		$El_id     = $login_id;			//LoginID
		$El_pass   = $pass_word;		//ユーザー送信用
		$El_url    = 'login.php';		//ログイン用URL
		
		//▼メール送信
		Email_Create($EmailHead,$EmailFoot,$Einv_code,$Eemail,$El_id,$El_pass,$El_url);
		
		//▼終了表示
		$end = 'end';
	}

	
}else if(($_POST['act'] == 'new')AND(isset($_POST['act']))){
	
	//▼戻る設定
	if($_POST['act_cancel']){
		$err  = true;
		$err1 = true;
	}else{
		$err  = false;
		$err1 = false;
	}
	
	/*----- エラーチェック -----*/
	//▼入力確認
	if(!$ivitation_code){$err = true;$err_text = '<p class="alert">紹介者コードを入力してください</p>';}
	if(!$user_email)    {$err = true;$err_text = '<p class="alert">メールアドレスを入力してください</p>';}
	
	
	//▼入力内容確認
	if($err == false){
		
		//▼紹介コードの確認
		$query_iv_id = tep_db_query("
			SELECT 
				`memberid`
			FROM `".TABLE_POSITION."` 
			WHERE `state` = '1'
			AND   `position_my_invite_code` = '".tep_db_input($ivitation_code)."'
		");

		//▼メールアドレスの確認
		$query_email = tep_db_query("
			SELECT 
				`memberid`
			FROM `".TABLE_MEM00000."` 
			WHERE `email` = '".tep_db_input($user_email)."'
		");
		
		if (!tep_db_num_rows($query_iv_id)) {
			$err1 = true;
			$err_text = '<p class="alert">無効な紹介者コードです</p>';
		
		}else if(tep_db_num_rows($query_email)){
			$err1 = true;
			$err_text = '<p class="alert">既に登録済みのメールアドレスです</p>';
		}
	}
	
	
	//▼表示設定
	if(($err == false)AND($err1 == false)){
		
		//▼登録ボタン
		$input_button = '<input type="submit" class="btn" name="act_send"   value="confirm">';
		$input_button.= '<input type="submit" class="btn spc10_l" name="act_cancel" value="cancel">';
		
		$keep     = 'readonly';
		$keep_inv = 'readonly';
		
	}else{
		//▼登録ボタン
		$input_button = '<input type="submit" class="btn" value="submit">';
		
		$keep = '';
	}
	
}else{
	
	$input_button = '<input type="submit" class="btn" value="submit">';
}


/*----- 表示フォーム -----*/
if($onLogin){
	$input_form = '<p class="alert">既にログインしています</p>';
	
}else{

	//▼登録フォーム
	if($end == 'end'){
		
		$input_form = '<div class="after_create">';
		$input_form.= '<p>アカウントを新規登録しました。</p>';
		$input_form.= '<p class="spc20">以下のアドレスにログイン情報を送りました。';
		$input_form.= 'メールの内容をご確認の上ログインしてください。</p>';
		$input_form.= '<div class="umail">'.$user_email.'</div>';
		$input_form.= '<p>メールが届かない場合には以下までご連絡ください。</p>';
		$input_form.= '<div class="support_mail">'.SITE_CUSTOMER_EMAIL.'</div>';
		$input_form.= '</div>';
		
	}else{

		//▼自動入力
		$input_auto = '<input type="hidden" name="act" value="new">';
		
		$input_form = $err_text;
		$input_form.= '<form action="'.$form_action_to.$cont_set.'" method="POST">';
		$input_form.= $input_auto;
		$input_form.= '<div class="form-group">';
		$input_form.= '<label for="IvitationCode">紹介者コード</label>';
		$input_form.= '<input type="text"  class="form-control" value="'.$ivitation_code.'" name="ivitation_code" id="IvitationCode" required placeholder="Invitation code" '.$keep_inv.'>';
		$input_form.= '</div>';
		$input_form.= '<div class="form-group">';
		$input_form.= '<label for="UserEmail">連絡用メールアドレス</label>';
		$input_form.= '<input type="email" class="form-control" value="'.$user_email.'"     name="user_email"     id="UserEmail"     required placeholder="Email address" '.$keep.'>';
		$input_form.= '</div>';
		$input_form.= '<div class="submit_area">';
		$input_form.= $input_button;
		$input_form.= '</div>';
		$input_form.= '</form>';
		$input_form.= '<p class="ld_login"><a href="login.php">ログインはこちら</a><p>';
		
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php echo $favicon."\n"; ?>
	<title><?php echo $title;?></title>
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="robots" content="noindex,nofollow,noarchive">
	<meta name="format-detection" content="telephone=no">
	<meta name="format-detection" content="email=no">
	<link rel="stylesheet" type="text/css" href="css/cssreset.css" media="all">
	<link rel="stylesheet" type="text/css" href="css/common.css" media="all">

	<link rel="stylesheet" href="js/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="js/bootstrap/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="css/my.css" media="all">
	
	<script src="../js/jquery-3.2.1.min.js" charset="UTF-8"></script>
	<script src="../js/bootstrap/js/bootstrap.min.js" charset="UTF-8"></script>
	<script src="../js/jquery-migrate-1.4.1.min.js" charset="UTF-8"></script>
	
	<style>
		.ld_login{text-align:right;}
		.after_create p{line-height:120%;}
		.after_create a{text-decoration:none; color:#222;}
		.after_create button{margin-top:20px;}
		
		.umail{margin:20px 0; font-size:24px; color:#F00; font-weight:800;}
		.support_mail{margin:10px 0; font-size:18px; color:#666; font-weight:800;}
	</style>
</head>
<body>
	<div id="wrapper">

		<div id="header">
			<?php require('inc_top_header.php');?>
		</div>

		<div d="contents" class="container">
			<div id="contents_inner">
				
				<div class="login_area row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<h2>新規登録</h2>
						<div class="act_box">
							<?php echo $input_form;?>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div id="footer">
			<?php require('inc_top_footer.php');?>
		</div>
	</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
