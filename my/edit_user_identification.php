<?php 
require('includes/application_top.php');

if($_COOKIE['user_id']){
	$user_id  = $_COOKIE['user_id'];
	$memberid = $_COOKIE['user_id'];
	$head_user_name = $_COOKIE['user_name'].'様';
}else{
	//$head_user_name = 'ゲスト様';
	tep_redirect('../logout.php', '', 'SSL');
}


//-----------------全体設定-----------------
//▼リンク先
$form_action_to = basename($_SERVER['PHP_SELF']);

//▼申し込み後
$form_action_next = 'edit_user_upload.php';

//▼必須属性
$must = '<span style="font-size:12pt; font-weight:400; color:#DD0000; vertical-align:super;">*</span>';

//▼使用不可
$disabled = 'disabled';

//▼Navigation
$my_nav = zGetMyNavigation($NavUserInfoJP,$form_action_to);



$ident_num    = $_POST['user_identification_number'];
$ident_issue  = $_POST['user_identification_date_issue'];
$ident_expire = $_POST['user_identification_date_expire'];

$ac_type      = $_POST['user_address_certification_type'];
$ac_issue     = $_POST['user_address_certification_date_issue'];


/*-------- データ処理 --------*/
if(($_POST['act'] == 'process')AND(!empty($_POST['act_send']))){

	/*----- 登録情報 -----*/
	//▼身分証
	$ident_data_array = array(
		'memberid'    => $user_id,
		'user_identification_number'      => $ident_num,
		'user_identification_date_issue'  => $ident_issue,
		'user_identification_date_expire' => $ident_expire,
		'date_create' => 'now()',
		'state'       => '1'
	);
	
	//▼住所証明書
	$ac_data_array = array(
		'memberid'        => $user_id,
		'user_address_certification_type'       => $ac_type,
		'user_address_certification_date_issue' => $ac_issue,
		'date_create'     => 'now()',
		'state'           => '1'
	);
	
	
	/*----- データ登録 -----*/
	//▼テーブル指定
	$db_table_a = TABLE_USER_IDENTIFICATION;
	$db_table_b = TABLE_USER_ADDRESS_CERTIFICATION;
	
	
	//▼身分証情報
	if($ta_id = zCheckUserReg($db_table_a,$user_id)){
		
		unset($ident_data_array['date_create']);
		$ident_data_array['date_update'] = 'now()';
		
		//更新登録
		$w_set = "`user_identification_id`='".tep_db_input($ta_id)."' AND `state`='1'";
		tep_db_perform($db_table_a,$ident_data_array,'update',$w_set);
		$end_a = 'up';
		
	}else{
		//新規登録
		zDBNewUniqueID($db_table_a,$ident_data_array,'user_identification_ai_id','user_identification_id');
		$end_a = 'new';
	}
	
	//▼住所証明書
	if($tb_id = zCheckUserReg($db_table_b,$user_id)){
		
		unset($ac_data_array['date_create']);
		$ac_data_array['date_update'] = 'now()';
		
		//更新登録
		$wb_set= "`user_address_certification_id`= '".tep_db_input($tb_id)."' AND `state`='1'";
		tep_db_perform($db_table_b,$ac_data_array,'update',$wb_set);
		$end_b = 'up';
		
	}else{
		//新規登録
		zDBNewUniqueID($db_table_b,$ac_data_array,'user_address_certification_ai_id','user_address_certification_id');
		$end_b = 'new';
	}
	
	
	/*----- ステータス更新 -----*/
	$st = zUserStatusCheck($user_id);
	
	//更新の時は何もしない
	//▼身分証
	if($st['ident'] != 'u'){
		//n　＞　u更新に変更
		if($st['ident'] == 'n'){
				$res = 'u';
		}else{
			$res = '1';
		}
		//▼ステータス更新
		zUserWCStatusUpdate('user_wc_status_identification',$res,$user_id);
	}
	
	//▼住所証明書
	if($st['ad_certif'] != 'u'){
		//n　＞　u更新に変更
		if($st['ad_certif'] == 'n'){
				$res = 'u';
		}else{
			$res = '1';
		}
		//▼ステータス更新
		zUserWCStatusUpdate('user_wc_status_address_certification',$res,$user_id);
	}
	
	
	//▼終了処理
	if(($end_a == 'new')AND($end_b == 'new')){
		$end = '<script>alert("登録しました");location.href="'.$form_action_next.'";</script>';
		
	}else{
		$end = '<script>alert("登録しました");</script>';
	}

	echo $end;

}else if($_POST['act'] == 'process'){
	
	//▼エラーチェック
	$err = false;
	
	if(!$ident_num)   {$err = true; $err_ident_num;}
	if(!$ident_issue) {$err = true; $err_ident_issue;}
	if(!$ident_expire){$err = true; $err_ident_expire;}
	if(!$ac_type)     {$err = true; $err_ac_type;}
	if(!$ac_issue)    {$err = true; $err_ac_issue;}
	
	/*-------- 表示設定 --------*/
	if(($err == false)AND(empty($_POST['act_cancel']))){
	
		//▼エラーなし　＞　確認画面
		$form_select = 'process';
		
	}else{
		if($err_ident_num    == true) { $err_text.= '<span class="alert">身分証番号が未記入です</span>'; }
		if($err_ident_issue  == true) { $err_test.= '<span class="alert">身分証の発行日が未記入です</span>'; }
		if($err_ident_expire == true) { $err_test.= '<span class="alert">身分証の有効期限未記入です</span>'; }
		if($err_ac_type      == true) { $err_test.= '<span class="alert">住所証明書種類が未記入です</span>'; }
		if($err_ac_issue     == true) { $err_test.= '<span class="alert">住所証明書の発行日が未記入です</span>'; }
	}

}else{

	/*----- 身分証明書 -----*/
	//▼証明書情報
	$user_ident_query = tep_db_query("
		SELECT 
			`user_identification_number` AS `num`,
			DATE_FORMAT(`user_identification_date_issue`,'%Y-%m-%d') AS `issue`,
			DATE_FORMAT(`user_identification_date_expire`,'%Y-%m-%d') AS `expire`
		FROM `".TABLE_USER_IDENTIFICATION."` 
		WHERE `state` = '1' 
		AND `memberid` = '".tep_db_input($user_id)."' 
	");

	if($user_ident = tep_db_fetch_array($user_ident_query)){
		$ident_num    = $user_ident['num'];
		$ident_issue  = $user_ident['issue'];
		$ident_expire = $user_ident['expire'];
	}

	//▼登録確認
	$isIdent = ($user_ident)? 1: 0;
	
	
	/*----- 住所証明書 -----*/
	$user_ac_query = tep_db_query("
		SELECT 
			`user_address_certification_type` AS `type`,
			DATE_FORMAT(`user_address_certification_date_issue`,'%Y-%m-%d') AS `issue`
		FROM `".TABLE_USER_ADDRESS_CERTIFICATION."` 
		WHERE `state` = '1' 
		AND   `memberid` = '".tep_db_input($user_id)."' 
	");

	//▼データ取得
	if($user_ac = tep_db_fetch_array($user_ac_query)){
		$ac_type   = $user_ac['type'];
		$ac_issue  = $user_ac['issue'];
	}
	
	//▼登録確認
	$isAddC = ($user_ac)? 1: 0;
}


/*-------- 表示内容 --------*/
if($form_select == 'process'){
	
	$input_auto = '<input type="hidden" name="act" value="process">';
	
	
	/*----- 身分証明書 -----*/
	//▼証明書番号
	$ident_number      = '<input type="text" id="" class="form-control" name="user_identification_number"      value="'.$ident_num.'"    readonly>';
	
	//▼発行日
	$ident_date_issue  = '<input type="text" id="" class="form-control" name="user_identification_date_issue"  value="'.$ident_issue.'"  readonly>';
	
	//▼有効期限
	$ident_date_expire = '<input type="text" id="" class="form-control" name="user_identification_date_expire" value="'.$ident_expire.'" readonly>';
	
	
	/*----- 住所証明書 -----*/
	//▼証明書種類
	foreach($AddressCertifArray AS $k => $v){
		
		//▼チェックの確認
		$checked = ($k == $ac_type)? 'checked="checked"' : 'disabled';
		
		//▼ラジオボタン入力確認
		$addr_certif_type.= '<label><input type="radio" name="user_address_certification_type" value="'.$k.'" '.$checked.'>　'.$v.'</label><br>';
	}
	
	//▼発行日
	$addr_certif_date_issue = '<input type="text" id="" class="form-control" name="user_address_certification_date_issue" value="'.$ac_issue.'" readonly>';
	
	
	/*----- 登録ボタン -----*/
	$button_in = '<input type="submit" class="btn"         name="act_send"   value="上の内容で登録する">';
	$button_in.= '<input type="submit" class="btn spc10_l" name="act_cancel" value="キャンセル">';
	
}else{

	//▼自動入力要素
	$input_auto = '<input type="hidden" name="act"      value="process">';
	$input_auto.= '<input type="hidden" name="user_own" value="'.$user_walle_card['own'].'" id="UOwn">';
	$input_auto.= '<input type="hidden" name="user_id"  value="'.$user_id.'">';
	
	
	/*----- 身分証明書 -----*/
	//▼証明書番号
	$ident_number      = '<input type="text" id="DNum"    class="form-control" name="user_identification_number"      value="'.$ident_num.'"    required pattern="^[0-9a-zA-Z-]+$">';

	//▼発行日
	$ident_date_issue  = '<input type="text" id="DIssue"  class="form-control" name="user_identification_date_issue"  value="'.$ident_issue.'"  required style="background:#FFF;" readonly>';

	//▼有効期限
	$ident_date_expire = '<input type="text" id="DExpire" class="form-control" name="user_identification_date_expire" value="'.$ident_expire.'" required style="background:#FFF;" readonly>';
	
	
	/*----- 住所証明書 -----*/
	//▼証明書種類
	foreach($AddressCertifArray AS $k => $v){
		
		//▼チェックの確認
		$checked = ($k == $ac_type)? 'checked="checked"' : '';
		
		//▼ラジオボタン入力確認
		$addr_certif_type.= '<label><input type="radio" name="user_address_certification_type" value="'.$k.'" '.$checked.'>　'.$v.'</label><br>';
	}
	
	//▼発行日
	$addr_certif_date_issue = '<input type="text" id="ACIssue" class="form-control" name="user_address_certification_date_issue" value="'.$ac_issue.'" required style="background:#FFF;" readonly>';
	
	
	/*----- 登録ボタン -----*/
	$button_in = '<input type="submit" class="btn" '.$disabled.' id="ActButton" value="内容を確認する">';
}


/*-------- 表示フォーム --------*/
//▼身分証
$input_ident = '<li><div class="form_el">';
$input_ident.= '<h4>証明書番号'.$must.'</h4>';
$input_ident.= '<div>'.$ident_number.'</div>';
$input_ident.= '</div></li>';

$input_ident.= '<li><div class="form_el">';
$input_ident.= '<h4>発行年日（交付日）'.$must.'</h4>';
$input_ident.= '<div>'.$ident_date_issue.'</div>';
$input_ident.= '</div></li>';

$input_ident.= '<li><div class="form_el">';
$input_ident.= '<h4>有効期限'.$must.'</h4>';
$input_ident.= '<div>'.$ident_date_expire.'</div>';
$input_ident.= '</div></li>';


//▼住所証明書
$input_addr_certif = '<li><div class="form_el">';
$input_addr_certif.= '<h4>住所証明書種類'.$must.'</h4>';
$input_addr_certif.= '<div class="radio">'.$addr_certif_type.'</div>';
$input_addr_certif.= '</div></li>';

$input_addr_certif.= '<li><div class="form_el">';
$input_addr_certif.= '<h4>発行年日（交付日）'.$must.'</h4>';
$input_addr_certif.= '<div>'.$addr_certif_date_issue.'</div>';
$input_addr_certif.= '</div></li>';


//▼申込ボタン
$input_button = '<div class="form-group">';
$input_button.= $button_in;
$input_button.= '</div>';


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type"         content="text/html; charset=<?php echo CHARSET; ?>">
	<meta http-equiv="Content-Style-Type"  content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<meta http-equiv="X-UA-Compatible"      content="IE=edge">
	<meta http-equiv="content-language"     content="ja">
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
	<link rel="stylesheet" type="text/css" href="../js/jquery-ui/jquery-ui.min.css">
	<link rel="stylesheet" type="text/css" href="../js/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="../js/bootstrap/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="../css/my.css"       media="all">
	
	<script src="../js/jquery-3.2.1.min.js"            charset="UTF-8"></script>
	<script src="../js/jquery-migrate-1.4.1.min.js" charset="UTF-8"></script>
	<script src="../js/bootstrap/js/bootstrap.min.js" charset="UTF-8"></script>
	<script src="../js/jquery-ui/jquery-ui.min.js"    charset="UTF-8"></script>
	
	<script type="text/javascript">
		var opmonth = ["1","2","3","4","5","6","7","8","9","10","11","12"];
		var opday   = ["日","月","火","水","木","金","土"];
		
		$(function() {
			var dopt ={dateFormat:'yy-mm-dd',changeYear:true, changeMonth:true, yearRange:"c-5:c+10",
						monthNames:opmonth,monthNamesShort:opmonth,
						dayNames:opday,dayNamesMin:opday,dayNamesShort:opday,
						showMonthAfterYear:true}
			$('#DIssue').datepicker (dopt);
			$('#DExpire').datepicker(dopt);
			$('#ACIssue').datepicker(dopt);
		});
	</script>
	
	<style>
		/*---datepick---*/
		.ui-datepicker select.ui-datepicker-month{width:30%;}
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
		
			<div id="left2" class="col-xs-12 col-sm-12 col-md-8 col-lg-10">
				<div class="inner">
					
					<div>
						<?php echo $my_nav;?>
					</div>
					
					<form id="ApplicForm" action="<?php echo $form_action_to;?>" method="POST">
						<?php echo $input_auto;?>
						
						<div class="form-group form_area">
							<h3>身分証明書（パスポート、運転免許証等）</h3>
							<ul class="form_table">
								<?php echo $input_ident;?>
							</ul>
						</div>
						
						<div  class="spc50">
							<div  class="form-group form_area">
								<h3>住所証明書</h3>
								<ul class="form_table">
									<?php echo $input_addr_certif;?>
								</ul>
							</div>
						</div>
						<div class="form_area spc20" style="text-align:center;">
							<?php echo $input_button;?>
						</div>
					</form>
					
				</div>
			</div>
		</div>
	</div>

	<div id="footer">
		<?php require('inc_user_footer.php'); ?>
	</div>
</div>
<script src="../js/MyHelper.js" charset="UTF-8"></script>
<script>
	var idNew = '<?php echo $edit;?>';
	
	//----------------関数定義----------------
	//▼入力確認関数
	function zOpenSubmit(){
	
		//▼カード申込状況
		var Own  = $('#UOwn').val();
		var Flag = 0;
		var BExt = 0;
		
		//▼会員ID・登録メールアドレス
		var NigId = $('#WCMail').val();
		
		//▼身分証番号
		var Num    = $('#DNum').val();
		
		//▼発行年日
		var Issue  = $('#DIssue').val();
		
		//▼有効期限
		var Expire = $('#DExpire').val();
		
		//▼住所証明書種類
		var ACType = $('input[name=user_address_certification_type]:checked').val();
		
		//▼住所証明書有効期限
		var ACIssue = $('#ACIssue').val();
		
		
		//▼登録の最終確認
		if((NigId != "")&&(Num != "")
			&&(Issue != "")&&(Expire != "")
			&&(ACType)&&(ACIssue != "")
		){Flag = 1;}
		
		
		//▼開放処理
		if(Flag == '1'){
			$('#ActButton').attr('disabled',false);
			
			return true;
		}else{
			$('#ActButton').attr('disabled',true);
			
			return false;
		}
	}
	
	
	//入力確認
	$('#ApplicForm').on('change keyup',function(){zOpenSubmit();});

</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
