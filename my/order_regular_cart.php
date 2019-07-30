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
$form_action_to = basename($_SERVER['PHP_SELF']);
$link_to        = 'order_regular.php';


$cur_ar   = zCurrencyList();		//通貨
$rank_ar  = zRankList();			//ランク
$point_ar = zPointList();			//ポイント
$rate_ar  = zRateList();			//通貨レート
$culc_ar  = zCulcList();			//手数料計算
$pay_ar   = zPaymentList();			//支払方法

$base_cur = $cur_ar[0];				//基準通貨


//▼カート設定の読み込み
require('../util/inc_cart_init.php');



//-------- 取得データ --------//
$p_num     = $_POST['num'];				//注文個数
$p_limit   = $_POST['order_limit'];		//入金期限
$p_payment = $_POST['odr_payment'];		//支払データ
$p_odr_fee = $_POST['odr_fee'];			//手数料データ
$p_deliver = $_POST['deliver'];			//申込みタイプ


//-------- データ処理 --------//
if(($_POST['act'] == 'process')AND($_POST['act_send'])){
	
	//▼リロード対策
	if($_POST['ticket'] === $_COOKIE['ticket']){
		tep_cookie_del('ticket');
		//echo '一回目';//そのまま
	}else{
		tep_cookie_del('ticket');
		tep_redirect('index.php', '', 'SSL');
	}
	
	//▼支払データ変換
	$odr_charge_ar = zJSToArry(str_replace('\\','' ,$p_payment));	//支払配列
	$odr_fee_ar    = zJSToArry(str_replace('\\','' ,$p_odr_fee));	//手数料配列
	
	//▼送り先指定
	$p_ssip = $_POST['ssip'];
	
	
	/*===========================
		共通対応
	===========================*/
	
	/*----- 送付先登録 -----*/
	//▼送付先住所対応
	if($_POST['noship']){
		require('../util/inc_cart_prc_shipping.php');
	}
	
	
	/*===========================
		GTW対応
	===========================*/
	
	//----- 注文情報集計 -----//
	//▼合計金額
	//cart_arに追加、手数料 + 商品合計
	require('../util/inc_cart_summary.php');
	
	
	//▼合計を集計
	foreach($cart_ar AS $k => $v){
		$total_amt  += $v['base_b'];		//カート合計金額
		$pl_resource+= $v['sumresource'];	//原資合計
		
		//登録用配列
		$as_ar[] = array('itemid'=>$v['plan_id'],'qty'=>$v['num']);
		
		//item point取得用
		$for_get_plan2.= (($for_get_plan2)? ",'":"'").$v['plan_id']."'";
	}
	
	
	//----- 請求集計 -----//
	//▼通貨別請求情報　＞　そのまま登録
	foreach($odr_charge_ar AS $curid => $pay){
		
		//CIS対応　＞　通貨は一つなので支払もひとつ
		if(!$ci_payid){
			$ci_payid = $pay['payid'];
		}
	}
	
	if($ci_payid > 0){
		$query =  tep_db_query("
			SELECT
				`m_payment_code` AS `code`
			FROM  `".TABLE_M_PAYMENT."`
			WHERE `state` = '1'
			AND   `m_payment_id` = '".tep_db_input($ci_payid)."'
		");
		$a     = tep_db_fetch_array($query);
		$pcode = $a['code'];
	}
	
	
	//----- 支払手数料集計 -----//
	//▼手数料集計
	//手数料をorgとbaseに再計算
	require('../util/inc_cart_prc_fee.php');
	
	
	//▼注文合計に追加　＞　基準通貨で計算
	$total_notax  = $total_amt;				//税抜き金額
	$total_amt   += $fee['sum']['base'];	//税込み金額
	
	
	//----- DB登録 -----//
	//▼開始日
	//申込み日+申込みコース月数
	$reg_date = date('Y-m-d',strtotime('+'.$p_deliver.' month'));
	
	$i0 = ($as_ar[0]['itemid'])? $as_ar[0]['itemid']:'null';
	$i1 = ($as_ar[1]['itemid'])? $as_ar[1]['itemid']:'null';
	$i2 = ($as_ar[2]['itemid'])? $as_ar[2]['itemid']:'null';
	
	$q0 = ($as_ar[0]['qty'])? $as_ar[0]['qty']:0;
	$q1 = ($as_ar[1]['qty'])? $as_ar[1]['qty']:0;
	$q2 = ($as_ar[2]['qty'])? $as_ar[2]['qty']:0;
	
	$regular_ar = array(
		'membertype'     => ($pcode)? $pcode:'1',	//支払方法
		'asregdate'      => $reg_date,				//開始日
		'deliverytype'   => $p_deliver,				//申込みコース
		'as_fee'         => $fee['syu']['base'],	//出荷事務手数料
		'as_daibiki_fee' => $fee['daib']['base'],	//代引き手数料
		'asitem001'      => $i0,					//商品コード1
		'asitem002'      => $i1,					//商品コード2
		'asitem003'      => $i2,					//商品コード3
		'asitemqty001'   => $q0,					//商品数量1
		'asitemqty002'   => $q1,					//商品数量2
		'asitemqty003'   => $q2						//商品数量3
	);
	
	
	//▼上書き
	$w_set = "`memberid`='".tep_db_input($user_id)."'";
	tep_db_perform(TABLE_MEM00000,$regular_ar,'update',$w_set);
	
	
	//▼カート登録完了
	foreach($cart_ar AS $k => $v){
		
		//GTW対応
		//▼登録配列
		$up_cart_ar = array(
			'order_id'               => $order_id,
			'user_o_cart_number'     => $v['num'],
			'user_o_cart_condition'  => 'a',
			'user_o_cart_date_order' => 'now()',
			'date_update'            => 'now()'
		);
		
		$w_set = "`user_o_cart_id`= '".tep_db_input($k)."' AND `state`='1'";
		tep_db_perform(TABLE_USER_O_CART,$up_cart_ar,'update',$w_set);
		
		//CIS対応
		$as01_ar = array(
			'memberid'       => $user_id,
			'itemid'         => $v['plan_id'],
			'qty'            => $v['num'],
			'input_datetime' => 'now()',
			'flg'            => '1'
		);
		
		tep_db_perform(TABLE_ASITEM00000,$as01_ar);
	}
	
	
	//----- メール送信 -----//
	//▼会員ID
	$query = tep_db_query("SELECT `login_id` FROM `".TABLE_MEM00000."` WHERE `memberid` = '".tep_db_input($user_id)."'");
	$mem0  = tep_db_fetch_array($query);
	
	//▼送信設定
	$email         = zGetUserEmail($user_id);	//送信メールアドレス
	$Efs_id        = $mem0['login_id'];			//会員ID　＞cisログインID
	$Euser_name    = $_COOKIE['user_name'];		//ユーザー名
	$Eorder_id     = $order_id;					//注文番号
	$Eorder_amount = $total_amt;				//手数料込全合計金額
	$Eorder_limit  = $p_limit;					//入金期限
	$Ecart_array   = $cart_ar;
	
	/*
	//▼送信実行
	Email_Order_Create(
		$EmailHead,
		$EmailFoot,
		$email,
		$Efs_id,
		$Euser_name,
		$Eorder_id,
		$Eorder_amount,
		$Eorder_limit,
		$Ecart_array
	);
	*/
	//▼終了処理
	$end = 'end';
	
}else if($_POST['act'] == 'process'){
	
	//▼初期設定
	$sum_in   = '';		//単体通常
	$total_in = '';		//個数通常
	
	
	//▼注文内容集計
	//cart_arに追加、手数料 + 商品合計
	require('../util/inc_cart_summary.php');
	
	
	//▼表示内容
	foreach($cart_ar AS $k => $vdt){
		
		//▼データ取得
		$plan_id = $vdt['plan_id'];
		
		//▼商品価格
		//通貨別に集計
		$sum_in   = '';
		$total_in = '';
		foreach($vdt['price'] AS $cid => $vcdt){
			
			//▼表示内容を作成
			$c0      = $vcdt['amt_a'];	//単体通常
			$c1      = $vcdt['amt_b'];	//個数通常
			$c       = $vcdt['name'];	//通貨名
			
			//金額,通貨
			$sum_in  .= '<p>'.$c0.'<span class="spc10_l">'.$c.'</span></p>';	//単体通常
			$total_in.= '<p>'.$c1.'<span class="spc10_l">'.$c.'</span></p>';	//個数通常
		}
		
		//▼商品個数
		$sel_num   = '<input type="hidden" name="num['.$k.']" value="'.$vdt['num'].'">'.$vdt['num'];
		$operation = '-';
		
		$list_in.= '<tr>';
		$list_in.= '<th>'.$plan_id.'</td>';
		$list_in.= '<td>'.$vdt['name'].'</td>';
		$list_in.= '<td>'.$sum_in.'</td>';
		$list_in.= '<td>'.$sel_num.'</td>';
		$list_in.= '<td>'.$total_in.'</td>';
		$list_in.= '</tr>';
	}
	
	
	//----- 選択コース -----//
	$input_pay = '<div class="spc50 form-check" id="psRC">';
	$input_pay.= '<h4>申込みコース</h4>';
	$input_pay.= '<div class="p_area">';
	$input_pay.= zSelectCart($DeliverArray,$p_deliver,'deliver','','class="form-control" id="rcDlver" required');
	$input_pay.= '</div>';
	$input_pay.= '<button type="button" class="spc10 btn" id="nxtPs2">支払方法を選ぶ</button>';
	$input_pay.= '</div>';
	
	
	//----- 支払通貨 -----//
	require('../util/inc_cart_f1_currency.php');
	
	
	//▼画面表示
	$input_pay.= '<div class="spc50 form-check '.$issHide2.'" id="ps2">';
	$input_pay.= '<h4>支払通貨</h4>';
	$input_pay.= '<div class="p_area">';
	$input_pay.= $p_cur;
	$input_pay.= '</div>';
	$input_pay.= $btn_ps3;
	$input_pay.= '</div>';
	
	
	//----- 支払方法 -----//
	require('../util/inc_cart_f1_payment.php');
	
	//▼支払方法入力
	$input_pay.= '<div class="spc50 form-group" id="ps3">';
	$input_pay.= '<h4>支払方法</h4>';
	$input_pay.= '<div class="p_area">';
	$input_pay.= $in_way;
	$input_pay.= '</div>';
	$input_pay.= '<button type="button" class="spc10 btn" id="nxtPs4" disabled="disabled">配送先を選ぶ</button>';
	$input_pay.= '</div>';
	
	
	$in_rate  = '<div id="pRate">'.$prate.'</div>';
	$in_total = '<div id="pTotal" class="num_in"></div>';
	$in_pfee  = '<div id="pFee"></div>';
	
	//▼入金予定日は先に入力しておく
	$in_limit = '<input type="hidden" id="dLimit" value="'.date('Y-m-d').'" name="order_limit">';
	
	
	//----- 配送先 -----//
	require('../util/inc_cart_f1_shipping.php');
	
	
	$input_pay.= '<div class="spc50 form-group" id="ps4">';
	$input_pay.= '<h4>配送先</h4>';
	$input_pay.= '<div class="p_area">';
	$input_pay.= $ssip_in;
	$input_pay.= '</div>';
	$input_pay.= '<button type="button" class="spc10 btn" id="nxtPs5" disabled="disabled">合計を確認する</button>';
	$input_pay.= '</div>';
	
	
	//----- 合計金額 -----//
	//▼合計非表示
	$rate_vi   = ($issHide2)? 'class="'.$issHide2.'"':'';
	
	//▼合計表示
	$input_pay.= '<div class="spc50 form-group" id="ps5">';
	$input_pay.= '<h4>毎月の支払金額</h4>';
	$input_pay.= '<div class="p_area">';
	$input_pay.= '<table class="paytable">';
	$input_pay.= '<tr '.$rate_vi.'><th>レート</th><td>'.$in_rate.'</td></tr>';
	$input_pay.= '<tr><th>手数料</th><td>'.$in_pfee.'</td></tr>';
	$input_pay.= '<tr><th>支払合計</th><td>'.$in_total.'</td></tr>';
	//$input_pay.= '<tr><th>入金予定日</th><td>'..'</td></tr>';
	$input_pay.= '</table>';
	$input_pay.= '</div>';
	$input_pay.= $in_limit;
	$input_pay.= '<input type="hidden" name="odr_fee"     value="" id="odrFee">';			//手数料
	$input_pay.= '<input type="hidden" name="odr_payment" value="" id="odrPayment">';		//支払金額合計
	//$input_pay.= '<button type="button" class="spc10 btn" id="nxtPs5" disabled="disabled">配送先を選ぶ</button>';
	$input_pay.= '</div>';
	
	
	//▼自動登録
	$input_auto_in = '<input type="hidden" name="act_send" value="send">';
	
	//▼登録ボタン
	$order_submit = '<input type="button" class="btn"  value="内容確認" disabled="disabled" id="Act">';
	$order_submit.= '<a class="btn btn_cancel spc10_l" href="">やり直す</a>';
	
	
	//▼javascript引継ぎ用
	$jsonSum      = json_encode($sum_ar);			//注文合計
	$jsonRate     = json_encode($rate_ar);			//通貨レート
	$jsonPayment  = json_encode($pay_way_ar);		//支払方法
	$jsonCurrency = json_encode($cur_ar);			//通貨
	$jsonCharge   = json_encode($charge_ar);		//注文編集用
	$jsonCsingle  = json_encode($paysel_single);	//支払通貨単体対応
	
}else{
	
	if($cart_ar){
		
		//▼個数選択画面読込
		require('../util/inc_cart_f0_start.php');
		
		
		//▼登録ボタン
		$order_submit = '<input type="submit" class="btn"   value="注文画面に進む">';
		$order_submit.= '<a class="btn btn_cancel spc10_l"  href="'.$link_to.'">商品選択に戻る</a>';
		
	}else{
		
		//▼カートの中身がない
		$no_cart      = '<p class="alert">現在カートの中身は空です</p>';
		$order_submit = '<a class="btn btn_cancel"  href="'.$link_to.'">商品選択に戻る</a>';
	}
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

}else{

	if($err_pos){
		
		//▼ポジションエラー
		$order_form = '<p class="alert">ログイン情報が正しくありません</p>';
		$order_form.= '<p class="alert">一度ログアウトし再度ログインしてください</p>';
		
	}else if(!$cart_ar){
		
		//▼カートの中身がない
		$order_form = $no_cart;
		$order_form.= '<p style="text-align:center;">'.$order_submit.'</p>';
		$order_form.= '<div>';
		
		$order_form.= '</div>';
		
	}else{
		
		//----- 登録フォーム -----//
		//▼自動登録項目
		$input_auto = '<input type="hidden" name="act"    value="process">';
		$input_auto.= '<input type="hidden" name="sort"   value="c">';
		$input_auto.= '<input type="hidden" name="ticket" value="'.$ticket.'">';
		$input_auto.= $input_auto_in;
		
		//----- 選択一覧 -----//
		//▼表示リスト
		$list_head = '<th>商品ID</th>';
		$list_head.= '<th>商品名</th>';
		$list_head.= '<th>商品価格(税抜)</th>';
		$list_head.= '<th>注文個数</th>';
		$list_head.= ($_POST['act'])? '<th>商品合計(税抜)</th>':'<th>操作</th>';

		$input_list = '<div class="form-group">';
		$input_list.= '<table class="table">';
		$input_list.= '<thead>';
		$input_list.= '<tr>'.$list_head.'</tr>';
		$input_list.= '</thead>';
		$input_list.= '<tbody>';
		$input_list.= $list_in;
		$input_list.= '</tbody>';
		$input_list.= '</table>' ;
		$input_list.= '</div>';
		
		//▼最終表示
		$order_form = $input_auto;
		$order_form.= $input_list;
		$order_form.= $input_pay;
		$order_form.= '<div class="spc50">';
		$order_form.= '<p style="text-align:center;">'.$order_submit.'</p>';
		$order_form.= '</div>';
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
		var opmonth = ["1","2","3","4","5","6","7","8","9","10","11","12"];
		var opday   = ["日","月","火","水","木","金","土"];
		var dopt ={
			dateFormat :'yy-mm-dd',
			changeMonth:true,
			minDate:new Date(),
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
		.table tbody td{height:52px;}

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
		
		#ps2,#ps3,#ps4,#ps5{display:none;}
		
		#ps2.isShow,
		#ps3.isShow,
		#ps4.isShow,
		#ps5.isShow{display:block;}
		
		.isHide{display:none;}
		
		.cl2{background:#F4F4F4; border-bottom:1px solid #E4E4E4;border-top:1px solid #E4E4E4; }
		.btn_cancel{background:#CFCFCF; color:#666;}
		.addr0 {margin:5px 10px;}
		.addr0 p{padding:0;margin:0;line-height:130%;}
		.adZip{float:left;}
		.add_in{display:none; margin-top:20px; max-width:600px;}
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

					<h2>Regular cart</h2>
					<div>
						<form name="odr_form" action="<?php echo $form_action_to.$cont_set;?>" method="POST" id="OdrForm">
							<?php echo $order_form;?>
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
<script src="../js/MyHelper.js" charset="UTF-8"></script>
<?php require('../util/inc_order_scrpt.php');?>
<?php require('../util/inc_order_control.php');?>
<script>
var Cat4 = new jSendPostDataAj('xml_order_cart_del.php');
function lzCartDel(A){
	var dData = {
		top   : 'cartdel',
		cartid: A
	};
	
	var Obc = Cat4.sendPost(dData);
	Obc.done(function(response){
		res = response.trim();
		if(res == 'ok'){location.reload();}else{alert('有効なデータがありません');}
	})
	.fail(function(){alert('削除できません');});
}

$('.ctDel').on('click',function(){
	dd = $(this).attr('id-data');
	lzCartDel(dd);
});
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
