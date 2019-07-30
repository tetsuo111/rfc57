<?php 
require('includes/application_top.php');

if($_COOKIE['user_id']){
	$user_id = $_COOKIE['user_id'];
	$head_user_name = $_COOKIE['user_name'].'様';
}else{
	//$head_user_name = 'ゲスト様';
	tep_redirect('../logout.php', '', 'SSL');
}

//▼ユーザー情報伝達
require ('inc_user_announce.php');


$now_pass  = $_POST['now_pass'];
$new_pass1 = $_POST['new_pass1'];
$new_pass2 = $_POST['new_pass2'];

if (isset($_POST['act']) && ($_POST['act'] == 'repass')) {
	$err = false;
	$err_empty = false;
	$err_now_pass = false;
	$err_new_pass_num = false;

	if(empty($now_pass) || empty($new_pass1) || empty($new_pass2) ){
		$err = true; $err_empty = true;
	}else{
		////現在パスのチェック
		$user_query = tep_db_query("
			SELECT `pasword`
			FROM `".TABLE_MEM00000."` 
			WHERE `memberid` = '".tep_db_input($user_id)."' 
		");
		if (!tep_db_num_rows($user_query) ) {
			$err = true; $err_now_pass = true;
		}else{
			$user = tep_db_fetch_array($user_query);
			$user_password = $user['pasword'];
			if (!tep_validate_password($now_pass, $user_password)) {
				$err = true; $err_now_pass = true;
			}
		}
		
		////希望パスの確認一致チェック
		if($new_pass1 != $new_pass2){
			$err = true; $err_new_pass = true;
		}
		
		////希望パスの文字数チェック
		if(strlen($new_pass1) < PASSWORD_MIN_LENGTH ){
			$err = true; $err_new_num = true;
		}else{
			//$crypted_password = tep_encrypt_password($join_password);
		}
		
	}
}elseif($_POST['act'] == 'edit_pass'){
	
	$crypted_password = tep_encrypt_password($new_pass1);
	$sql_data_array = array('pasword' => $crypted_password);
	tep_db_perform(TABLE_MEM00000, $sql_data_array, 'update', " `memberid` = '".tep_db_input($user_id)."'");
	
	/*
	//メール送信
	$email = $user_email;
	$reset_url = HTTP_SERVER.'/pass_reset.php?uid='.$user_id.'&email='.$user_email;
	Email_Pass_Reset($EmailHead,$EmailFoot,$email,$reset_url);
	*/
	$announce = '<div class="announce">';
	$announce.= 'パスワードを変更しました。<br>';
	$announce.= '</div>';
	$echo_flag = 'announce';
}

//err text
if($err_empty    == true) { $repass_err_text  = '<p class="alert">未入力の項目があります。</p>'; }
if($err_now_pass == true) { $repass_err_text .= '<p class="alert">現在パスワードが違います。</p>'; }
if($err_new_pass == true) { $repass_err_text .= '<p class="alert">ご希望パスワードが一致していません。</p>'; }
if($err_new_num  == true) { $repass_err_text .= '<p class="alert">パスワードは4文字以上にしてください。</p><br>'; }

if(($_POST['act'] == 'repass') && ($err == false)){
	$item  = '<input type="hidden" name="now_pass"  value="'.$now_pass.'">';               //現在
	$item .= '<input type="hidden" name="new_pass1" value="'.$new_pass1.'">';             //希望
	$item .= '<input type="hidden" name="new_pass2" value="'.$new_pass2.'">';             //希望確認
	
	$repass_form  = '<form name="repass" action="edit_data_pass.php" method="post">';
	$repass_form .= '<input type="hidden" name="act" value="edit_pass">';
	$repass_form .= $item;
	$repass_form_ele_text = $repass_err_text;
	$repass_form_ele_1 = '<input class="form-control" type="password" value="******" disabled="disabled">';             //現在
	$repass_form_ele_2 = '<input class="form-control" type="password" value="******" disabled="disabled">';             //希望
	$repass_form_ele_3 = '<input class="form-control" type="password" value="******" disabled="disabled">';             //希望確認
	$repass_form_ele_submit = '<input type="submit" class="btn" value="変更する">';
	$repass_form_end  = '</form>';
	
}else{
	$repass_form  = '<form name="repass" action="edit_data_pass.php" method="post">';
	$repass_form .= '<input type="hidden" name="act" value="repass">';
	$repass_form_ele_text = $repass_err_text;
	$repass_form_ele_1 = '<input class="form-control" type="password" name="now_pass"  value="'.$now_pass.'">';               //現在
	$repass_form_ele_2 = '<input class="form-control" type="password" name="new_pass1" value="'.$new_pass1.'">';             //希望
	$repass_form_ele_3 = '<input class="form-control" type="password" name="new_pass2" value="'.$new_pass2.'">';             //希望確認
	$repass_form_ele_submit = '<input type="submit" class="btn" value="確認">';
	$repass_form_end  = '</form>';
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type"         content="text/html; charset=<?php echo CHARSET; ?>">
	<meta http-equiv="Content-Style-Type"  content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<meta http-equiv="X-UA-Compatible"      content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php echo $favicon."\n"; ?>
	<title><?php echo $title;?></title>
	<meta name="description"       content="">
	<meta name="keywords"          content="">
	<meta name="robots"            content="noindex,nofollow,noarchive">
	<meta name="format-detection" content="telephone=no">
	<meta name="format-detection" content="email=no">
	<link rel="stylesheet" type="text/css" href="../css/cssreset.css" media="all">
	<link rel="stylesheet" type="text/css" href="../css/common.css"   media="all">
	<link rel="stylesheet" type="text/css" href="../js/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="../js/bootstrap/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="../css/my.css"       media="all">
	
	<script src="../js/jquery-3.2.1.min.js"            charset="UTF-8"></script>
	<script src="../js/bootstrap/js/bootstrap.min.js" charset="UTF-8"></script>
	
</head>
<body>
<div id="wrapper">
	
	<div id="header">
		<?php require('inc_user_header.php');?>
	</div>
	
	
	<div class="container-fluid">
		<div id="content" class="row">
			
			<div id="left1" class="col-md-4 col-lg-2">
				<div class="inner">
					<div class="u_menu_area">
						<?php require('inc_user_left.php'); ?>
					</div>
				</div>
			</div>
			
			<div id="left2" class="col-xs-12 col-md-12 col-md-8 col-lg-10">
				<div class="inner">

					<?php echo $warning; ?>
					
					<div class="area1">
							<?php 
								if($echo_flag == 'announce'){ 
									echo $announce; 
								}else{
							?>
							<?php echo $repass_form;?>
								<?php echo $repass_form_ele_text;?>
								<div class="form_group form_area">
									<h3>パスワード変更</h3>
									<ul class="form_table">
										<li>
											<div class="form_el row">
												<h4>現在のパスワード</h4>
												<div><?php echo $repass_form_ele_1; ?></div>
											</div>
										</li>
										<li>
											<div class="form_el row">
												<h4>ご希望のパスワード</h4>
												<div><?php echo $repass_form_ele_2; ?></div>
											</div>
										</li>
										<li>
											<div class="form_el row">
												<h4>ご希望パスワードの確認</h4>
												<div><?php echo $repass_form_ele_3; ?></div>
											</div>
										</li>
									</ul>
								</div>
								<div class="submit_area spc20">
									<?php echo $repass_form_ele_submit;?>
								</div>
							<?php echo $repass_form_end;?>
						<?php } ;?>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div id="footer">
		<?php require('inc_user_footer.php');?>
	</div>
</div>
<script src="../js/MyHelper.js" charset="UTF-8"></script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>