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
if(($_POST['act'] == "process")AND(empty($_POST["act_clear"]))){
	
	//▼エラーチェック
	$err = false;
	
	if(!isset($_POST['zsys_setting_value'])){
		$err = true;
		$err_text = '<span class="err">値が入力されていません</span>';
	}
	
	//▼データ登録
	if($err == false){
		
		//▼登録用データ
		$query = tep_db_query("
			SELECT
				*
			FROM `".TABLE_ZSYS_SETTING."`
			WHERE `state` = '1'
			AND `zsys_setting_paramater` = '".tep_db_input($_POST['zsys_setting_paramater'])."'
		");
		
		//▼データ取得
		$d = tep_db_fetch_array($query);
		
		
		//▼データ変更
		$d['zsys_setting_value'] = $_POST['zsys_setting_value'];
		
		$tmp = $d;
		unset($tmp['zsys_setting_ai_id']);
		unset($tmp['date_update']);
		
		//▼登録用設定
		$data_arry = $tmp;
		$old_array = array('date_update'=>'now()','state'=>'y');
		$w_set     = "`state` = '1' AND `zsys_setting_paramater` = '".tep_db_input($_POST['zsys_setting_paramater'])."'";
		$db_table  = TABLE_ZSYS_SETTING;
		
		//▼更新実行
		tep_db_perform($db_table,$old_array,'update',$w_set);	//過去のデータを変更
		tep_db_perform($db_table,$data_arry);					//新規登録


		//▼終了処理
		$end_reg = "end";
		$end_text = 'システムの設定を変更しました';
	}
}

//---------------初期設定---------------
//▼システム設定
$query_sys = tep_db_query("
	SELECT
		`zsys_setting_paramater` AS `paramater`,
		`zsys_setting_type`      AS `type`,
		`zsys_setting_exp`       AS `exp`,
		`zsys_setting_item`      AS `item`,
		`zsys_setting_value`     AS `value`
	FROM `".TABLE_ZSYS_SETTING."`
	WHERE `state` = '1'
	ORDER BY `zsys_setting_paramater` ASC
");

//▼種類配列
$SysTypeArray = array('num'=>'数値','chr'=>'文字列');


while($ds = tep_db_fetch_array($query_sys)){
	
	//▼操作
	$operation = '<a href="'.$form_action_to.'?parm='.$ds["paramater"].'">この数値を変更する</a>';
	
	$value = "";
	
	//▼値の表示
	if($ds["paramater"] == 'sys_consum_tax_use'){
		//▼消費税表示
		$value = $ConsumTaxArray[$ds["value"]];
	}else{
		$value = $ds["value"];
	}
	
	//▼設定テーブル
	$list_in_tr.= '<tr><td>'.$ds["item"].'</td><td>'.$SysTypeArray[$ds["type"]].'</td><td class="value">'.$value.'</td><td>'.$ds["exp"].'</td><td>'.$operation.'</td></tr>';
	
	
	//--------------値変更--------------
	//▼登録フォーム用
	if($_GET['parm'] == $ds['paramater']){
		
		//▼入力要素
		$input_v = '<input type="text" class="input_v" name="zsys_setting_value" value="'.$ds["value"].'">';
		
		$input_auto = '<input type="hidden" name="act" value="process">';
		$input_auto.= '<input type="hidden" name="zsys_setting_paramater" value="'.$ds['paramater'].'">';
		
		$input_form_in = '<tr><td>'.$ds["item"].'</td><td>'.$SysTypeArray[$ds["type"]].'</td><td class="value">'.$input_v.'</td><td>'.$ds["exp"].'</td></tr>';

		$disabled = "";
	}
}


$input_button = '<input type="submit" name="act_send" value="この数値に変更する" '.$disabled.'>';
$input_button.= '<input type="submit" name="act_clear" value="クリア">';

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
	$input_form.= '<tr><th style="width:150px;">データ名</th><th style="width:70px;">データ種類</th><th style="width:70px;">値</th><th>説明</th></tr>';
	$input_form.= $input_form_in;
	$input_form.= '</table>';
	$input_form.= '<div class="spc10">';
	$input_form.= $input_button.$err_text;
	$input_form.= '</div>';
	$input_form.= '</form>';
}


//▼システムリスト
$input_list = '<table class="list_table">';
$input_list.= '<tr><th style="width:150px;">データ名</th><th style="width:70px;">データ種類</th><th style="width:70px;">値</th><th>説明</th><th>操作</th></tr>';
$input_list.= $list_in_tr;
$input_list.= '</table>';


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
	<script src="../js/jquery-2.2.1.min.js" charset="UTF-8"></script>

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
							
							<div class="spc50">
								<?php echo $input_list;?>
							</div>
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