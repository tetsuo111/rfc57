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

//▼ボタン利用
$disabled = 'disabled';


//---------------データ処理---------------
//▼データ登録
if($_POST['act_init']) {

	$end_text = '';
	foreach($_POST as $k => $p){
		if($k!='act_init'){
			if(preg_match('/^mem/',$k) || $k=='position' || $k=='fs_setting' || $k=='user_wc_status') {
				$query = "DELETE FROM $k WHERE memberid<>1000;";

				tep_db_query($query);
			}elseif($k=='a_doc') {
				tep_db_trancate($k);

				$query = "DELETE FROM tag00000 WHERE article_type=2;";

				tep_db_query($query);

			}elseif($k=='a_event') {
				tep_db_trancate($k);

				$query = "DELETE FROM tag00000 WHERE article_type=3;";

				tep_db_query($query);

			}elseif($k=='a_qanda') {
				tep_db_trancate($k);

				$query = "DELETE FROM tag00000 WHERE article_type=1;";

				tep_db_query($query);

			}else{

				tep_db_trancate($k);
			}


			$end_text .= $k.'をクリアしました<br>';

		}
	}


	$end_reg = 'end';
}

//---------------初期設定---------------
//▼テーブルのリスト
$query_sys = tep_db_query("SHOW TABLES;");


$input_form_in = '';
while($ds = tep_db_fetch_array($query_sys)){

	//▼設定テーブル
	switch ($ds['Tables_in_'.DB_DATABASE]){
		case 'master';
		case 'zsys_setting';
			$input_form_in.= '<tr><td>クリアできません</td><td>'.$ds['Tables_in_'.DB_DATABASE].'</td></tr>';
			break;
		case 'view_charge';
			break;
		default;
			$input_form_in.= '<tr><td><input type="checkbox" name="'.$ds['Tables_in_'.DB_DATABASE].'"></td><td>'.$ds['Tables_in_'.DB_DATABASE].'</td></tr>';
	}


	
	
}

$input_button.= '<input onclick="confirm(\'本当にクリアしますか？\');" type="submit" name="act_init" value="クリア">';

//---------------表示フォーム---------------

//▼表示フォーム
if($end_reg == "end"){

	$input_form = '<p>'.$end_text.'</p>';
	$input_form.= '<a href="'.$form_action_to.'">変更を続ける</a>';

}else{

	//▼申込フォーム
	$input_form = '<form action="'.$form_action_to.'" method="POST">';
	$input_form.= $input_auto;
	$input_form.= '<table class="list_table">';
	$input_form.= '<tr><th width="10%">選択</th><th>テーブル名</th></tr>';
	$input_form.= $input_form_in;
	$input_form.= '</table>';
	$input_form.= '<div class="spc10">';
	$input_form.= $input_button.$err_text;
	$input_form.= '</div>';
	$input_form.= '</form>';
}


//▼システムリスト
//$input_list = '<table class="list_table">';
//$input_list.= '<tr><th style="width:150px;">データ名</th><th style="width:70px;">データ種類</th><th style="width:70px;">値</th><th>説明</th><th>操作</th></tr>';
//$input_list.= $list_in_tr;
//$input_list.= '</table>';


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
	<script src="../js/jquery-3.2.1.min.js" charset="UTF-8"></script>

	<script>
		$(function() {
			$('input[name*="mem"]').click(function(){
				if($(this).prop('checked')){
					$('input[name*="mem"]').prop('checked',true);
					$('input[name*="position"]').prop('checked',true);
				}else{
					$('input[name*="mem"]').prop('checked',false);
					$('input[name*="position"]').prop('checked',false);
				}

			});

			$('input[name*="odr"]').click(function(){
				if($(this).prop('checked')){
					$('input[name*="odr"]').prop('checked',true);

				}else{
					$('input[name*="odr"]').prop('checked',false);

				}

			});

			$('input[name*="qanda"]').click(function(){
				if($(this).prop('checked')){
					$('input[name*="qanda"]').prop('checked',true);

				}else{
					$('input[name*="qanda"]').prop('checked',false);

				}
			});

			$('input[name*="news"]').click(function(){
				if($(this).prop('checked')){
					$('input[name*="news"]').prop('checked',true);

				}else{
					$('input[name*="news"]').prop('checked',false);

				}
			});

			$('input[name*="m_payment"]').click(function(){
				if($(this).prop('checked')){
					$('input[name*="m_payment"]').prop('checked',true);

				}else{
					$('input[name*="m_payment"]').prop('checked',false);

				}
			});

			$('input[name*="m_plan"]').click(function(){
				if($(this).prop('checked')){
					$('input[name*="m_plan"]').prop('checked',true);
					$('input[name="item00000"]').prop('checked',true);

				}else{
					$('input[name*="m_plan"]').prop('checked',false);
					$('input[name="item00000"]').prop('checked',false);
				}
			});
			$('input[name="item00000"]').click(function(){
				if($(this).prop('checked')){
					$('input[name*="m_plan"]').prop('checked',true);


				}else{
					$('input[name*="m_plan"]').prop('checked',false);

				}
			});

			$('input[name="m_rank"]').click(function(){
				if($(this).prop('checked')){
					$('input[name="typ01004"]').prop('checked',true);

				}else{
					$('input[name="typ01004"]').prop('checked',false);

				}
			});

			$('input[name="typ01004"]').click(function(){
				if($(this).prop('checked')){
					$('input[name="m_rank"]').prop('checked',true);

				}else{
					$('input[name="m_rank"]').prop('checked',false);

				}
			});

		});
	</script>
	<style>
		.value{text-align:right;}
		.input_v{padding:5px; width:60px;}
	</style>

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
						
						<h2>システム設定</h2>
						
						<div class="part">
							<div>
								<?php echo $input_form;?>
							</div>
							
<!--							<div class="spc50">-->
<!--								--><?php //echo $input_list;?>
<!--							</div>-->
						</div>

					</div>
				</div>
				
				<div class="clear_float"></div>
			</div>
		</div>
		
		
		<div id="footer">
			<?php require('inc_master_footer.php'); ?>
		</div>
	</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>