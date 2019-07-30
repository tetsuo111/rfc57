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


//▼銀行口座
$master_bank_1 = $_POST['master_bank_1'];
$master_bank_2 = $_POST['master_bank_2'];
$master_bank_3 = $_POST['master_bank_3'];
$master_bank_4 = $_POST['master_bank_4'];
$master_bank_5 = $_POST['master_bank_5'];


if($_POST['act'] == 'edit'){
	$err = false;
	///エラーチェック
	
	//銀行口座
	if(!$master_bank_1){ $err = true; $err_bank_1 = true; }
	if(!$master_bank_2){ $err = true; $err_bank_2 = true; }
	if(!$master_bank_3){ $err = true; $err_bank_3 = true; }
	if(!$master_bank_4){ $err = true; $err_bank_4 = true; }
	if(!$master_bank_5){ $err = true; $err_bank_5 = true; }
	
	if($err == false){    //エラーなし
		$form_select = 'process';

	}else{
		//銀行口座エラー
		if($err_bank_1 == true)   { $add_err_bank_1   = '<span class="err"> 未記入</span>'; }
		if($err_bank_2 == true)   { $add_err_bank_2   = '<span class="err"> 未記入</span>'; }
		if($err_bank_3 == true)   { $add_err_bank_3   = '<span class="err"> 未記入</span>'; }
		if($err_bank_4 == true)   { $add_err_bank_4   = '<span class="err"> 未記入</span>'; }
		if($err_bank_5 == true)   { $add_err_bank_5   = '<span class="err"> 未記入</span>'; }
	}

	
}elseif($_POST['act'] == 'process'){

	//▼登録チェック
	$query_check = tep_db_query("
		SELECT 
			`master_bank_id`
		FROM `".TABLE_MASTER_BANK."`
		WHERE `state` = '1' 
	");

	//▼登録情報
	$sql_data_array = array(
		'master_bank_name'         => $master_bank_1,
		'master_bank_branch'       => $master_bank_2,
		'master_bank_type'         => $master_bank_3,
		'master_bank_number'       => $master_bank_4,
		'master_bank_account_name' => $master_bank_5,
		'date_create'              => 'now()',
		'state' => '1'
	);
	
	//▼DB登録
	$db_table = TABLE_MASTER_BANK;
	
	if ($b = tep_db_fetch_array($query_check)){
		//更新登録
		zDBUpdate($db_table,$sql_data_array,$b['master_bank_id']);

	}else{
		//新規登録
		zDBNewUniqueID($db_table,$sql_data_array,'master_bank_ai_id','master_bank_id');
	}
	
	//▼終了テキスト
	$end_text = '<p>入金先の銀行口座を登録しました</p>';

}else{


	//▼銀行口座
	$master_bank_1 = '';
	$master_bank_2 = '';
	$master_bank_3 = '';
	$master_bank_4 = '';
	$master_bank_5 = '';

	//▼銀行情報
	$query =  tep_db_query("
		SELECT 
			`master_bank_name`         AS `mb_name`,
			`master_bank_branch`       AS `mb_branch`,
			`master_bank_type`         AS `mb_type`,
			`master_bank_number`       AS `mb_number`,
			`master_bank_account_name` AS `mb_account_name`
		FROM `".TABLE_MASTER_BANK."`
		WHERE `state` = '1' 
	");

	if ($b = tep_db_fetch_array($query)) {
		$master_bank_1 = $b['mb_name'];
		$master_bank_2 = $b['mb_branch'];
		$master_bank_3 = $b['mb_type'];
		$master_bank_4 = $b['mb_number'];
		$master_bank_5 = $b['mb_account_name'];
	}
}




//-----------------表示フォーム-----------------
if($form_select == 'process'){
	
	$add_item  = '<input type="hidden" name="master_bank_1" value="'.$master_bank_1.'" >';//銀行名
	$add_item .= '<input type="hidden" name="master_bank_2" value="'.$master_bank_2.'" >';//支店名
	$add_item .= '<input type="hidden" name="master_bank_3" value="'.$master_bank_3.'" >';//口座種別
	$add_item .= '<input type="hidden" name="master_bank_4" value="'.$master_bank_4.'" >';//口座番号
	$add_item .= '<input type="hidden" name="master_bank_5" value="'.$master_bank_5.'" >';//口座名義
	
	$add_form  = '<form name="edit" action="'.$form_action_to.'" method="post">';
	$add_form .= '<input type="hidden" name="act" value="process">';
	$add_form .= $add_item;
	$add_form_end  = '</form>';
	
	//master_bank
	$add_form_bank_1 = '<input type="text" class="input_text" value="'.$master_bank_1.'" disabled="disabled">';//銀行名
	$add_form_bank_2 = '<input type="text" class="input_text" value="'.$master_bank_2.'" disabled="disabled">';//支店名
	$add_form_bank_3 = '<input type="text" class="input_text_f" style="width:4em;" maxlength="4" value="'.$master_bank_3.'" disabled="disabled">';//口座種別
	$add_form_bank_4 = '<input type="text" class="input_text" value="'.$master_bank_4.'" disabled="disabled">';//口座番号
	$add_form_bank_5 = '<input type="text" class="input_text" value="'.$master_bank_5.'" disabled="disabled">';//口座名義

	$add_form_back  = '<form name="back" action="'.$form_action_to.'" method="post">';
	$add_form_back .= '<input type="hidden" name="act" value="">';
	$add_form_back .= $add_item;
	$add_form_back .= '</form>';

	$add_form_ele_submit = '<input type="submit" value="登録する">';
	$add_form_ele_submit .= '<a class="cancel" onClick="document.back.submit();">　キャンセル</a>';

}else{

	$add_form  = '<form name="edit" action="'.$form_action_to.'" method="post">';
	$add_form .= '<input type="hidden" name="act" value="edit">';
	
	$add_form_end  = '</form>';

	//master_bank
	$add_form_bank_1 = '<input class="input_text" type="text" name="master_bank_1" value="'.$master_bank_1.'">';//銀行名
	$add_form_bank_2 = '<input class="input_text" type="text" name="master_bank_2" value="'.$master_bank_2.'">';//支店名

	$selected_3_1 = ($master_bank_3 == '普通')? 'selected':'';
	$selected_3_2 = ($master_bank_3 == '当座')? 'selected':'';
	$add_form_bank_3 = '<select  class="selcet_both" style="width:80px;" name="master_bank_3">';               //口座種別
	$add_form_bank_3.= '<option value="普通" '.$selected_3_1.'>普通</option>';
	$add_form_bank_3.= '<option value="当座" '.$selected_3_2.'>当座</option>';
	$add_form_bank_3.= '</select> ';

	$add_form_bank_4 = '<input class="input_text" type="text" name="master_bank_4" value="'.$master_bank_4.'">';//口座番号
	$add_form_bank_5 = '<input class="input_text" type="text" name="master_bank_5" value="'.$master_bank_5.'">';//口座名義

	$add_form_ele_submit = '<input type="submit" value="確認">';
}

$must = '<span style="font-size:12pt; font-weight:400; color:#DD0000; vertical-align:super;">*</span>';

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
	<script src="https://ajaxzip3.googlecode.com/svn/trunk/ajaxzip3/ajaxzip3.js" charset="UTF-8"></script>
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
				
				<h2>入金先銀行口座</h2>
				<div class="area1">
					<?php echo $add_form;?>
						
						<table class="input_form">
							
							<tr>
								<th>銀行名<?php echo $must;?></th>
								<td><?php echo $add_form_bank_1; ?><?php echo $add_err_bank_1;?></td>
							</tr>
							<tr>
								<th>支店名<?php echo $must;?></th>
								<td><?php echo $add_form_bank_2; ?><?php echo $add_err_bank_2;?></td>
							</tr>
							<tr>
								<th>口座種別<?php echo $must;?></th>
								<td><?php echo $add_form_bank_3; ?><?php echo $add_err_bank_3;?></td>
							</tr>
							
							<tr>
								<th>口座番号</th>
								<td>
									<?php echo $add_form_bank_4; ?><?php echo $add_err_bank_4;?><br>
									形式：000-000000-000<br>
								</td>
							</tr>
							<tr>
								<th>口座名義</th>
								<td><?php echo $add_form_bank_5; ?><?php echo $add_err_bank_5;?></td>
							</tr>
						</table>
						<div class="spc20">
							<?php echo $add_form_ele_submit; ?>
						</div>
					<?php echo $add_form_end;?>
					<?php echo $add_form_back;?>
					<?php echo $end_text;?>
				</div>

			</div>
		</div>
	</div>
	
	<div id="footer">
		<?php require('inc_master_footer.php'); ?>
	</div>
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
