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
$link_to        = 'user_order_edit.php';

/*-------- リスト取得 --------*/
//▼通貨リスト
$cur_ar        = zCurrencyList();
$base_currency = $cur_ar[0];


/*-------- 検索条件 --------*/
//search_box
//▼検索条件
$s_name = ($_POST['s_name'])? $_POST['s_name'] : '';
$s_fsid = ($_POST['s_fsid'])? $_POST['s_fsid'] : '';
$s_mail = ($_POST['s_mail'])? $_POST['s_mail'] : '';


//▼名前条件
if($s_name){
	$search_name = "AND(`vo`.`u_name` LIKE '%".tep_db_input($s_name)."%')";
	
}else{
	$search_name = '';
}

//▼FSID条件
if($s_fsid){
	$search_fsid = "AND `vo`.`fs_id` LIKE '%".$s_fsid."%'";
}else{
	$search_fsid = "";
}

//▼メールアドレス条件
if($s_fsid){
	$search_mail = "AND `vo`.`u_mail` LIKE '%".$s_mail."%'";
}else{
	$search_mail = "";
}

//▼出荷前　＞　入金済み注文データ
$order_query = tep_db_query("
	SELECT 
		`o`.`user_order_id`        AS `order_id`,
		`o`.`user_order_sort`      AS `o_sort`,
		`o`.`plan_id`              AS `o_plan_id`,
		`o`.`user_order_num`       AS `o_num`,
		`o`.`rank_id`              AS `o_rank_id`,
		`mr`.`m_rank_name`         AS `o_rank_name`,
		`o`.`user_order_amount`    AS `o_amount`,
		`o`.`user_order_condition` AS `o_condition`,
		`o`.`user_order_remarks`   AS `o_remarks`,
		DATE_FORMAT(`o`.`user_order_date_application`,'%Y-%m-%d') AS `d_appli`,
		DATE_FORMAT(`o`.`user_order_date_limit`      ,'%Y-%m-%d') AS `d_limit`,
		DATE_FORMAT(`o`.`user_order_date_done`       ,'%Y-%m-%d') AS `d_done`,
		DATE_FORMAT(`o`.`user_order_date_mail_send`  ,'%Y-%m-%d') AS `d_smail`,
		DATE_FORMAT(`o`.`user_order_date_figure`     ,'%Y-%m-%d') AS `d_figure`,
		`o0`.`recmoney`,
		DATE_FORMAT(`o0`.`recdate`,'%Y-%m-%d')                    AS `recdate`,
		DATE_FORMAT(`o0`.`sendoffdate1`              ,'%Y-%m-%d') AS `d_sendoff1`,
		`d`.`user_o_detail_js_plan` AS `plans`,
		`d`.`user_o_detail_js_item` AS `items`,
		`o`.`position_id` AS `position_id`,
		`u`.`memberid`    AS `user_id`,
		`u`.`login_id`,
		`u`.`email`,
		(CASE WHEN (`u`.`name1` is not null) THEN `u`.`name1` WHEN (`u`.`name2` is not null) THEN `u`.`name2` ELSE NULL end) AS `name`,
		`p`.`position_condition` AS `p_cond`
	FROM `".TABLE_USER_ORDER."` `o` 
	LEFT JOIN `".TABLE_MEM00000."` `u`  ON  `u`.`memberid`      = `o`.`user_id`
	LEFT JOIN `".TABLE_M_RANK."`  `mr`  ON `mr`.`m_rank_id`     = `o`.`rank_id`
	LEFT JOIN `".TABLE_POSITION."` `p`  ON  `p`.`memberid`      = `o`.`user_id`
	LEFT JOIN `".TABLE_ODR00000."` `o0` ON `o0`.`orderid`       = `o`.`user_order_id`
	LEFT JOIN `".TABLE_USER_O_DETAIL."` `d`  ON  `d`.`user_order_id` = `o`.`user_order_id`
	WHERE (`o`.`state` = '1')
	AND ((`mr`.`state` = '1') OR (`mr`.`state` IS NULL))
	AND  `p`.`state`  = '1'
	AND  `o`.`user_order_condition` = 'a'
	AND `o0`.`sendoffdate1` IS NOT NULL
	AND  `d`.`state` = '1'
	".$search_name." 
	".$search_fsid."
	".$search_mail."
	ORDER BY `o`.`user_order_id` DESC 
");


//▼データ取得
if (tep_db_num_rows($order_query)){
	
	//▼表示列作成
	require('./mutil/mut_order_back_0_rows.php');
	
}else{
	$order_list_tr = '<tr><td colspan="6">注文の履歴がありません</td></tr>';
}


//▼登録内容振り分け
$input_auto = '<input type="hidden" name="top" value="back_after">';


//▼登録フォーム
require('./mutil/mut_order_back_1_form.php');

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
	<link rel="stylesheet" type="text/css" href="../js/jquery-ui/jquery-ui.min.css">
	<link rel="stylesheet" type="text/css" href="../css/master.css"   media="all">
	<script src="../js/jquery-3.2.1.min.js" charset="UTF-8"></script>
	<script src="../js/jquery-migrate-1.4.1.min.js"   charset="UTF-8"></script>
	<script src="../js/jquery-ui/jquery-ui.min.js"    charset="UTF-8"></script>
	<script type="text/javascript">
		var opmonth = ["1","2","3","4","5","6","7","8","9","10","11","12"];
		var opday   = ["日","月","火","水","木","金","土"];
		var dopt ={
			dateFormat :'yy-mm-dd',
			changeMonth:true,
			monthNames:opmonth,monthNamesShort:opmonth,
			dayNames:opday,dayNamesMin:opday,dayNamesShort:opday,
			showMonthAfterYear:true
		}
		
		$(function() {
			$('#dBack').datepicker(dopt);
			$('#dCulc').datepicker(dopt);
		});
	</script>
	<style>
		.form_outer{margin:0 auto; max-height:500px; overflow:auto;}
		.input_form .o_num{font-size:16px;padding:0 5px;}
		.notable td{border:none;font-size:11px;}
		
		.order_list th{line-height:110%;}
		.ok{color:#00F; font-weight:800; text-align:center;}
		.done{background:#E0E0E0;}
		.name_err{background:#F00; color:#FFF; line-height:100%;padding:5px 10px;font-weight:800;}
		
		.oprBack,.ch_item{padding:2px 5px; font-size:11px}
		.pl_items{display:none; background:#F4F4F4; padding:5px;}
	</style>
</head>
<body id="body">
<div id="wrapper">
	<?php echo $pop;?>
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
					
					<h2>注文一覧</h2>
					<div>
						<?php echo $search_box;?>
						<?php echo $order_list;?>
					</div>
				</div>
			</div>

			<div class="float_clear"></div>
		</div>
	</div>
	
	<div id="footer">
		<?php require('inc_master_footer.php'); ?>
	</div>
</div>
<?php require('./mutil/mut_order_back_2_script.php');?>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
