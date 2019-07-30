<?php 
require('includes/application_top.php');

if(($_COOKIE['master_id']) && ($_COOKIE['master_permission'])){
	tep_redirect('logout.php', '', 'SSL');
	
}else{
	$head_master_name = '';
}

$form_action_to = basename($_SERVER['PHP_SELF']);

if (isset($_POST['act']) && ($_POST['act'] == 'login')) {
	
	$login_email    = $_POST['login_email'];
	$login_password = $_POST['login_password'];

	if(empty($login_email) || empty($login_password)){
		$login_err_text = '<span class="err">未入力の項目があります。</span>';
	}else{
		$master_query = tep_db_query("
			SELECT 
				`master_id`, 
				`master_permission`, 
				`master_name`, 
				`master_email`, 
				`master_password` 
			FROM `".TABLE_MASTER."`
			WHERE `state` = '1' 
			AND `master_email` = '".tep_db_input($login_email)."' 
		");
		
		if (!tep_db_num_rows($master_query) ) {
			$login_err_text = '<span class="err">IDまたはパスワードが違います。</span>';
		} else {
			$master = tep_db_fetch_array($master_query);
			
			$master_id         = $master['master_id'];
			$master_permission = $master['master_permission'];
			$master_name       = $master['master_name'];
			$master_email      = $master['master_email'];
			$master_password   = $master['master_password'];

			if (!tep_validate_password($login_password, $master_password)) {
				$login_err_text = '<span class="err">IDまたはパスワードが違います。</span>';
			} else {
				//クッキーを設定する
				tep_cookie_set('master_id',$master_id);                  //$_COOKIE['master_id']
				tep_cookie_set('master_permission',$master_permission);  //$_COOKIE['master_permission']
				tep_cookie_set('master_name',$master_name);              //$_COOKIE['master_name']
				
				tep_redirect('../master', '', 'SSL');
			}
		}
	}
}

$input_auto = '<input type="hidden" name="act" value="login">';
$login_form_ele_text = $login_err_text;
$login_form_ele_1 = '<input class="input_text" type="text" name="login_email" value="'.$login_email.'">';             //email
$login_form_ele_2 = '<input class="input_text" type="password" name="login_password" value="'. $login_password.'">';  //pass
$login_form_ele_submit = '<input type="submit" value="ログイン">';


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
	<style>
	</style>
</head>
<body id="body">
<div id="wrapper">
	
	<div class="master_login">
		<div class="login_inner">
			<h2>マスター　ログイン</h2>
			<div>
				<form name="login" action="<?php echo $form_action_to;?>" method="post">
					<?php echo $input_auto;?>
					<table class="form_table">
						<tr>
							<th></th>
							<td><?php echo $login_form_ele_text; ?></td>
						</tr>
						<tr>
							<th>メールアドレス</th>
							<td><?php echo $login_form_ele_1; ?></td>
						</tr>
						<tr>
							<th>パスワード</th>
							<td><?php echo $login_form_ele_2; ?></td>
						</tr>
						<tr>
							<td></td>
							<td><?php echo $login_form_ele_submit; ?></td>
						</tr>
					</table>
				</form>
			</div>
		</div>
	</div>
	
	<div id="footer">
		<?php require('../admin/inc_master_footer.php'); ?>
	</div>
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
