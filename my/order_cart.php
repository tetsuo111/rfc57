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
$link_to        = 'order.php';



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
		
		//item point取得用
		$for_get_plan2.= (($for_get_plan2)? ",'":"'").$v['plan_id']."'";
	}
	
	//----- 支払手数料集計 -----//
	//▼手数料集計
	//手数料をorgとbaseに再計算
	require('../util/inc_cart_prc_fee.php');
	
	
	//▼注文合計に追加　＞　基準通貨で計算
	$total_notax  = $total_amt;				//税抜き金額
	$total_amt   += $fee['sum']['base'];	//税込み金額
	
	//----- 注文登録 -----//
	//▼注文情報登録

	$grank_id = 0; //なんか空文字怒られるから
	$order_ar = array(
		'position_id'                 => $position_id,
		'user_id'                     => $user_id,
		'rank_id'                     => $grank_id,
		'user_order_sort'             => $o_sort,
		'user_order_date_limit'       => $p_limit,
		'user_order_date_application' => 'now()',
		'user_order_amount'           => $total_amt,
		'user_order_resource'         => $pl_resource,
		'date_create'                 => 'now()',
		'state'                       => '1'
	);
	
	
	//新規登録
	$order_id = zDBNewUniqueID(TABLE_USER_ORDER,$order_ar,'user_order_ai_id','user_order_id');
	
	
	//----- 請求登録 -----//
	//▼通貨別請求情報　＞　そのまま登録
	foreach($odr_charge_ar AS $curid => $pay){
		
		//▼初期化
		$pay_ar = '';
		
		//▼データ格納
		$pay_ar = array(
			'user_order_id'          => $order_id,
			'position_id'            => $position_id,
			'user_id'                => $user_id,
			'currency_id'            => $pay['curid'],
			'payment_id'             => $pay['payid'],
			'user_o_charge_c_amount' => $pay['amt'],
			'user_o_charge_c_date'   => 'now()',
			'date_create'            => 'now()',
			'state'                  => '1'
		);
		
		//▼データ登録
		$tch_id_ar[$pay['payid']] = tep_db_perform(TABLE_USER_O_CHARGE,$pay_ar);
		
		
		//▼CIS対応　＞　通貨は一つなので支払もひとつ
		if(!$ci_payid){
			$ci_payid = $pay['payid'];
		}
	}
	
	
	//----- 詳細登録 -----//
	//全履歴　　　cart_ar			＞ js_plan
	//商品手数料　o_detail['culc']	＞ js_culc
	//品目　　　　odr_fee_ar　　　 	＞ js_fee
	//ポイント　　odr_fee_ar　　　 	＞ js_fee
	//合計金額　　sum_ar			＞ js_sum
	//支払手数料　odr_fee_ar　　　 	＞ js_fee
	
	//▼品目取得
	$query =  tep_db_query("
		SELECT
			`pi`.`m_plan_id`          AS `planid`,
			`pi`.`m_item_id`          AS `itemid`,
			`pi`.`m_plan_item_num`    AS `itemnum`,
			`ii`.`m_item_name`        AS `name`,
			`ii`.`m_item_fixamount`   AS `famount`,
			`ii`.`m_item_currency_id` AS `curid`,
			`ii`.`m_item_resource`    AS `resource`
		FROM      `".TABLE_M_ITEM."` `ii` 
		LEFT JOIN `".TABLE_M_PLAN_ITEM."` `pi` ON `pi`.`m_item_id` = `ii`.`m_item_id`
		WHERE `ii`.`state` = '1'
		AND   `pi`.`state` = '1'
		AND   `pi`.`m_plan_id` IN (".$for_get_plan2.")
		ORDER BY `pi`.`m_plan_id`
	");
	
	while($it = tep_db_fetch_array($query)) {
		//詳細登録用
		$o_detail['item'][$it['planid']][$it['itemid']] = $it;
		$o_detail['item'][$it['planid']][$it['itemid']]['cur'] = $cur_ar[$it['curid']];
	}
	
	//▼ポイント取得
	$query =  tep_db_query("
		SELECT
			`pp`.`m_plan_id`           AS `planid`,
			`pp`.`m_point_id`          AS `pointid`,
			`mp`.`m_point_name`        AS `name`,
			`pp`.`m_plan_point_amount` AS `amt`
		FROM `".TABLE_M_PLAN_POINT."` `pp`
		LEFT JOIN `".TABLE_M_POINT."` `mp`  ON `mp`.`m_point_id` = `pp`.`m_point_id`
		WHERE `mp`.`state` = '1'
		AND   `pp`.`state` = '1'
		AND   `pp`.`m_plan_id` IN (".$for_get_plan2.")
		ORDER BY `pp`.`m_plan_id`
	");
	
	while($pt = tep_db_fetch_array($query)) {
		//詳細登録用
		$o_detail['point'][$pt['planid']][$pt['pointid']] = $pt;
	}
	
	
	//▼詳細履歴を保存
	$detail_ar = array(
		'user_order_id'           => $order_id,
		'position_id'             => $position_id,
		'user_id'                 => $user_id,
		'user_o_detail_js_plan'   => json_encode($cart_ar),
		'user_o_detail_js_item'   => json_encode($o_detail['item']),
		'user_o_detail_js_point'  => json_encode($o_detail['point']),
		'user_o_detail_js_culc'   => json_encode($o_detail['culc']),
		'user_o_detail_js_sum'    => json_encode($sum_ar),
		'user_o_detail_js_fee'    => json_encode($odr_fee_ar),
		'date_create'             => 'now()',
		'state'                   => '1'
	);
	
	//新規登録
	tep_db_perform(TABLE_USER_O_DETAIL,$detail_ar);
	
	
	//▼カート登録完了
	foreach($cart_ar AS $k => $v){
		
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
	}
	
	
	//================================
	//CIS対応
	//================================
	//▼支払識別コード取得
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
		
		//▼請求番号
		if($pcode == '5'){
			$ch_id = $tch_id_ar[$ci_payid];
		}
	}
	
	//▼注文種類a：初回　b：追加　c：リピート
	$otype_ar    = array('a'=>1,'c'=>2,'b'=>3);
	$adrtye_ar   = array('a'=>1,'b'=>2);
	
	//注文種類 初回1　リピート2　追加3
	if($o_sort){
		$ci_otype = $otype_ar[$o_sort];
	}else{
		$ci_otype = 'b';
	}
	
	//▼登録データ
	$ci_oid       = $order_id;
	$ci_ftype     = 5;										//申し込み方法
	$ci_stype     = 1;										//受け取り方法 通常1　持ち帰りに備えて2も残しておく
	$ci_adrtype   = $adrtye_ar[$p_ssip];					//届け先 1登録住所 2その他送付先
	$ci_paytype   = ($pcode)? $pcode:'1';					//支払方法　＞何もなければ銀行振込
	$ci_sumintax  = ($total_notax + $fee['tax']['base']);	//商品代金合計（税込）
	$ci_sumouttax = $total_notax;							//商品代金合計（税抜き）
	$ci_sumtax    = $fee['tax']['base'];					//消費税合計
	$ci_sendfee   = $fee['syu']['base'];					//出荷事務手数料
	$ci_daibiki   = $fee['daib']['base'];					//代引手数料
	$ci_sumprice  = $total_amt;								//合計金額　商品合計+支払手数料
	$ci_sumpoint  = $pl_resource;							//合計原資
	$recmoney     = ($pcode == 3)? $total_amt:0;			//recmoney カード決済のみ初動でセット、ｶｰﾄﾞ以外は0
	
	//▼配送先の登録
	if($_POST['noship']){
		//初期注文の時にはすでに取得済
		
	}else{
		
		//▼登録済み住所を取得
		$query =  tep_db_query("
			SELECT
				`otheradrpost`,
				`otheradr1`,
				`otheradr2`,
				`otheradr3`,
				`otheradr4`,
				`otherphone`,
				`othername1`
			FROM      `".TABLE_MEM00001."`
			WHERE `memberid` = '".tep_db_input($user_id)."'
		");

		$f = tep_db_fetch_array($query);
		
		$ss_name  = $f['othername1'];
		$ss_zip   = $f['otheradrpost'];
		$ss_pref  = $f['otheradr1'];
		$ss_city  = $f['otheradr2'];
		$ss_area  = $f['otheradr3'];
		$ss_strt  = $f['otheradr4'];
		$ss_phone = $f['otherphone'];
	}
	
	
	//▼odr00000
	$odr00_ar = array(
		'orderid'        => $ci_oid,
		'inputdate'      => 'now()',
		'editflag'       => 0,
		'memberid'       => $user_id,
		'orderdate'      => 'now()',
		'ordertype'      => $ci_otype,
		'formtype'       => $ci_ftype,
		'sendtype'       => $ci_stype,
		'adrtype'        => $ci_adrtype,
		'paytype'        => $ci_paytype,
		'adrname'        => $ss_name,
		'adrpost'        => $ss_zip,
		'adr1'           => $ss_pref,
		'adr2'           => $ss_city,
		'adr3'           => $ss_area,
		'adr4'           => $ss_strt,
		'phone'          => $ss_phone,
		'sumitem_intax'  => $ci_sumintax,
		'sumitem_outtax' => $ci_sumouttax,
		'sumtax'         => $ci_sumtax,
		'senddate1'      => 'now()',
		'sendfee'        => $ci_sendfee==''?0:$ci_sendfee,
		'daibikifee'     => $ci_daibiki,
		'sumprice'       => $ci_sumprice,
		'sumpoint'       => $ci_sumpoint,
		'recmoney'       => $recmoney,
		'senddate1'      => 'now()',
	);
	
	tep_db_perform(TABLE_ODR00000,$odr00_ar);
	
	
	//----- 注文詳細 -----//
	//▼カート単位で処理
	$count = 1;
	foreach($cart_ar AS $k => $v){
		
		$ci_detail = $ci_oid.'-'.$count;
		
		//▼odr00001
		$odr01_ar = array(
			'orderid'      => $ci_oid,
			'detailid'     => $ci_detail,
			'itemid'       => $v['plan_id'],
			'itemname'     => $v['name'],
			'qty'          => $v['num'],
			'price_intax'  => ($v['base_a'] + $v['tax_a']),
			'price_outtax' => $v['base_a'],
			'tax'          => $v['tax_b'],
			'sumprice'     => ($v['base_b'] + $v['tax_b']),
			'point'        => $v['resource'],
			'sumpoint'     => $v['sumresource']
		);
		
		tep_db_perform(TABLE_ODR00001,$odr01_ar);
		$count++;
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
	
	
	//▼終了処理
	if($pcode == '5'){
		
		//▼カード決済画面
		tep_cookie_set("odrid",$order_id);		//注文番号
		tep_cookie_set("chid" ,$ch_id);			//請求番号
		tep_redirect('pay_card.php', '', 'SSL');
		
	}else{
		
		//通常終了
		$end = 'end';
	}
	
}else if($_POST['act'] == 'process'){
	
	//▼初期設定
	$sum_in   = '';		//単体通常
	$total_in = '>';	//個数通常
	
	
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
	
	
	//----- 支払通貨 -----//
	//基準通貨しかなければ処理を飛ばす
	require('../util/inc_cart_f1_currency.php');
	
	
	//▼画面表示
	$input_pay = '<div class="spc50 form-check '.$issHide2.'" id="ps2">';
	$input_pay.= '<h4>支払通貨</h4>';
	$input_pay.= '<div class="p_area">';
	$input_pay.= $p_cur;
	$input_pay.= '</div>';
	$input_pay.= $btn_ps3;
	$input_pay.= '</div>';
	
	
	//----- 支払方法 -----//
	require('../util/inc_cart_f1_payment.php');
	
	//▼支払方法入力
	$input_pay.= '<div class="spc50 form-group '.$isshow3.'" id="ps3">';
	$input_pay.= '<h4>支払方法</h4>';
	$input_pay.= '<div class="p_area">';
	$input_pay.= $in_way;
	$input_pay.= '</div>';
	$input_pay.= '<button type="button" class="spc10 btn btn-default" id="nxtPs4" disabled="disabled">配送先を選ぶ</button>';
	$input_pay.= '</div>';
	
	
	$in_way   = '<div id="pWay"></div>';
	$in_rate  = '<div id="pRate">'.$prate.'</div>';
	$in_total = '<div id="pTotal" class="num_in"></div>';
	$in_pfee  = '<div id="pFee"></div>';
	
	$limit_v = ($order_limit)? $order_limit:date('Y-m-d');
	$in_limit = '<input type="text" id="dLimit" value="'.$limit_v.'" name="order_limit" class="form-control" style="background:#FFF;" size=6 required readonly>';
	
	
	//----- 配送先 -----//
	require('../util/inc_cart_f1_shipping.php');
	
	
	$input_pay.= '<div class="spc50 form-group" id="ps4">';
	$input_pay.= '<h4>配送先</h4>';
	$input_pay.= '<div class="p_area">';
	$input_pay.= $ssip_in;
	$input_pay.= '</div>';
	$input_pay.= '<button type="button" class="spc10 btn btn-default" id="nxtPs5" disabled="disabled">合計を確認する</button>';
	$input_pay.= '</div>';
	
	
	//----- 合計金額 -----//
	//▼合計表示
	$input_pay.= '<div class="spc50 form-group" id="ps5">';
	$input_pay.= '<h4>支払金額</h4>';
	
	//▼合計非表示
	$rate_vi   = ($issHide2)? 'class="'.$issHide2.'"':'';
	
	$input_pay.= '<div class="p_area">';
	$input_pay.= '<table class="paytable">';
	$input_pay.= '<tr '.$rate_vi.'><th>レート</th><td>'.$in_rate.'</td></tr>';
	$input_pay.= '<tr><th>手数料</th><td>'.$in_pfee.'</td></tr>';
	$input_pay.= '<tr><th>支払合計</th><td>'.$in_total.'</td></tr>';
	$input_pay.= '<tr id="sdLimit"><th>入金予定日</th><td style="text-align:right;">'.$in_limit.'</td></tr>';
	$input_pay.= '</table>';
	$input_pay.= '</div>';
	
	$input_pay.= '<input type="hidden" name="odr_fee"     value="" id="odrFee">';			//手数料
	$input_pay.= '<input type="hidden" name="odr_payment" value="" id="odrPayment">';		//支払金額合計
	$input_pay.= '</div>';
	
	
	//▼自動登録
	$input_auto_in = '<input type="hidden" name="act_send" value="send">';
	
	//▼登録ボタン
	$order_submit = '<input type="button" class="btn btn-default"  value="内容確認" disabled="disabled" id="Act">';
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
		
		//▼個数選択
		require('../util/inc_cart_f0_start.php');
		
		//▼登録ボタン
		$order_submit = '<input type="submit" class="btn btn-default"   value="注文画面に進む">';
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
		$input_auto = '<input type="hidden" name="act"     value="process">';
		$input_auto.= '<input type="hidden" name="ticket"  value="'.$ticket.'">';
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
		
		#ps3,#ps4,#ps5{display:none;}
		
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

					<h2>Cart</h2>
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
