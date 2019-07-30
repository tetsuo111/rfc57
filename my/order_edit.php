<?php 
require('includes/application_top.php');

if($_COOKIE['user_id']){
	$user_id        = $_COOKIE['user_id'];
	$position_id    = $_COOKIE['position_id'];
	$user_email     = $_COOKIE['user_email'];
	$head_user_name = $_COOKIE['user_name'].'様';
	
}else{
	tep_redirect('../logout.php', '', 'SSL');
}


//▼とび先設定
$form_action_to   = basename($_SERVER['PHP_SELF']);
$form_action_card = 'pay_card.php';

$uorder_id = ($_POST['uorder_id'])? $_POST['uorder_id'] : $_GET['uorder_id'];
$cont_set  = '?uorder_id='.$uorder_id;


//----- 注文情報 -----//
//▼注文
$query_order =  tep_db_query("
	SELECT
		`user_order_id`         AS `id`,
		`plan_id`,
		`user_order_num`        AS `num`,
		`user_order_sort`       AS `sort`,
		`user_order_condition`  AS `coidition`,
		DATE_FORMAT(`user_order_date_limit`,'%Y-%m-%d')       AS `limit`,
		DATE_FORMAT(`user_order_date_application`,'%Y-%m-%d') AS `appli`
	FROM  `".TABLE_USER_ORDER."`
	WHERE `state` = '1'
	AND   `user_order_condition` = '1'
	AND   `user_order_id` = '".tep_db_input($uorder_id)."'
");

if($od = tep_db_fetch_array($query_order)){
	
	$m_plan_id   = $od['plan_id'];
	$sort        = $od['sort'];
	$order_num   = $od['num'];		//注文個数
	$order_limit = $od['appli'];	//入金期限
	
	
	//▼注文詳細を取得
	$query =  tep_db_query("
		SELECT
			`user_o_detail_js_plan` AS `js_plan`
		FROM  `".TABLE_USER_O_DETAIL."`
		WHERE `state` = '1'
		AND   `user_order_id` = '".tep_db_input($uorder_id)."'
	");

	$dt = tep_db_fetch_array($query);
	$cart_ar = zJSToArry($dt['js_plan']);
	
	
	//▼支払情報
	$query_c =  tep_db_query("
		SELECT
			`c`.`user_o_charge_id`        AS `ch_id`,
			`c`.`payment_id`              AS `pid`,
			`c`.`user_o_charge_condition` AS `condition`,
			`p`.`m_payment_code`          AS `pcode`
		FROM      `".TABLE_USER_O_CHARGE."` `c`
		LEFT JOIN `".TABLE_M_PAYMENT."`     `p` ON `p`.`m_payment_id` = `c`.`payment_id`
		WHERE `c`.`state` = '1'
		AND   `c`.`user_order_id` = '".tep_db_input($uorder_id)."'
		AND   `p`.`state` = '1'
	");
	
	while($a = tep_db_fetch_array($query_c)){
		
		if($a['pcode']  == '5'){
			$p_chid      = $a['ch_id'];
			$p_condition = $a['condition'];
		}
	}
	
}else{
	//▼注文対象
	echo '<script>alert("変更・削除できる注文がありません");location.href="index.php";</script>';
}



/*====================================
	データ処理
====================================*/
if(($_POST['act_send'] == 'send')AND(!$_POST['sub_clear'])){
	
	/*----- データ準備 -----*/
	//▼リロード対策
	if($_POST['ticket'] === $_COOKIE['ticket']){
		tep_cookie_del('ticket');
		//echo '一回目';//そのまま
	}else{
		tep_cookie_del('ticket');
		tep_redirect('index.php', '', 'SSL');
	}
	
	/*----- DB登録 -----*/
	if($_POST['act_del']){
		
		foreach($cart_ar AS $k => $v){
			
			//▼価格を抽出
			$price = $v['price'];
			
			$pr_a = '';
			$pr_b = '';
			foreach($price AS $a => $p){
				$pr_a.= $p['amt_a'].$p['name']."\n";
				$pr_b.= $p['amt_b'].$p['name']."\n";
			}
			
			//▼送信用配列
			$dcart_ar[] = array(
				'plan_id' => $v['plan_id'],
				'name'    => $v['name'],
				'num'     => $v['num'],
				'pricea'  => $pr_a,
				'priceb'  => $pr_b
			);
		}
		
		
		//▼データ取消し　＞注文取消は「c」
		$del_array = array('date_update'=>'now()','state'=>'c');
		$w_dset    = "`user_order_id`='".tep_db_input($uorder_id)."' AND `state`='1'";
		
		//▼更新用DB
		tep_db_perform(TABLE_USER_ORDER,$del_array,'update',$w_dset);
		
		
		//▼カート削除
		$del_c_array = array(
			'user_o_cart_date_out' => 'now()',
			'date_update'          => 'now()',
			'state'                => 'c'
		);
		
		$w_c_dset = "`order_id`='".tep_db_input($uorder_id)."' AND `state`='1'";
		tep_db_perform(TABLE_USER_O_CART,$del_c_array,'update',$w_c_dset);
		
		
		//========================
		//CIS対応
		//========================
		//▼削除用配列
		$del_odr00_ar = array(
			'editdate' => 'now()',
			'editflag' => '-1',
			'candate'  => 'now()'
		);
		
		//▼削除実行
		$w_o00_set = "`orderid`='".tep_db_input($uorder_id)."'";
		tep_db_perform(TABLE_ODR00000,$del_odr00_ar,'update',$w_o00_set);
		
		
		//----- メール送信 -----//
		//▼会員ID
		$query = tep_db_query("SELECT `login_id` FROM `".TABLE_MEM00000."` WHERE `memberid` = '".tep_db_input($user_id)."'");
		$mem0  = tep_db_fetch_array($query);
		
		//▼メール設定
		$email      = zGetUserEmail($user_id);	//送信メールアドレス
		$Efs_id     = $mem0['login_id'];		//fsid
		$Euser_name = $_COOKIE['user_name'];
		$Eorder_id  = $uorder_id;				//注文番号
		$Ecart_ar   = $dcart_ar;				//注文カート内容
		
		//▼送信実行
		Email_Order_Del(
			$EmailHead,
			$EmailFoot,
			$email,
			$Efs_id,
			$Euser_name,
			$Eorder_id,
			$Ecart_ar
		);
		
		//▼終了処理
		$end = 'del';
		
	}else{
		
		$end = 'end';
	}
	
	
}else{
	
	//▼自動送信用
	$input_auto = '<input type="hidden" name="act_send" value="send">';
	$input_auto.= '<input type="hidden" name="act"      value="process">';
	
	//▼登録ボタン
	$dis_s = ($uorder_id)? '':'disabled="disabled"';
	$order_submit = '<input type="button" class="btn" value="内容確認" '.$dis_s.' id="Act">';
	$order_submit.= '<a class="btn btn_cancel spc10_l" href="'.$form_action_to.$cont_set.'">やり直す</a>';

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
if($end == 'del'){

	$order_form = '<p>注文を取消しました</p>';
	$order_form.= '<p>取消した注文の内容をメールで送りました。取消しのメールが届いていることをご確認ください。</p>';

}else if($end == 'end'){
	
	$order_form = '<p>注文の変更を登録しました</p>';
	$order_form.= '<p>詳細をご登録のメールアドレスに送りました。変更内容をご確認の上お支払いください。</p>';

}else{
	
	
	//----- 注文詳細情報 -----//
	foreach($cart_ar AS $k => $v){
		
		//▼価格を抽出
		$price = $v['price'];
		
		//▼商品価格を作成
		$pr_a = '';
		$pr_b = '';

		foreach($price AS $a => $p){
			$pr_a.= '<p>'.$p['amt_a'].$p['name'].'</p>';
			$pr_b.= '<p>'.$p['amt_b'].$p['name'].'</p>';
		}
		
		$dlist_in.= '<tr>';
		$dlist_in.= '<td>'.$v['plan_id'].'</td>';
		$dlist_in.= '<td>'.$v['name'].'</td>';
		$dlist_in.= '<td>'.$pr_a.'</td>';
		$dlist_in.= '<td>'.$v['num'].'</td>';
		$dlist_in.= '<td>'.$pr_b.'</td>';
		$dlist_in.= '</tr>';
	}
	
	
	//▼選択内容表示
	$dhead_in = '<th>商品番号</th>';
	$dhead_in.= '<th>商品名</th>';
	$dhead_in.= '<th>単価</th>';
	$dhead_in.= '<th>個数</th>';
	$dhead_in.= '<th>合計</th>';
	
	$oder_detail = '<table class="table">';
	$oder_detail.= '<thead>';
	$oder_detail.= '<tr>'.$dhead_in.'</tr>';
	$oder_detail.= '</thead>';
	$oder_detail.= '<tbody>';
	$oder_detail.= $dlist_in;
	$oder_detail.= '</tbody>';
	$oder_detail.= '</table>';
	
	
	//----- 注文表示 -----//
	//▼注文内容
	$condt = ($od['condition'] == 'a')? '確認済':'<span class="alert">入金待</span>';
	
	//▼削除処理
	$order_del = '<form action="" name="dlF'.$uorder_id.'" method="POST">';
	$order_del.= '<input type="hidden" name="uorder_id" value="'.$uorder_id.'">';	//注文対策
	$order_del.= '<input type="hidden" name="ticket"    value="'.$ticket.'">';		//リロード対策
	$order_del.= '<input type="hidden" name="act_send"  value="send">';
	$order_del.= '<input type="hidden" name="act_del"   value="del">';
	$order_del.= '<input type="button" class="btn"      value="注文を取り消す" onclick="lzOdrDel(\''.$uorder_id.'\');">';
	$order_del.= '</form>';
	
	
	//▼クレジットで決済が未完了の場合
	if($p_chid && $p_condition != 'a'){
		$re_card = '<form action="'.$form_action_card.'" style="margin:10px 0;" method="POST">';
		$re_card.= '<input type="hidden" name="act_re" value="recard">';				//注文番号
		$re_card.= '<input type="hidden" name="odrid"  value="'.$uorder_id.'">';		//注文番号
		$re_card.= '<input type="hidden" name="chid"   value="'.$p_chid.'">';			//請求番号
		$re_card.= '<input type="submit" class="btn"   value="再決済">';
		$re_card.= '</form>';
	}
	
	//▼注文情報
	$order_in = '<tr>';
	$order_in.= '<td>'.$od['id'].'</td>';
	$order_in.= '<td>'.$od['appli'].'</td>';
	$order_in.= '<td>'.$od['limit'].'</td>';
	$order_in.= '<td>'.$condt.'</td>';
	$order_in.= '<td>'.$order_del.$re_card.'</td>';
	$order_in.= '</tr>';
	
	//▼表示項目
	$l_head = '<th>注文番号</th>';
	$l_head.= '<th>注文日</th>';
	$l_head.= '<th>入金予定</th>';
	$l_head.= '<th>状況</th>';
	$l_head.= '<th>操作</th>';
	
	$oder_list = '<table class="table">';
	$oder_list.= '<thead>';
	$oder_list.= '<tr>'.$l_head.'</tr>';
	$oder_list.= '</thead>';
	$oder_list.= '<tbody>';
	$oder_list.= $order_in;
	$oder_list.= '</tbody>';
	$oder_list.= '</table>';
	
	
	//----- 登録フォーム -----//
	$order_form = $oder_list;
	$order_form.= '<div class="spc50">';
	$order_form.= '<h3>Order detail</h3>';
	$order_form.= $oder_detail;
	$order_form.= '</div>';
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
		var opmonth = ["1","2","3","4","5","6","7","8","9","10","11","12"];
		var opday   = ["日","月","火","水","木","金","土"];
		var dopt ={
			dateFormat :'yy-mm-dd',
			changeMonth:true,
			minDate: new Date(),
			monthNames:opmonth,monthNamesShort:opmonth,
			dayNames:opday,dayNamesMin:opday,dayNamesShort:opday,
			showMonthAfterYear:true
		}
		$(function() {$('#dLimit').datepicker(dopt);});
	</script>
	<style>
		.announce_table{margin:5px 0px 5px 0px; width:600px;}
		.announce_table tr{}
		.announce_table th{background:#DFDFDF;border:1px #FFFFFF solid; padding:2px 5px 2px 5px;}
		.announce_table td{background:#FFFFFF;border:1px #DFDFDF solid; padding:2px 5px 2px 5px;}
		
		#dPay{display:none;padding:5px; max-width:600px; width:100%; background:#F6F6F6;}
		
		.p_area{width:100%; border:1px solid #E4E4E4; padding:10px; overflow:hidden; border-radius:10px;}
		
		.table thead th{background:#F4F4F4;}
		.table tbody tr{border-bottom:1px solid #E9E9E9;}
		.table.list_table tr{border:1px solid #E9E9E9;}
		.table.list_table tr .notable tr{ border:none;}
		
		.notable td{border:none; padding:2px 5px;}
		.notable2 {width:100%;}
		.notable2 tr{border:none;}
		
		.paytable th,.paytable td{padding:7px 10px;}
		.paytable td{text-align:right;}
		.paytable tr{border-bottom:1px solid #E4E4E4;}
		
		.sub_in{padding:5px 0;}
		.lsel{background:#F9F9F9;}
		
		#ps2,#ps3,#ps4{display:none;}
		
		#ps2.isShow,
		#ps3.isShow,
		#ps4.isShow{display:block;}
		
		.cl2{background:#F4F4F4; border-bottom:1px solid #E4E4E4;border-top:1px solid #E4E4E4; }
		.btn_cancel{background:#CFCFCF; color:#666;}
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

					<h2>Order edit</h2>
					<div>
						<?php echo $order_form;?>
					</div>
				</div>
			</div>
		</div>
	</div>
		
	<div id="footer">
		<?php require('inc_user_footer.php');?>
	</div>
</div>
<?php require('../util/inc_order_control.php');?>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
