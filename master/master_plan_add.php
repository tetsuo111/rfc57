<?php 
require('includes/application_top.php');

if(($_COOKIE['master_id']) && ($_COOKIE['master_permission'])){
	$master_id = $_COOKIE['master_id'];
	$head_master_name = $_COOKIE['master_name'].'様';
}else{
	//$head_master_name = 'ゲスト様';
	tep_redirect('logout.php', '', 'SSL');
}


//-------- 全体設定 --------//
//▼とび先設定
$form_action_to = basename($_SERVER['PHP_SELF']);

//★
$m_plan_id  = $_GET['m_plan_id'];
$cont_set = '?m_plan_id='.$m_plan_id;

//▼初回：a　追加：b　定期：c
$sort = 'b';


//-------- リスト取得 --------//
$cur_ar   = zCurrencyList();	//通貨
$rank_ar  = zRankList();		//ランク
$point_ar = zPointList();		//ポイント
$plan_ar  = zPlanListAr();		//登録済み商品
$base_cur = $cur_ar[0];


//▼品目
$query_m = tep_db_query("
	SELECT 
		`m_item_id`          AS `id`,
		`m_item_name`        AS `name`,
		`m_item_fixamount`   AS `famt`,
		`m_item_resource`    AS `reso`,
		`m_item_currency_id` AS `cur_id`,
		`m_item_taxtype`
	FROM  `".TABLE_M_ITEM."`
	WHERE `state` = '1'
	ORDER BY `m_item_id`
");

while($m = tep_db_fetch_array($query_m)){
	$item_ar[$m['id']] = $m;
}

//▼通貨レート
$query_m =  tep_db_query("
	SELECT
		`m_currency_id`       AS `id`,
		`m_currency_now_rate` AS `rate`
	FROM  `".TABLE_M_CURRENCY_NOW."`
	WHERE    `state` = '1'
	ORDER BY `m_currency_id` ASC
");

$rate_ar[0] = 1;
while($m = tep_db_fetch_array($query_m)) {
	$rate_ar[$m['id']] = $m['rate'];
}

//★変数設定
$p_id            = $_POST['m_plan_id'];
$p_name          = $_POST['m_plan_name'];
$p_limited_id    = $_POST['m_plan_limited_id'];
$p_rank_id       = $_POST['m_plan_rank_id'];
$p_grank_id      = $_POST['m_plan_grank_id'];
$p_sort          = $_POST['m_plan_sort'];
$p_sum_resource  = $_POST['m_plan_sum_resource'];
$p_o_must        = $_POST['m_plan_o_must'];
$p_o_limit_times = $_POST['m_plan_o_limit_times'];
$p_o_limit_piece = $_POST['m_plan_o_limit_piece'];
$p_plan_taxtype  = $_POST['m_plan_taxtype'];
$p_caution       = $_POST['m_plan_caution'];

//▼JS対応
$p_item  = $_POST['item'];
$p_point = $_POST['poi'];

//▼手数料
$culc   = $_POST['culc'];	//手数料配列


//-------- データ処理 --------//
if($_POST['act'] == 'process'){
	require('./mutil/mut_mplan_process.php');
}


//----- 表示フォーム -----//
if($end == 'end'){
	
	$input_form = '<p>'.$end_text.'</p>';
	$input_form.= '<a href="'.$form_action_to.'">商品の設定を続ける</a>';
	
}else{
	
	//▼初期設定
	require('./mutil/mut_mplan_init.php');
	
	//▼表示フォーム
	require('./mutil/mut_mplan_form.php');
	
}

//script 引継ぎ用
$jsonItem = json_encode($item_ar);
$jsonRate = json_encode($rate_ar);
$jsonPlan = json_encode($plan_ar);

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
	<link rel="stylesheet" type="text/css" href="../css/common.css"   media="all">
	<link rel="stylesheet" type="text/css" href="../css/master.css"   media="all">
	<script src="../js/jquery-3.2.1.min.js" charset="UTF-8"></script>
	<style>
		.input_list{width:100%;}
		.txarea {width:100%;height:80px;resize:none;}
		.notable th,.notable td{background:#FFF;border:none;}
		.it_name{max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
		.pd_m{padding:2px 5px;}
		.chAble{font-size:11px;padding:2px;}
		.ok{font-size:11px; font-weight:800; color:#00F;}
		.ng{font-size:11px; font-weight:800; color:#F00;}
		#RmvCulc{display:none;}
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
					
					<h2>追加購入商品設定</h2>
					<div class="m_area">
						<div class="m_list_area_f">
							<?php echo $input_list;?>
						</div>
						<div class="m_input_area_f">
							<div class="m_inner">
								<?php echo $err_text;?>
								<?php echo $input_form;?>
							</div>
						</div>
					</div>
					<div class="float_clear"></div>
				</div>
			</div>
		</div>
		<div class="float_clear"></div>
	</div>
	
	<div id="footer">
		<?php require('inc_master_footer.php'); ?>
	</div>
</div>
<?php require('./mutil/mut_mplan_script.php');?>
<?php require('./mutil/mut_fee_script.php');?>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
