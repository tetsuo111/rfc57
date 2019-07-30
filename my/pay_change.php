<?php 
require('includes/application_top.php');

if($_COOKIE['user_id']){
	$user_id        = $_COOKIE['user_id'];
	$position_id    = $_COOKIE['position_id'];
	$head_user_name = $_COOKIE['user_name'].'様';
}else{
	//$head_user_name = 'ゲスト様';
	tep_redirect('../logout.php', '', 'SSL');
}


//▼戻り先
$link_back = 'order_regular.php';

//▼カード情報を確認
$ok_card = false;
$query = tep_db_query("
	SELECT
		`login_id`,		-- クレジットid
		`memo1`			-- クレジットpass
	FROM  `".TABLE_MEM00000."`
	WHERE `memberid`   = '".tep_db_input($user_id)."'
");

if($a = tep_db_fetch_array($query)){
	if($a['login_id'] && $a['memo1']){$ok_card = true;}
}


//▼表示フォーム
$btn_back = '<a href="'.$link_back.'"><button type="button" class="btn btn_back spc10_l">定期購入に戻る</button></a>';

if($ok_card){
	
	//▼カード送信情報
	$site_id_m   = zGetSysSetting('sys_cm_site_id');		//月毎決済ID
	$site_pass_m = zGetSysSetting('sys_cm_site_pass');		//月毎決済Pass
	$c_id        = $a['login_id'];
	$c_pass      = $a['memo1'];
	$c_url       = 'https://payment.alij.ne.jp/service/continue/change';
	
	//▼表示フォーム
	$card_in = '<table class="table list_table"><tbody>';
	$card_in.= '<tr><th>顧客ID</th><td>'.$c_id.'</td></tr>';
	$card_in.= '</tbody></table>';
	
	//▼変更ボタン
	$change_card = '<form  action="'.$c_url.'" method="POST" class="spc10">';
	$change_card.= '<input type="hidden" name="SiteId"       value="'.$site_id_m.'">';
	$change_card.= '<input type="hidden" name="SitePass"     value="'.$site_pass_m.'">';
	$change_card.= '<input type="hidden" name="CustomerId"   value="'.$c_id.'">';
	$change_card.= '<input type="hidden" name="CustomerPass" value="'.$c_pass.'">';
	$change_card.= '<input type="submit" class="btn"         value="カード情報を変更する">';
	$change_card.= $btn_back;
	$change_card.= '</form>';
	
}else{
	
	$change_card = '<p class="alert">カード情報が登録されていません</p>';
	$change_card.= $btn_back;
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
	<style>
		.list_table{max-width:600px; border-top:1px solid #E4E4E4;}
		.btn_back{color:#333;}
	</style>
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
						<div class="part">
							<div class="spc20">
								<?php echo $card_in;?>
							</div>
							<div class="spc20">
								<?php echo $change_card;?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script src="../js/MyHelper.js" charset="UTF-8"></script>
		
		<div id="footer">
			<?php require('inc_user_footer.php');?>
		</div>
	</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
