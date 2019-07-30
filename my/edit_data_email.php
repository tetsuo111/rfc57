<?php 
require('includes/application_top.php');

if($_COOKIE['user_id']){
	$user_id   = $_COOKIE['user_id'];
	$head_user_name = $_COOKIE['user_name'].'様';
}else{
	//$head_user_name = 'ゲスト様';
	tep_redirect('../logout.php', '', 'SSL');
}

//▼ユーザー情報伝達
require ('inc_user_announce.php');


$user_query = tep_db_query("
	SELECT `email`
	FROM `".TABLE_MEM00000."` 
	WHERE `memberid` = '".tep_db_input($user_id)."' 
");
$user = tep_db_fetch_array($user_query);
$now_email  = $user['email'];
$new_email  = $_POST['new_email'];

if (isset($_POST['act']) && ($_POST['act'] == 'edit')) {
	$err = false;
	$err_empty = false;

	if(empty($new_email)){
		$err = true; $err_empty = true;
	}else{
		////登録済メールアドレスチェック
		$new_email = mb_convert_kana($new_email, "as");        //全角英数字、スペースを半角に  //http://php.net/manual/ja/function.mb-convert-kana.php
		$new_email = preg_replace('/(\s|　)/','',$new_email);  //スペース削除
		$email_query = tep_db_query("
			SELECT u.`memberid`
			FROM `".TABLE_MEM00000."` u 
			WHERE u.`email` = '".tep_db_input($new_email)."'
		");
		if(!tep_db_num_rows($email_query) ) { //該当なし
		}else{//既存のemail
			$err = true; $err_email = true;
		}
	}
}elseif($_POST['act'] == 'no_err'){
	
	$sql_data_array = array('email' => $new_email);
	tep_db_perform(TABLE_MEM00000, $sql_data_array, 'update', " `memberid` = '".tep_db_input($user_id)."'");
	tep_cookie_set('email',$new_email);    //$_COOKIE['email']
	/*
	//メール送信
	$email = $user_email;
	$reset_url = HTTP_SERVER.'/pass_reset.php?uid='.$user_id.'&email='.$user_email;
	Email_Pass_Reset($EmailHead,$EmailFoot,$email,$reset_url);
	*/
	$announce = '<div class="announce">';
	$announce.= 'メールアドレスを変更しました。<br>';
	$announce.= '</div>';
	$echo_flag = 'announce';
}

//err text
if($err_empty    == true) { $edit_err_text  = '<p class="alert">未入力の項目があります。</p>'; }
if($err_email == true) { $edit_err_text .= '<p class="alert">既に登録のあるメールアドレスです。</p>'; }

if(($_POST['act'] == 'edit') && ($err == false)){
	$item .= '<input type="hidden" name="new_email" value="'.$new_email.'">';             //希望確認
	
	$edit_form  = '<form name="repass" action="edit_data_email.php" method="post">';
	$edit_form .= '<input type="hidden" name="act" value="no_err">';
	$edit_form .= $item;
	$edit_form_ele_text = $edit_err_text;
	$edit_form_ele_1 = '<input class="form-control" type="text" value="'.$now_email.'" disabled="disabled">';             //現在
	$edit_form_ele_2 = '<input class="form-control" type="text" value="'.$new_email.'" disabled="disabled">';             //希望
	$edit_form_ele_submit = '<input type="submit"  class="btn" value="変更する">';
	$edit_form_end  = '</form>';
	
}else{
	$edit_form  = '<form name="repass" action="edit_data_email.php" method="post">';
	$edit_form .= '<input type="hidden" name="act" value="edit">';
	$edit_form_ele_text = $edit_err_text;
	$edit_form_ele_1 = '<input class="form-control" type="text" name="now_email" value="'.$now_email.'" disabled="disabled">';  //現在
	$edit_form_ele_2 = '<input class="form-control" type="text" name="new_email" value="'.$new_email.'">';                      //希望
	$edit_form_ele_submit = '<input type="submit" class="btn" value="確認">';
	$edit_form_end  = '</form>';
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
						<?php echo $edit_form;?>
							<?php echo $edit_form_ele_text; ?>
							<div class="form_group form_area">
								<h3>メールアドレス変更</h3>
								<ul class="form_table">
									<li>
										<div class="form_el row">
											<h4>現在のメールアドレス</h4>
											<div><?php echo $edit_form_ele_1; ?></div>
										</div>
									</li>
									<li>
										<div class="form_el row">
											<h4>ご希望のメールアドレス</h4>
											<div><?php echo $edit_form_ele_2; ?></div>
										</div>
									</li>
								</ul>
							</div>
							<div class="submit_area spc20">
								<?php echo $edit_form_ele_submit;?>
							</div>
						<?php echo $edit_form_end;?>
						<?php } ?>
					</div>
				
				</div>
			</div>
		</div>
	</div>
	
	<div id="footer">
		<?php require('inc_user_footer.php');?>
	</div>
<script src="../js/MyHelper.js" charset="UTF-8"></script>
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
