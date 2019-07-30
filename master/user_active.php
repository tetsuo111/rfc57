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

//▼注文データ
$order_query = tep_db_query("
	SELECT
		`vo`.`position_id`,
		`vo`.`user_id`,
		`vo`.`fs_id`,
		`vo`.`u_mail`    AS `mail`,
		`vo`.`order_id`,
		`vo`.`o_sort`,
		`vo`.`o_rank_id` AS `rank_id`,
		`vo`.`o_rank_name`,
		`vo`.`o_plan_name`,
		`vo`.`o_amount`,
		`vo`.`o_condition`,
		`vo`.`o_remarks` AS `remarks`,
		`p`.`position_condition` AS `p_cond`,
		`vo`.`d_appli`,
		`vo`.`d_limit`,
		`vo`.`d_done`,
		`vo`.`d_smail`,
		`vo`.`d_figure`,
		`vo`.`u_name`  AS `name`
	FROM      `".VIEW_ORDER."`     `vo`
	LEFT JOIN `".TABLE_POSITION."` `p` ON `vo`.`position_id` = `p`.`position_id`
	WHERE `vo`.`o_sort`      = 'a'
	AND   `vo`.`o_condition` = 'a'
	AND   `p`.`state`  = '1'
	AND   `p`.`position_condition` IS NULL
	".$search_name." 
	".$search_fsid."
	".$search_mail."
	ORDER BY `vo`.`order_id` DESC 
");


//▼データ取得
if (tep_db_num_rows($order_query) ) {
	
	/*----- 注文情報 -----*/
	//▼注文情報
	while ($b = tep_db_fetch_array($order_query)) {
		
		$order_ar[$b['order_id']]['order'] = $b;
		
		//▼紹介者
		$for_get_id .= (($for_get_id)?  ',':'')."'".$b['inv']."'";
		$for_get_odr.= (($for_get_odr)? ",":"")."'".$b['order_id']."'";
		
		//▼確認用
		if($b['mail']){$ch_mail[$b['mail']]++;}
	}
	
	
	//▼請求取得
	$charge_query = tep_db_query("
		SELECT
			`order_id`,
			`c_currency_name` AS `cur_name`,
			`r_amount`
		FROM `".VIEW_CHARGE."`
		WHERE `order_id` IN (".$for_get_odr.")
		ORDER BY `order_id`,`c_currency_id`
	");
	
	
	while ($ch = tep_db_fetch_array($charge_query)) {
		$order_ar[$ch['order_id']]['charge'][] = $ch;
	}
	
	
	/*----- 紹介者情報 -----*/
	//▼紹介者情報
	$position_query = tep_db_query("
		SELECT 
			`p`.`position_id`        AS `p_id`,
			`p`.`position_condition` AS `p_active`,
			CONCAT(`ui`.`user_name`,'　',`ui`.`user_name2`)  AS `name`
		FROM      `".TABLE_POSITION."` AS `p`
		LEFT JOIN `".TABLE_USER_INFO."` AS `ui` ON `p`.`user_id` = `ui`.`user_id`
		WHERE `p`.`state`  = '1'
		AND ((`ui`.`state`  = '1')OR(`ui`.`state` IS NULL))
		AND `p`.`position_id` IN(".$for_get_id.")
	");

	while ($c = tep_db_fetch_array($position_query)) {
		$inv_ar[$c['p_id']] = array('active'=>$c['p_active'],'name'=>$c['name']);
	}
	
	
	/*----- 表示成形 -----*/
	//▼紹介者情報
	foreach($order_ar AS $dodr){
		
		//▼各情報
		$a      = $dodr['order'];
		$charge = $dodr['charge'];
		
		//▼注文状況
		if($a['o_condition'] == 'a'){
			$condition = '<span class="ok">確認済</span>';
			
		}else if($a['o_condition'] == 'c'){
			$condition = '<span class="alert">キャンセル</span>';
			
		}else{
			$condition = '入金待';
		}
		
		//▼メール確認
		$err_mail_cl = ($ch_mail[$a['mail']] > 1)? 'class="ermail"':'';
		
		//▼操作設定
		if($err_mail_cl){
			$operation.= '<p class="alert">メールアドレスが重複しています</p>';
		}else{
			//▼操作
			//$disabled   = 'disabled';
			$operation  = '<button type="button" class="uniOn" data-pid="'.$a['position_id'].'" data-rank="'.$a['rank_id'].'" data-odr="'.$a['order_id'].'" '.$disabled.'>ユニのポジションを有効にする</button>';
		}

		/*
		$o_amt      = zCheckNum($a['o_amt']);
		$o_rate_amt = zCheckNum($a['o_rate_amt']);
		$o_paid_amt = zCheckNum($a['o_paid_amt']);
		*/
		$o_paid_amt = '';
		foreach($charge AS $dch){
			$o_paid_amt.= '<p>'.$dch['r_amount'].' '.$dch['cur_name'].'</p>';
		}
		
		$o_type     = ($a['o_sort'] == 'a')? '<span class="alert">'.$OrderSortArray[$a['o_sort']].'</span>': $OrderSortArray[$a['o_sort']];
		
		$order_list_tr.= '<tr>';
		$order_list_tr.= '<td>'.$a['order_id'].'</td>';
		$order_list_tr.= '<td>'.$a['user_id'].'</td>';
		$order_list_tr.= '<td>'.$a['position_id'].'</td>';
		$order_list_tr.= '<td>'.$a['name'].'</td>';
		$order_list_tr.= '<td>'.$a['fs_id'].'</td>';
		$order_list_tr.= '<td>'.$a['mail'].'</td>';
		$order_list_tr.= '<td>'.$o_type.'</td>';
		$order_list_tr.= '<td>'.$a['d_appli'].'</td>';
		$order_list_tr.= '<td>'.(($a['d_done'])?     $a['d_done']:'-').'</td>';
		$order_list_tr.= '<td>'.$o_paid_amt.'</td>';
		$order_list_tr.= '<td>'.$a['remarks'].'</td>';
		$order_list_tr.= '<td>'.$condition.'</td>';
		$order_list_tr.= '<td>'.$a['o_rank_name'].'</td>';
		$order_list_tr.= '<td>'.$operation.'</td>';
		$order_list_tr.= '</tr>';
	}
	
}else{
	$order_list_tr = '<tr><td colspan="6">注文の履歴がありません</td></tr>';
}



//▼リスト見出し
$list_head ='<th>注文<br>番号</th>';
$list_head.='<th>顧客<br>番号</th>';
$list_head.='<th>ポジション<br>番号</th>';
$list_head.='<th>顧客名</th>';
$list_head.='<th>会員ID</th>';
$list_head.='<th>メールアドレス</th>';
$list_head.='<th>種類</th>';
$list_head.='<th>注文日</th>';
$list_head.='<th>入金<br>完了日</th>';
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
$search_box.= '　会員ID ';
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
		.ermail{background:#FFA500; color:#FFF;}
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
		var cc = $(this).attr('data-odr');
		
		if(confirm('ポジション番号「'+aa+'」をアクティブにしますか')){

			var Data = {top:'union',sendid:aa,rankid:bb,orderid:cc};
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
		}
	});

</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
