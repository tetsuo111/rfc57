<?php 
require('includes/application_top.php');

if($_COOKIE['user_id']){
	$user_id        = $_COOKIE['user_id'];
	$position_id    = $_COOKIE['position_id'];
	$head_user_name = $_COOKIE['user_name'].'様';
	
}else if($_SERVER['HTTP_USER_AGENT'] == 'CreditPaymentService / KickProcess'){
	
	//カード会社
	$ua = $_SERVER['HTTP_USER_AGENT'];
	
}else{
	
	//$head_user_name = 'ゲスト様';
	tep_redirect('../logout.php', '', 'SSL');
}

//▼とび先設定
$form_edit_to = 'order_edit.php';

//▼基準通貨
$base_cur = zGetSysSetting('sys_base_currency_unit');

//▼請求
function lzGetChargeData($ch_id){
	
	$query =  tep_db_query("
		SELECT
			`user_o_charge_c_amount`  AS `c_amount`,
			`user_o_charge_condition` AS `condition`
		FROM `".TABLE_USER_O_CHARGE."`
		WHERE `state` = '1'
		AND   `user_o_charge_id` = '".tep_db_input($ch_id)."'
	");

	return tep_db_fetch_array($query);
}

//▼注文
function lzGetOrderData($odr_id){
	
	$query= tep_db_query("
		SELECT
			`user_order_id`         AS `id`,
			`user_order_amount`     AS `o_amt`,
			`user_order_condition`  AS `condition`
		FROM `".TABLE_USER_ORDER."`
		WHERE `state`         = '1'
		AND   `user_id`       = '".tep_db_input($_COOKIE['user_id'])."'
		AND   `user_order_id` = '".tep_db_input($odr_id)."'
	");
	
	return tep_db_fetch_array($query);
}

//▼顧客情報
function lzGetMemberLoginId($usid = ''){
	
	//▼取得ID
	if(!$usid){ $usid = $_COOKIE['user_id'];}
	
	$query =  tep_db_query("
		SELECT 
			`login_id`,
			`memo1`
		FROM  `".TABLE_MEM00000."`
		WHERE `memberid` = '".tep_db_input($usid)."'
	");

	return tep_db_fetch_array($query);
}


//----- 決済結果受け取り -----//
if($_GET && ($ua == 'CreditPaymentService / KickProcess')){
	
	//▼結果取得
	$result   = $_GET['Result'];										//決済結果
	$dba      = $_GET['dba'];											//明細番号
	$amt      = $_GET['Amount'];										//金額
	$cst_id   = $_GET['CustomerId'];									//顧客ID
	$cst_pass = $_GET['CustomerPass'];									//顧客Pass
	$tr_code  = $_GET['SiteTransactionId'];								//注文・請求番号
	
	//▼データ分解
	$pos_c    = strpos($tr_code,'c');									//請求開始位置
	$pos_z    = strpos($tr_code,'z');									//請求終了位置
	$odr_id   = substr($tr_code,1,$pos_c - 1);							//注文番号
	$ch_id    = substr($tr_code,($pos_c+1),($pos_z - ($pos_c+1)));		//請求番号
	
	//▼結果を保存
	if($result == 'OK'){
		
		//----- パスワード登録 -----//
		//▼登録配列
		$up_pass_ar = array(
			'editdate' => 'now()',
			'memo1'    => $cst_pass
		);
		
		$w_set = "`login_id` = '".tep_db_input($cst_id)."'";
		tep_db_perform(TABLE_MEM00000,$up_pass_ar,'update',$w_set);
		
		
		//----- 入金完了 -----//
		//▼確認日
		$d_date = date('Y-m-d');
		
		//▼登録データ
		$top            = 'send';
		$charge_id      = $ch_id;		//請求番号
		$peyment_amount = $amt;			//入金金額
		$date_payment   = $d_date;		//入金確認日
		
		//請求処理本体
		require('../master/mutil/mut_edit_charge.php');
		
		$res_ch = $res;
		
		//▼全入金完了
		$top            = 'done';
		$order_id       = $odr_id;
		$o_figure       = $d_date;
		
		//入金完了本体
		require('../master/mutil/mut_order_recieveall.php');
		
		$res_odr = $res;
		
		
		//★履歴用
		$string = 'odr>'.$order_id.'>res>'.$res_odr."\n";
		$string.= 'chr>'.$charge_id.'>res>'.$res_ch."\n";
		
	}
		
	//▼NGの場合
	foreach($_GET AS $kg => $vg){
		$string.= $kg.':'.$vg."\n";
	}

	write_log($string,'w');
}


//----- 注文情報取得 -----//
//▼データ取得
if($_COOKIE['odrid'] && $_COOKIE['chid']){
	
	$c_odrid = $_COOKIE['odrid'];
	$c_chid  = $_COOKIE['chid'];

}else if($_POST['odrid'] && $_POST['chid']){
	
	$c_odrid = $_POST['odrid'];
	$c_chid  = $_POST['chid'];
	$f_edit  = true;
}


//▼データ取得
if($c_odrid && $c_chid){
	
	//----- 必要情報取得 -----//
	//▼データ取得
	$dt_odr = lzGetOrderData($c_odrid);
	$dt_ch  = lzGetChargeData($c_chid);
	$dt_mem = lzGetMemberLoginId();
	
	//▼選択商品データ
	$query= tep_db_query("
		SELECT
			`user_o_detail_js_plan` AS `jplan`
		FROM `".TABLE_USER_O_DETAIL."`
		WHERE `state`         = '1'
		AND   `user_order_id` = '".tep_db_input($c_odrid)."'
	");
	
	$a = tep_db_fetch_array($query);
	$jplan = json_decode($a['jplan'],true);
	
	foreach($jplan AS $k => $vp){
		
		$li_plan.= '<tr><th>商品番号</th><td>'.$vp['plan_id'].'</td></tr>';
		$li_plan.= '<tr><th>商品名</th><td>'.$vp['name'].'</td></tr>';
		$li_plan.= '<tr><th>注文個数</th><td>'.$vp['num'].'個</td></tr>';
		$li_plan.= '<tr><th>注文金額</th><td>'.($vp['base_b']+$vp['tax_b']).$base_cur.'</td></tr>';
		
		//▼カード表示用
		$cd_plan.= (($cd_plan)? ',':'').$vp['name'].':'.$vp['num'].'個';
	}
	
	//▼詳細表示
	$ul_plan = '<table class="table list_table"><tbody>'.$li_plan.'</tbody></table>';
	
	$loinid   = $dt_mem['login_id'];
	$cstmpass = $dt_mem['memo1'];
	
	//$o_amt    = $dt_odr['o_amt'];				//注文金額
	$o_amt    = 210;							//テスト用
	$pay_code = 'o'.$c_odrid.'c'.$c_chid.'z';	//注文コード
	
	
	//----- 決済実行 -----//
	//▼決済本番url
	$an_url1  = 'https://payment.alij.ne.jp/service/credit';
	
	
	//▼決済用データ
	//$site_id_m   = zGetSysSetting('sys_cm_site_id');		//月毎決済ID
	//$site_pass_m = zGetSysSetting('sys_cm_site_pass');	//月毎決済Pass
	//$site_id_y   = zGetSysSetting('sys_cy_site_id');		//年毎決済ID
	//$site_pass_y = zGetSysSetting('sys_cy_site_pass');	//年毎決済Pass
	$site_id1   = zGetSysSetting('sys_c_site_id');			//都度決済用ID
	$site_pass1 = zGetSysSetting('sys_c_site_pass');		//都度決済用Pass
	
	
	//▼送信フォーム
	$tr_code   = $pay_code;
	$pos_c     = strpos($tr_code,'c');										//請求開始位置
	$pos_z     = strpos($tr_code,'z');										//請求終了位置
	$order_id  = substr($tr_code,1,($pos_c - 1));							//注文番号
	$charge_id = substr($tr_code,($pos_c+1),($pos_z - ($pos_c + 1)));		//請求番号
	
	$card_form = '<form action="'.$an_url1.'" method="POST" class="fl_l">';
	$card_form.= '<input type="hidden" name ="siteId"            value="'.$site_id1.'">';
	$card_form.= '<input type="hidden" name ="sitePass"          value="'.$site_pass1.'">';
	$card_form.= '<input type="hidden" name ="CustomerId"        value="'.$loinid.'">';
	$card_form.= '<input type="hidden" name ="CustomerPass"      value="'.$cstmpass.'">';
	$card_form.= '<input type="hidden" name ="itemId"            value="'.$cd_plan.'">';
	$card_form.= '<input type="hidden" name ="SiteTransactionId" value="'.$pay_code.'">';
	$card_form.= '<input type="hidden" name ="Amount"            value="'.$o_amt.'">';
	$card_form.= '<input type="submit" class="btn" value="クレジット決済画面に移動する">';
	$card_form.= '</form>';
	
	//▼戻るフォーム
	if($f_edit){
		$back_form = '<form action="'.$form_edit_to.'" method="POST"  class="fl_l spc10_l">';
		$back_form.= '<input type="hidden" name ="uorder_id" value="'.tep_db_input($c_odrid).'">';
		$back_form.= '<input type="submit" class="btn" value="戻る">';
		$back_form.= '</form>';
	}
	
}else{
	
	$card_form = '<p class="alert">注文情報がありません</p>';
}

//▼設定削除
tep_cookie_del('odrid');
tep_cookie_del('chid');
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
								<?php echo $ul_plan;?>
							</div>
							<div style="overflow:hidden;">
								<?php echo $card_form;?>
								<?php echo $back_form;?>
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
