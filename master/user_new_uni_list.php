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

//▼基準通貨
$base_currency = zGetSysSetting('sys_base_currency');


/*-------- リスト取得 --------*/
$cur_ar  = zCurrencyList();		//通貨リスト
$rank_ar = zRankList();			//ランクリスト


/*-------- 検索条件 --------*/
//search_box
//▼検索条件
$s_name = ($_POST['s_name'])? $_POST['s_name'] : '';
$s_fsid = ($_POST['s_fsid'])? $_POST['s_fsid'] : '';
$s_mail = ($_POST['s_mail'])? $_POST['s_mail'] : '';


//▼名前条件
if($s_name){
	$search_name = "AND((`ui`.`user_name`      LIKE '%".tep_db_input($s_name)."%')OR(`ui`.`user_name2`      LIKE '%".tep_db_input($s_name)."%')";
	$search_name.=   "OR(`ui`.`user_name_kana` LIKE '%".tep_db_input($s_name)."%')OR(`ui`.`user_name_kana2` LIKE '%".tep_db_input($s_name)."%'))";
	
}else{
	$search_name = '';
}

//▼FSID条件
if($s_fsid){
	$search_fsid = "AND `u`.`fs_id` LIKE '%".$s_fsid."%'";
}else{
	$search_fsid = "";
}

//▼メールアドレス条件
if($s_fsid){
	$search_mail = "AND `u`.`user_email` LIKE '%".$s_mail."%'";
}else{
	$search_mail = "";
}


