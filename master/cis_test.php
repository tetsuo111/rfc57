<?php 
require('includes/application_top.php');

if(($_COOKIE['master_id']) && ($_COOKIE['master_permission']) && (empty($_COOKIE['admin_id']))){
	$master_id = $_COOKIE['master_id'];
	$head_master_name = $_COOKIE['master_name'].'様';
}else{
	//$head_master_name = 'ゲスト様';
	tep_redirect('logout.php', '', 'SSL');
}


//▼POSTでデータ送信
if($_POST['act_send']){
	
	$url = 'https://www.cis-co.tokyo/voucher/';
	$post_data = array('cid' => 'gld');
	
	/*
	//POSTデータ
	$post_data = array('cid' => 'gld');
	$data = http_build_query($post_data, "", "&");	//文字列をURLエンコード

	//header　＞　ヘッダーにPOSTデータを追加
	$header = array(
		"Content-Type: application/x-www-form-urlencoded",
		"Content-Length: ".strlen($data)
	);
	
	//stream　＞さまざまなリソースに共通した手順でアクセスする手段
	//プロトコルを指定してファイルにアクセスする
	//context アクセス条件
	$context = array(
		"http" => array(
			"method"  => "POST",
			"header"  => implode("\r\n", $header),
			"content" => $data
		)
	);
	
	//アクセス条件を指定してファイルに接続
	$rr = file_get_contents($url, false, stream_context_create($context));
	*/
	
	echo cCreateNewCisID();
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
					
					<h2>マスターアカウント</h2>
					<div class="part">
						<form action="" method="POST">
							<input type="submit" name="act_send" value="テスト送信">
						</form>
						
						<div style="margin-top:20px;">
							<a href="">クリア</a>
						</div>
					</div>

				</div>
			</div>
			
			<div class="clear_float"></div>
		</div>
		
		<div id="footer">
			<?php require('inc_master_footer.php'); ?>
		</div>
	</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
