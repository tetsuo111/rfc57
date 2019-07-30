<?php 
require('includes/application_top.php');


//▼とび先
$form_action_to = basename($_SERVER['PHP_SELF']);
$link_remind    = 're_pass.php';

$login_id = $_GET['yl_id'];
$keep     = ($_GET['yl_id'])? 'readonly':'';

if (isset($_POST['act']) && ($_POST['act'] == 'login')) {
	
	$login_id   = kill_space($_POST['login_id']);
	$login_pass = kill_space($_POST['login_pass']);

	if(empty($login_id) || empty($login_pass)){
		$login_err_text = '<div class="alert">未入力の項目があります。</div>';
	
	}else{

		$user_query = tep_db_query("
			SELECT 
				`memberid`,
				IFNULL(`name1`,`name2`) AS `name`,
				`pasword`,
				`bctype`
			FROM `".TABLE_MEM00000."`
			WHERE `login_id` = '".tep_db_input($login_id)."'
		");
		
		if (!tep_db_num_rows($user_query) ) {

			$login_err_text = '<div class="alert">IDまたはパスワードが違います。</div>';
			
		} else {
			
			//▼ユーザー情報を取得
			$user = tep_db_fetch_array($user_query);
			$user_id       = $user['memberid'];
			$user_password = $user['pasword'];
			$user_bctype   = $user['bctype'];
			
			if (!tep_validate_password($login_pass, $user_password)) {
				$login_err_text = '<div class="err">IDまたはパスワードが違います。</div>';
				
			} else {
				
				//ユーザー情報を設定
				if($user['name']){
					$user_name = $user['name'];
				}else{
					$user_name = 'New User';
				}
				
				//ポジションIDを取得
				$position_query = tep_db_query("
					SELECT 
						`position_id`  AS `pid`
					FROM `".TABLE_POSITION."` 
					WHERE `state`   = '1' 
					AND   `memberid` = '".tep_db_input($user_id)."' 
				");
				$p = tep_db_fetch_array($position_query);
				
				
				//クッキーを設定する
				tep_cookie_set('user_id'     ,$user_id);			//$_COOKIE['user_id']
				tep_cookie_set('user_name'   ,$user_name);			//$_COOKIE['user_name']
				tep_cookie_set('position_id' ,$p['pid']);			//$_COOKIE['position_id']
				
				//会員区分を追加
				if($user_bctype){
					tep_cookie_set('bctype'  ,$user_bctype);		//$_COOKIE['position_id']
				}
				
				
				//▼初回ログイン日を登録
				if(!$user['inputdate']){
					//登録データ
					$done_ar['inputdate'] = 'now()';
					$w_set = "`memberid`='".tep_db_input($user_id)."'";
					tep_db_perform(TABLE_MEM00000,$done_ar,'update',$w_set);
					
					
					//ポジションステータスを更新
					$p_up_ar = array('date_update'=>'now()','state'=>'1');
					$wp_set  = "`memberid`='".tep_db_input($user_id)."' AND `state`='0'";
					tep_db_perform(TABLE_POSITION,$p_up_ar,'update',$wp_set);
					
					//登録画面
					tep_redirect('./my/edit_user_info.php', '', 'SSL');
				}else{
					
					//▼トップ画面
					tep_redirect('./my', '', 'SSL');
				}
			}
		}
	}
}


$input_auto = '<input type="hidden" name="act" value="login">';

//▼登録フォーム
$input_form = $login_err_text;
$input_form.= '<form action="'.$form_action_to.'" method="POST">';
$input_form.= $input_auto;
$input_form.= '<div class="form-group">';
$input_form.= '<label for="LoginId">Login ID</label>';
$input_form.= '<input type="text" class="form-control"    value="'.$login_id.'" name="login_id" id="LoginId" required placeholder="Login ID" '.$keep.'>';
$input_form.= '</div>';
$input_form.= '<div class="form-group">';
$input_form.= '<label for="Password">Password</label>';
$input_form.= '<input type="password" class="form-control" value="'.$login_pass.'" name="login_pass" id="Password" required placeholder="Password">';
$input_form.= '</div>';
$input_form.= '<div class="submit_area">';
$input_form.= '<button type="submit" class="btn">submit</button>';
$input_form.= '<p class="repass"><a href="'.$link_remind.'">パスワードを忘れた方</a></p>';
$input_form.= '</div>';
$input_form.= '</form>';

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

	<script>
		function e_pass(pass){
			type = document.getElementById(pass).type;
			if(type == 'password'){
				document.getElementById(pass).type = 'text';
			}else{
				document.getElementById(pass).type = 'password';
			}
		}
	</script>
	<style>
		.repass{font-size:11px;text-align:right; margin-top:10px;}
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
						<h2>ログイン</h2>
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