/*----- データ取得 -----*/
//▼新規注文情報　＞新規、入金完了、APLogin未発行の人
$order_query = tep_db_query("
	SELECT 
		`u`.`user_id`,
		`u`.`fs_id`,
		`u`.`user_email`                  AS `mail`,
		`o`.`user_order_id`               AS `order_id`,
		`o`.`position_id`,
		`o`.`user_order_user_rank_id`     AS `rank_id`,
		`o`.`user_order_sort`             AS `o_sort`,
		`o`.`user_order_pay_currency_id`  AS `o_cur_id`,
		`o`.`user_order_amount`           AS `o_amt`,
		`o`.`user_order_pay_rate`         AS `o_rate`,
		`o`.`user_order_pay_rate_amount`  AS `o_rate_amt`,
		`o`.`user_order_peyment_amount`   AS `o_paid_amt`,
		`o`.`user_order_condition`        AS `o_condition`,
		`o`.`user_order_remarks`          AS `remarks`,
		DATE_FORMAT(`o`.`user_order_date_application`,'%Y-%m-%d') AS `d_appli`,
		DATE_FORMAT(`o`.`user_order_date_limit`      ,'%Y-%m-%d') AS `d_limit`,
		DATE_FORMAT(`o`.`user_order_date_payment`    ,'%Y-%m-%d') AS `d_paid`,
		DATE_FORMAT(`o`.`user_order_date_mail_send`  ,'%Y-%m-%d') AS `d_smail`,
		CONCAT(`ui`.`user_name`,'　',`ui`.`user_name2`)  AS `name`
	FROM      `".TABLE_USER_ORDER."`  AS  `o` 
	LEFT JOIN `".TABLE_USER."`        AS  `u` ON  `o`.`user_id`     =  `u`.`user_id`
	LEFT JOIN `".TABLE_USER_INFO."`   AS `ui` ON  `o`.`user_id`     = `ui`.`user_id`
	LEFT JOIN `".TABLE_POSITION."`    AS  `p` ON  `o`.`position_id` =  `p`.`position_id`
	WHERE `o`.`state` = '1'
	AND   `o`.`user_order_sort`      = 'a'
	AND   `o`.`user_order_condition` = 'a'
	AND   `u`.`state`  = '1' 
	AND  ((`ui`.`state`  = '1')OR(`ui`.`state` IS NULL))
	AND   `p`.`state`  = '1'
	AND   `p`.`position_condition` IS NULL
	".$search_name." 
	".$search_fsid."
	".$search_mail."
	ORDER BY `o`.`user_order_id` DESC 
");


//▼データ取得
if (tep_db_num_rows($order_query) ) {
	
	//▼基準通貨
	$base_cr = $cur_ar[0];
	
	while ($a = tep_db_fetch_array($order_query)) {
		
		//▼通貨
		$cur = $cur_ar[$a['o_cur_id']];
		
		//▼注文状況
		if($a['o_condition'] == 'a'){
			$condition = '<span class="ok">確認済</span>';
			
		}else if($a['o_condition'] == 'c'){
			$condition = '<span class="alert">キャンセル</span>';
			
		}else{
			$condition = '入金待';
		}
		
		//▼操作
		$disabled  = 'disabled';
		$operation = '<button type="button" class="uniOn" data-pid="'.$a['position_id'].'" data-rank="'.$a['rank_id'].'" '.$disabled.'>ユニのポジションを有効にする</button>';
		
		$o_amt      = zCheckNum($a['o_amt']);
		$o_rate_amt = zCheckNum($a['o_rate_amt']);
		$o_paid_amt = zCheckNum($a['o_paid_amt']);
		$o_type     = ($a['o_sort'] == 'a')? '<span class="alert">'.$OrderSortArray[$a['o_sort']].'</span>': $OrderSortArray[$a['o_sort']];
		
		$order_list_tr.='<td>'.$a['order_id'].'</td>';
		$order_list_tr.='<td>'.$a['user_id'].'</td>';
		$order_list_tr.='<td>'.$a['name'].'</td>';
		$order_list_tr.='<td>'.$a['fs_id'].'</td>';
		$order_list_tr.='<td>'.$a['mail'].'</td>';
		$order_list_tr.='<td>'.$o_type.'</td>';
		$order_list_tr.='<td>'.$a['d_appli'].'</td>';
		$order_list_tr.='<td>'.$o_rate_amt.' '.$cur.'</td>';
		$order_list_tr.='<td>'.(($a['d_paid'])?     $a['d_paid']:'-').'</td>';
		$order_list_tr.='<td>'.(($a['o_paid_amt'])? $o_paid_amt.' '.$cur:'-').'</td>';
		$order_list_tr.='<td>'.$a['remarks'].'</td>';
		$order_list_tr.='<td>'.$condition.'</td>';
		$order_list_tr.='<td>'.$rank_ar[$a['rank_id']].'</td>';
		$order_list_tr.='<td>'.$operation.'</td>';
	}
	
}else{
	$order_list_tr = '<tr><td colspan="6">注文の履歴がありません</td></tr>';
}



//▼リスト見出し
$list_head ='<th>注文<br>番号</th>';
$list_head.='<th>顧客<br>番号</th>';
$list_head.='<th>顧客名</th>';
$list_head.='<th>FSID</th>';
$list_head.='<th>メールアドレス</th>';
$list_head.='<th>種類</th>';
$list_head.='<th>注文日</th>';
$list_head.='<th>支払<br>金額</th>';
$list_head.='<th>入金<br>確認日</th>';
$list_head.='<th>入金<br>金額</th>';
$list_head.='<th>メモ</th>';
$list_head.='<th>状況</th>';
$list_head.='<th>開始<br>ランク</th>';
$list_head.='<th>操作</th>';


//▼表示リスト
$order_list = '<table class="order_list" style="font-size:8pt;">';
$order_list.= '<tr>'.$list_head.'</tr>';
$order_list.= $order_list_tr;
$order_list.= '</table>';


//▼検索フォーム
$search_box = '<div style="margin:10px 0;">';
$search_box.= '<form name="search" action="'.$form_action_to.'" method="POST">';
$search_box.= 'お名前・カナ ';
$search_box.= '<input type="text" style="width:200px; padding:5px 5px;" name="s_name" value="'.$s_name.'"> ';
$search_box.= '　FSID ';
$search_box.= '<input type="text" style="width:100px; padding:5px 5px;" name="s_fsid" value="'.$s_fsid.'"> ';
$search_box.= '<input type="submit" style="width:60px; padding:5px 0px;" value="検索"> ';
$search_box.= '<input type="button" style="width:60px; padding:5px 0px;" value="リセット" OnClick="window.location.href=\''.$form_action_to.'\'"> ';
$search_box.= '</form>';
$search_box.= '</div>';
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
		.ok{color:#00F; font-weight:800;}
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
<script src="../js/MyHelper.js"></script>
<script>
	var Cat1 = new jSendPostDataAj('xml_uni_on.php');
	
	$('.uniOn').on('click',function(){
		
		var aa = $(this).attr('data-pid');
		var bb = $(this).attr('data-rank');
		
		var Data = {top:'union',sendid:aa,rankid:bb};
		var Obj   = Cat1.sendPost(Data);
		
		if(Obj){
			Obj.done(function(response){
				if(response == 'ok'){
					alert('ユニのポジションを有効にしました');
					location.href="<?php echo $form_action_to;?>";
				}else{
					alert('情報が登録できません');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown){
				alert("データの登録に失敗しました");
			});
		}else{
			alert("データが不正です");
		}
	});

</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
