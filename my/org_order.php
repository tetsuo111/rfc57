<?php 
require('includes/application_top.php');

if($_COOKIE['user_id']){
	$user_id        = $_COOKIE['user_id'];
	$user_email     = $_COOKIE['user_email'];
	$head_user_name = $_COOKIE['user_name'].'様';
	$position_id    = $_COOKIE['position_id'];
}else{
	//$head_user_name = 'ゲスト様';
	tep_redirect('../logout.php', '', 'SSL');
}


//▼とび先設定
$form_action_to   = basename($_SERVER['PHP_SELF']);	//オーダー登録用
$form_action_next = 'edit_user_address.php';		//初回オーダー後用

//▼基準通貨
$cur     = zGetSysSetting('sys_base_currency');
$cur_uni = zGetSysSetting('sys_base_currency_unit');
$dis     = '';


/*-------- リスト取得 --------*/
//▼ランク設定
$rank_ar = zRankList();


//▼通貨
$query_currency = tep_db_query("
	SELECT
		`cr`.`m_currency_id`       AS `id`,
		`cr`.`m_currency_name`     AS `name`,
		`cn`.`m_currency_now_rate` AS `rate`
	FROM  `".TABLE_M_CURRENCY."` AS `cr`
	LEFT JOIN `".TABLE_M_CURRENCY_NOW."` AS `cn` ON `cn`.`m_currency_id` = `cr`.`m_currency_id`
	WHERE `cr`.`state` = '1'
	AND   `cn`.`state` = '1'
	ORDER BY `cr`.`m_currency_id` ASC
");


//▼」初期設定
$cur_ar['o']      = $cur_uni;
$cur_rate_ar['o'] = 1;

if(tep_db_num_rows($query_currency)){
	while($c = tep_db_fetch_array($query_currency)) {
		$cur_ar[$c['id']]      = $c['name'];
		$cur_rate_ar[$c['id']] = $c['rate'];
	}
}

//▼引継ぎ用
$jsonRate = json_encode($cur_rate_ar);
$jsonCur  = json_encode($cur_ar);



/*----- 商品一覧 -----*/
//▼アクティブ確認
if($position_id){
	
	if(zPositionActiveCheck($position_id)){
		
		//▼Active
		//追加購入のみ
		$plan_sort = "`m_plan_o_after` = 'a'";
		
	}else{
		
		//▼InActive
		//登録確認　＞　初回注文の履歴がない
		$sort = (!zCheckUserReg(TABLE_USER_ORDER,$user_id))? 'a' : 'b';

		if($sort == 'a'){
			$plan_sort = "`m_plan_o_first` = 'a'";
		}else{
			$plan_sort = "`m_plan_o_after` = 'a'";
		}
	}

}else{
	$err_pos = true;
}



//▼注文回数
$query_order_limit =  tep_db_query("
	SELECT
		COUNT(`uo`.`user_order_id`) AS `o_num`,
		`mp`.`m_plan_id`            AS `id`,
		`mp`.`m_plan_o_limit_times` AS `limit_time`
	FROM  `".TABLE_USER_ORDER."` AS `uo`
	LEFT JOIN`".TABLE_M_PLAN."`  AS `mp` ON `uo`.`user_order_plan` LIKE CONCAT('%\"planid\":\"',`mp`.`m_plan_id`,'\"%')
	WHERE `mp`.`state` = '1'
	AND   `mp`.`m_plan_o_limit_times` > 0
	AND   `uo`.`state`   = '1'
	AND   `uo`.`user_id` = '".tep_db_input($user_id)."'
	GROUP BY `mp`.`m_plan_id`
	ORDER BY `mp`.`m_plan_id` ASC
");

while($lt = tep_db_fetch_array($query_order_limit)){
	if($lt['o_num'] >= $lt['limit_time']){
		$for_avoid_plan_id.= ((!$for_avoid_plan_id)? "'":",'").$lt['id']."'";
	}
}

if($for_avoid_plan_id){
	$search_limit = "AND `m_plan_id` NOT IN(".$for_avoid_plan_id.")";
}


//▼費用、注文設定
$query_plan = tep_db_query("
	SELECT
		`m_plan_id`            AS `id`,
		`m_plan_name`          AS `name`,
		`m_plan_rank_id`       AS `rank_id`,
		`m_plan_sum`           AS `sum`,
		`m_plan_item`          AS `item`,
		`m_plan_item_pay`      AS `i_pay`,
		`m_plan_detail`        AS `detail`,
		`m_plan_point`         AS `point`,
		`m_plan_o_group`       AS `o_group`,
		`m_plan_o_limit_times` AS `o_lt`,
		`m_plan_o_limit_piece` AS `o_lp`,
		`m_plan_o_must`        AS `o_must`,
		`m_plan_o_caution`     AS `o_caution`
	FROM  `".TABLE_M_PLAN."`
	WHERE `state` = '1'
	AND   ".$plan_sort."
	".$search_limit."
	ORDER BY `m_plan_id` ASC
");


while($b = tep_db_fetch_array($query_plan)) {
	
	//▼注文表示用
	$gr = ($b['o_group'])? $b['o_group']:$b['id'];
	$plan_ar[$gr][] = $b;
	
	//▼登録用
	$plan_reg_ar[$b['id']] = $b;
	
	//▼プラン
	$js_plan[$b['id']] = $b['sum'];
}


/*----- 取得データ -----*/
$Po_plan      = $_POST['o_plan'];			//注文別選択ID
$Po_num       = $_POST['o_num'];			//注文別個数ID
$agree_ap     = $_POST['agree_ap'];			//AP規約
$agree_fs     = $_POST['agree_fs'];			//FS規約
$pay_cr_id    = $_POST['pay_currency'];		//支払通貨ID
$order_limit  = $_POST['order_limit'];		//支払予定日



/*====================================
	データ処理
====================================*/
if(($_POST['act_send'] == 'send')AND(empty($_POST['sub_clear']))){
	
	//▼リロード対策
	if($_POST['ticket'] === $_COOKIE['ticket']){
		tep_cookie_del('ticket');
		//echo '一回目';//そのまま
	}else{
		tep_cookie_del('ticket');
		tep_redirect('order_list.php', '', 'SSL');
	}
	
	
	/*----- 注文費用計算 -----*/
	//▼登録内容を作成　＞出力結果 $order_ar
	require('../util/inc_user_order_save.php');
	
	
	//新規登録
	$db_table = TABLE_USER_ORDER;
	$order_id = zDBNewUniqueID($db_table,$order_ar,'user_order_ai_id','user_order_id');
	
	
	/*----- ステータス更新 -----*/
	require('../util/inc_user_order_status.php');
	
	
	/*----- メール送信 -----*/
	//▼送信情報設定
	$email             = zGetUserEmail($user_id);	//送信メールアドレス
	$Efs_id            = $_COOKIE['fs_id'];			//fsid
	$Euser_name        = $_COOKIE['user_name'];
	$Eorder_id         = $order_id;					//注文番号
	$Eorder_amount     = $order_amount;				//注文金額
	$Eorder_limit      = $order_limit;				//支払予定日
	$Eorder_currency   = $cur_ar[$pay_cr_id];		//支払通貨
	$Eorder_rate       = $rate;						//注文時の通貨レート
	$Eorder_pay_amount = $order_pay_rate_amount;
	
	//▼新規の場合
	Email_Order_Create($EmailHead,$EmailFoot,$email,$Efs_id,$Euser_name,$Eorder_id,$Eorder_amount,$Eorder_limit,$Eorder_currency,$Eorder_rate,$Eorder_pay_amount);

	//▼終了処理
	$end = 'end';
	
}else if($_POST['act'] == 'process'){

	//▼エラーチェック　＞　確定表示
	$err = (!empty($_POST['sub_clear']))? true: false;
	$err_limit = false;
	
	//▼データチェック
	if(empty($_POST['o_plan']))      {$err=true; $err_text.= '<p class="alert">商品を選択してください</p>';}					//プラン
	if(empty($_POST['pay_currency'])){$err=true; $err_text.= '<p class="alert">支払通貨を選択してください</p>';}				//通貨
	if(empty($_POST['order_limit'])) {$err=true; $err_text.= '<p class="alert">入金予定日を選択してください</p>';}				//入金予定日
	
	if($sort == 'a'){
		require('../util/inc_user_order_agree_check.php');
	}
	
	//▼入金期限
	if(!$order_limit) {$err = true; $err_limit = true;}
	if(($order_limit) && ($order_limit < date("Y-m-d"))) {$err = true; $err_limit2 = true;}
	
	
	//▼表示設定
	if($err == false){    //エラーなし
	
		//▼自動入力要素
		$input_auto = '<input type="hidden" name="act_send"    value="send">';
		$input_auto.= '<input type="hidden" name="order_limit" value="'.$order_limit.'"  id="date">';		//入金予定日
		
		
		//▼各入力要素
		$fr_item_list     = '<p>'.$first_item_num.'</p>';
		$ap_item_list     = '<p>'.$append_item_num.'</p>';
		$order_form_ele_2 = '<p>'.$order_limit.'</p>';
		
		//▼合計金額
		$fr_total  = $first_item_num  * $first_price;
		$ap_total  = $append_item_num * $append_price;
		$odr_total = $fr_total + $ap_total + $_POST['wallet_purchased'];
		
		
		//▼入力制限
		$dis = 'disabled';
		
		
		//▼登録ボタン
		$order_form_ele_submit = '<input type="submit" class="btn form_submit" name="sub_send"  value="注文する" id="Act">';
		$order_form_ele_submit.= '<input type="submit" class="btn form_cancel spc10_l" name="sub_clear" value="キャンセル">';

	}else{
		
		if($err_limit  == true){$err_text.= '<span class="alert"> 振込予定日が未入力です。</span><br>';}
		if($err_limit2 == true){$err_text.= '<span class="alert"> 過去の日付が入力されています。</span><br>';}
		
		//▼入金予定日
		$order_form_ele_2 = '<input type="text" name="order_limit"  value="'.$order_limit.'" id="date" readonly="readonly"style="width:90px;">';

		//▼登録ボタン
		$order_form_ele_submit = '<input type="submit" class="btn form_submit" value="確認画面へ" disabled="disabled" id="Act">';
	}

	
}else{

	//▼入金予定日
	$order_form_ele_2 = '<input  type="text" name="order_limit"  value="'.$order_limit.'" id="date" readonly="readonly" style="width:90px;">';

	//▼登録ボタン
	$order_form_ele_submit = '<input type="submit" class="btn form_submit" value="確認画面へ" disabled="disabled" id="Act">';
	
	
	$isWork = 'work';
}


//▼リロード対策
$ticket = md5(uniqid().mt_rand());
tep_cookie_set('ticket',$ticket );



/*======================================
	ユーザー情報取得
======================================*/
//▼ユーザー情報伝達
require ('inc_user_announce.php');



/*======================================
	表示フォーム
======================================*/
if($end == 'end'){
	
	$order_form = '<p>注文を登録しました</p>';
	$order_form.= '<p>詳細をご登録のメールアドレスに送りました。内容をご確認の上お支払いください。</p>';
	
	if($sort == 'a'){
		
		$scr = '<script>';
		$scr.= 'alert("注文を登録しました。"+"\n"+"詳細をご登録のメールアドレスに送りました。内容をご確認の上お支払いください。");';
		$scr.= 'location.href="'.$form_action_next.'";';
		$scr.= '</script>';
		
		echo $scr;
	}
	
}else{
	
	if($err_pos){
		//▼エラー表示
		$order_form = '<p class="alert">ログイン情報が正しくありません</p>';
		$order_form.= '<p class="alert">一度ログアウトし再度ログインしてください</p>';

		
	}else{
		//▼注文表示
		require('../util/inc_user_order_form.php');
	}
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type"         content="text/html; charset=<?php echo CHARSET; ?>">
	<meta http-equiv="Content-Style-Type"   content="text/css">
	<meta http-equiv="Content-Script-Type"  content="text/javascript">
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
	<link rel="stylesheet" type="text/css" href="../js/jquery-ui/jquery-ui.min.css">
	<link rel="stylesheet" type="text/css" href="../js/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="../js/bootstrap/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="../css/my.css"       media="all">
	
	<script src="../js/jquery-3.2.1.min.js"            charset="UTF-8"></script>
	<script src="../js/jquery-migrate-1.4.1.min.js"   charset="UTF-8"></script>
	<script src="../js/bootstrap/js/bootstrap.min.js" charset="UTF-8"></script>
	<script src="../js/jquery-ui/jquery-ui.min.js"    charset="UTF-8"></script>

	<script type="text/javascript">
		$(function() {$('#date').datepicker({ dateFormat: 'yy-mm-dd', });});
	</script>
	<style>
		.announce_table{margin:5px 0px 5px 0px; width:600px;}
		.announce_table tr{}
		.announce_table th{background:#DFDFDF;border:1px #FFFFFF solid; padding:2px 5px 2px 5px;}
		.announce_table td{background:#FFFFFF;border:1px #DFDFDF solid; padding:2px 5px 2px 5px;}
		
		.show_ok{background:#FFF;}
		
		.rate_area .inner2{padding:5px 10px; background:#F4F4F4;}
		
		.order_table{width:100%; color:#222;}
		.order_table tr{border:1px solid #E4E4E4;}
		.order_table th{padding:10px; font-weight:800;}
		.order_table td{padding:10px; height:26px;}
		.order_table td .el2{text-align:right;}
		
		.total_area{overflow:hidden;}
		.total_area .inner2{width:220px; border-bottom:1px solid #222;float:right; padding:5px 10px;}
		.total_area .inner2 .el1{float:right;}
		
		.agree_area {border:1px solid #E4E4E4; border-radius:7px; padding:10px;}
		.agree_area .agree_check{background:#F4F4F4; padding:7px; border-radius:7px;margin-top:10px;}
		.cancel{float:right;}
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
					
					<div>
						<?php echo $my_nav;?>
					</div>

					<h2>Order</h2>
					<div>
						<?php echo $err_text;?>
						<form name="edit" action="<?php echo $form_action_to;?>" method="POST" id="OdrForm">
							<?php echo $order_form ;?>
							<?php echo $order_agree;?>
							<?php echo $order_submit ;?>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
		
	<div id="footer">
		<?php require('inc_user_footer.php');?>
	</div>
</div>
<?php require('../util/inc_user_order_control.php');?>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
