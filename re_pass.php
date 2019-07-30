<?php 
require('includes/application_top.php');

//▼とび先
$form_action_to = basename($_SERVER['PHP_SELF']);

//▼データ取得
$login_id = kill_space($_POST['login_id']);
$email    = kill_space($_POST['email']);


//-------- データ処理 --------//
if($_POST['act'] == 'repass' && $_POST['act_send']){
		
		//---- 再発行 -----//
		//▼パスワード
		$pass_word = makeRandStr();							//メール送信用
		$crpt_pass = tep_encrypt_password($pass_word);		//DB登録用
		
		//▼ユーザー情報更新
		$mem_up_ar = array('pasword' => $crpt_pass,'editdate'=>'now()');
		$w_set     = "`login_id`='".tep_db_input($login_id)."'";
		tep_db_perform(TABLE_MEM00000,$mem_up_ar,'update',$w_set);
		
		
		//---- 終了処理 -----//
		//▼メール内容
		$Eemail    = $email;			//登録Eメール
		$El_id     = $login_id;			//LoginID
		$El_pass   = $pass_word;		//ユーザー送信用
		$El_url    = 'login.php';		//ログイン用URL
		
		//▼ログイン情報再発行
		Email_Re_Login($EmailHead,$EmailFoot,$Eemail,$El_id,$El_pass,$El_url);
		
		//▼終了表示
		$end = 'end';
		
}else if(isset($_POST['act']) && ($_POST['act'] == 'repass')){
	
	//▼エラーチェック
	$err = false;
	
	if(!$login_id || !$email){
		$err = true;
		$err_text = '<div class="alert">未入力の項目があります。</div>';
	
	}else{

		$query = tep_db_query("
			SELECT 
				`memberid`,
				`login_id`
			FROM `".TABLE_MEM00000."`
			WHERE `login_id` = '".tep_db_input($login_id)."'
			AND   `email`    = '".tep_db_input($email)."'
		");
		
		if(!tep_db_num_rows($query)){
			
			//▼エラーあり
			$err = true;
			$err_text = '<div class="alert">該当するアカウントがありません</div>';
		}
	}
	
	if($err == false){
		
		//▼エラーなし
		$err_text = '<p class="ok_text">該当するアカウントがあります。';
		$err_text.= 'ログイン情報を再発行します。</p>';
		
		$read         = 'readonly';
		$input_button = '<input type="submit" name="act_send" class="btn" value="ログイン情報再発行">';
		$input_button.= '<a href="" class="btn btn_cancel spc10_l">戻る</a>';
		
	}else{
		
		//▼エラーあり
		$input_button = '<input type="submit" class="btn" value="確認画面">';
	}
	
}else{
	
	//▼初期設定
	$input_button = '<input type="submit" class="btn" value="確認画面">';
}


if($end == 'end'){
	
	$input_form = '<div class="after_create">';
	$input_form.= '<p>ログイン情報を再発行しました。</p>';
	$input_form.= '<p class="spc20">以下のアドレスに新しいログイン情報を送りました。';
	$input_form.= 'メールの内容をご確認の上ログインしてください。</p>';
	$input_form.= '<div class="umail">'.$email.'</div>';
	$input_form.= '<p>メールが届かない場合には以下までご連絡ください。</p>';
	$input_form.= '<div class="support_mail">'.SITE_CUSTOMER_EMAIL.'</div>';
	$input_form.= '</div>';
	
}else{
	
	$input_auto = '<input type="hidden" name="act" value="repass">';

	//▼登録フォーム
	$input_form = $err_text;
	$input_form.= '<form action="'.$form_action_to.'" method="POST">';
	$input_form.= $input_auto;
	$input_form.= '<div class="form-group">';
	$input_form.= '<label for="LoginId">Login ID</label>';
	$input_form.= '<input type="text" class="form-control"  value="'.$login_id.'" name="login_id" id="LoginId" required placeholder="Login ID" '.$read.'>';
	$input_form.= '</div>';
	$input_form.= '<div class="form-group">';
	$input_form.= '<label for="mail">Mail Address</label>';
	$input_form.= '<input type="text" class="form-control"  value="'.$email.'" name="email" id="mail" required placeholder="Mail address" '.$read.'>';
	$input_form.= '</div>';
	$input_form.= '<div class="submit_area">';
	$input_form.= $input_button;
	$input_form.= '</div>';
	$input_form.= '</form>';
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
		.repass{font-size:11px;text-align:right; margin-top:10px;}
		.btn_cancel{background:#D4D4D4;}
		.ok_text   {color:#F00;}
		
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
						<h2>ログイン情報再発行</h2>
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
