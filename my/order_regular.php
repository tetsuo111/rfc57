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
$link_to        = 'order_regular_cart.php';
$link_address   = 'edit_user_address.php';
$link_ship      = 'edit_user_ship.php';
$link_change    = 'pay_change.php';

$m_plan_id = $_GET['m_plan_id'];
$cont_set  = '?m_plan_id='.$m_plan_id;


//----- 商品一覧 -----//
//▼アクティブ確認
if($user_id){
	
	//▼住所確認
	$query_a =  tep_db_query("
		SELECT
			`memberid`
		FROM  `".TABLE_MEM00001."`
		WHERE `memberid` = '".tep_db_input($user_id)."'
	");

	if(!tep_db_num_rows($query_a)){$err_addr = true;}
	
}else{
	$err_pos = true;
}


//-------- データ更新登録 --------//
if($_POST['act'] == 'regular'){
	
	//----- データ取得 -----//
	$rg_state = $_POST['rg_state'];			//定期購入自体　a：しない　b：する
	$deliver  = $_POST['deliverytype'];		//配送間隔　1：毎月 2：2ヵ月 3：3ヵ月
	$memtype  = $_POST['membertype'];		//支払方法　5クレジットカード
	$num      = $_POST['num'];				//選択個数
	$sort     = $_POST['sort'];				//注文種類
	$bcty     = $_POST['bcty'];				//会員区分
	$fsyu     = $_POST['fsyu'];				//出荷事務手数料　事務処理手数料
	$fdaib    = $_POST['fdaib'];			//代引き手数料　送料
	
	//----- エラーチェック -----//
	$err = false;
	
	if(!$rg_state){
		$err = true;
		$err_text = '<p class="alert">定期購入申込みを選択してください</p>';
		
	}else if($rg_state == 'b'){
		
		//▼定期購入を申し込む場合
		if(!$deliver){$err = true; $err_text.= '<p class="alert">申込みコースを選択してください</p>';}
		if(!$memtype){$err = true; $err_text.= '<p class="alert">支払方法を選択してください</p>';}
		if(!$num)    {$err = true; $err_text.= '<p class="alert">定期購入商品を選択してください</p>';}
	}
	
	
	//----- データ更新 -----//
	if($err == false){
		
		if($rg_state == 'a'){
			
			//--- 定期購入しない ---//
			$regular_ar = array(
				'editdate'   => 'now()',		//変更日
				'as_stopflg' => '1'				//終了フラグ
			);
			
			//▼上書き
			$w_set = "`memberid`='".tep_db_input($user_id)."'";
			tep_db_perform(TABLE_MEM00000,$regular_ar,'update',$w_set);
			
			
		}else if($rg_state == 'b'){
			
			//▼既存の状態を保持
			$query = tep_db_query("
				SELECT
					`asitem001`    AS `it1`,
					`asitem002`    AS `it2`,
					`asitem003`    AS `it3`,
					`asitemqty001` AS `qt1`,
					`asitemqty002` AS `qt2`,
					`asitemqty003` AS `qt3`
				FROM  `".TABLE_MEM00000."`
				WHERE `memberid`   = '".tep_db_input($user_id)."'
			");
			
			$om0 = tep_db_fetch_array($query);
			
			
			//--- mem00000設定 ---//
			//▼登録データ
			foreach($num AS $k => $v){
				if($v){
					$as_ar[]    = array('itemid'=>$k,'qty'=>$v);
					$new_ar[$k] = $v;	//新規登録用 0は除外
				}
			}
			
			$i0 = ($as_ar[0]['qty'])? $as_ar[0]['itemid']:'null';
			$i1 = ($as_ar[1]['qty'])? $as_ar[1]['itemid']:'null';
			$i2 = ($as_ar[2]['qty'])? $as_ar[2]['itemid']:'null';
			
			$q0 = ($as_ar[0]['qty'])? $as_ar[0]['qty']:0;
			$q1 = ($as_ar[1]['qty'])? $as_ar[1]['qty']:0;
			$q2 = ($as_ar[2]['qty'])? $as_ar[2]['qty']:0;
			
			$regular_ar = array(
				'editdate'       => 'now()',		//変更日
				'as_stopflg'     => 'null',			//中止フラグを消す
				'membertype'     => $memtype,		//支払方法
				'deliverytype'   => $deliver,		//申込みコース
				'as_fee'         => $fsyu,			//出荷事務手数料
				'as_daibiki_fee' => $fdaib,			//代引き手数料
				'asitem001'      => $i0,			//商品コード1
				'asitem002'      => $i1,			//商品コード2
				'asitem003'      => $i2,			//商品コード3
				'asitemqty001'   => $q0,			//商品数量1
				'asitemqty002'   => $q1,			//商品数量2
				'asitemqty003'   => $q2				//商品数量3
			);
			
			//▼上書き
			$w_set = "`memberid`='".tep_db_input($user_id)."'";
			tep_db_perform(TABLE_MEM00000,$regular_ar,'update',$w_set);
			
			
			//--- AS00000設定 ---//
			//▼初期設定
			$db_table_as = TABLE_ASITEM00000;		//DB情報
			
			
			//▼AS既存確認
			if($om0){
				
				//▼選択商品リスト
				for($i=1;$i<4;$i++){
					
					$itemid = $om0['it'.$i];
					
					//▼登録分岐
					if($num[$itemid]){
						
						//更新対象　＞既存 + 数量あり
						$up_ar[$itemid] = $num[$itemid];
						
						//新規用
						$for_as_id.= (($for_as_id)? ",'":"'").$itemid."'";
						unset($new_ar[$itemid]);
						
					}else if($itemid && !$num[$itemid]){
						
						//削除対象　＞既存 + 数量なし
						$del_ar[$itemid] = $itemid;
						
						//新規用
						$for_as_id.= (($for_as_id)? ",'":"'").$itemid."'";
						unset($new_ar[$itemid]);
					}
				}
			}
			
			//▼削除対象
			$query =  tep_db_query("
				SELECT
					`asid`,
					`itemid`
				FROM  `".$db_table_as."`
				WHERE `memberid` = '".tep_db_input($user_id)."'
				AND   `itemid` IN (".$for_as_id.")
				AND   `flg`    = '1'
			");
			
			while($b = tep_db_fetch_array($query)){
				
				$itm = $b['itemid'];
				
				//▼データ登録判断
				if($del_ar[$itm]){
					//削除
					$as_up_ar = array('flg' => '0');
					
				}else{
					$as_up_ar = array('qty' => $up_ar[$itm]);
				}
				
				//▼検索設定
				$w_set_as = "`memberid`='".tep_db_input($user_id)."' AND `itemid`='".tep_db_input($itm)."' AND `flg`='1'";
				
				//▼更新実行
				tep_db_perform($db_table_as,$as_up_ar,'update',$w_set_as);
			}
			
			
			//▼AS申込み新規
			foreach($new_ar AS $k => $v){
				
				//▼登録データ
				$as01_ar = array(
					'memberid'       => $user_id,
					'itemid'         => $k,
					'qty'            => $v,
					'input_datetime' => 'now()',
					'flg'            => '1'
				);
				
				tep_db_perform($db_table_as,$as01_ar);
			}
		}
		
		//▼終了処理
		$end = 'end';
	}
}

//-------- 顧客伝達事項 -------//
//▼ユーザー情報伝達
require ('inc_user_announce.php');


//----- データ表示 ----//
if($err_pos){
	
	//▼ポジションエラー
	$order_form = '<p class="alert">ログイン情報が正しくありません</br>';
	$order_form.= '一度ログアウトし再度ログインしてください</p>';
	
}else if($err_addr){
	
	//▼アドレス
	$order_form = '<p class="alert">住所情報が登録されていません</br>';
	$order_form.= '注文の前に住所を登録してください</p>';
	$order_form.= '<a href="'.$link_address.'">住所情報を登録する</a>';
	
}else{
	
	if($end == 'end'){
		
		//▼登録終了
		$order_form = '<p>定期購入の設定を変更しました</p>';
		$order_form.= '<a href="">定期購入の設定を確認する</a>';
		
	}else{
		
		//----- 定期購入を確認 -----//
		$query = tep_db_query("
			SELECT
				`bctype`,					-- 会員区分
				`membertype`,				-- 支払方法
				`asregdate`,				-- 登録日
				`as_stopflg`,				-- 停止
				`deliverytype`,				-- 発送期間
				`as_fee`,					-- 送料
				`as_daibiki_fee`,			-- 代引き手数料
				`as_adjust`,				-- 調整金
				`as_discount`,				-- 割引料
				`asitem001`    AS `it1`,	-- 商品1
				`asitem002`    AS `it2`,	-- 商品2
				`asitem003`    AS `it3`,	-- 商品3
				`asitemqty001` AS `qt1`,	-- 商品1数量
				`asitemqty002` AS `qt2`,	-- 商品2数量
				`asitemqty003` AS `qt3`,	-- 商品3数量
				`login_id`,					-- クレジットid
				`memo1`,					-- クレジットpass
				DATE_FORMAT(`asregdate`,'%Y-%m-%d') AS `d_next`
			FROM  `".TABLE_MEM00000."`
			WHERE `memberid`   = '".tep_db_input($user_id)."'
			AND   `membertype` IS NOT NULL
		");
		
		if($a = tep_db_fetch_array($query)){
			
			//--- 定期購入あり ---//
			//▼リスト取得
			$rg_state_ar   = array('a'=> 'しない','b'=>'する');
			$pay_ar        = zPaymentList('code');						//支払方法
			$pay_codeid    = zPaymentList('codeid');					//支払方法　制限計算用
			$point_ar      = zPointList();								//ポイント
			$rank_bcodr_ar = zRankList('bcorder');						//ランク一覧　会員区分＞順番
			$rank_bcid_ar  = zRankList('bcid');							//ランク一覧　会員区分＞ID
			
			$culc_ar       = zCulcList();								//手数料計算
			$base_cur      = zGetSysSetting('sys_base_currency_unit');	//基準通貨
			
			//▼登録情報
			$f_stop  = $a['as_stopflg'];		//申込み状況
			$d_next  = $a['d_next'];			//次回購入日
			$f_jimu  = $a['as_fee'];			//事務手数料
			$f_daibi = $a['as_daibiki_fee'];	//代引手数料
			$bctype  = $a['bctype'];			//会員区分
			
			$on_regu = true;					//定期購入設定フラグ
			
			//▼選択商品リスト
			for($i=1;$i<4;$i++){
				if($a['it'.$i]){
					$c_plan_ar[$a['it'.$i]] = $a['qt'.$i];
				}
			}
			
			//▼カード変更
			if($a['membertype'] == '5'){
				
				//▼カード送信情報
				$site_id_m   = zGetSysSetting('sys_cm_site_id');		//月毎決済ID
				$site_pass_m = zGetSysSetting('sys_cm_site_pass');		//月毎決済Pass
				$c_id        = $a['login_id'];
				$c_pass      = $a['memo1'];
				$c_url       = 'https://payment.alij.ne.jp/service/continue/change';
				
				//▼カード変更リンク
				$change_card ='<p style="margin-bottom:0; margin-top:10px; text-align:right; font-size:11px;"><a href="'.$link_change.'">カード情報の変更はこちら</a></p>';
			}
			
			
			//----- 表示設定 -----//
			//▼表示クラス
			$form_a0 = '<div class="spc20 form-group">';
			$form_a1 = '</div>';
			
			$form_b0 = '<div class="p_area">';
			$form_b1 = '</div>';
			
			
			//▼フォーム要素
			$cl_sel_ed = 'class="form-control rgin"';	//入力表示
			$dis       = 'disabled';
			
			//▼注意事項
			$alm1 = '<p class="alert" style="margin:0; padding-top:10px;">※予定日が、休日の場合は翌営業日となります。</p>';
			
			
			//----- 内容構築 -----//
			//▼定期購入自体
			$rg_state = ($f_stop)? 'a':'b';
			
			$rg_state_in = '<h4>定期購入申込</h4>';
			$rg_state_in.= $form_b0;
			$rg_state_in.= zSelectListSet($rg_state_ar,$rg_state,'rg_state','','','','',$cl_sel_ed);
			$rg_state_in.= $form_b1;
			
			
			//▼コース選択
			$rg_term_in = '<h4>申込みコース</h4>';
			$rg_term_in.= $form_b0;
			$rg_term_in.= zSelectListSet($DeliverArray,$a['deliverytype'],'deliverytype','','','','',$cl_sel_ed);
			$rg_term_in.= $form_b1;
			
			
			//▼支払制限追加
			require('../util/inc_payment_restriction.php');
			
			$pay_ar_edit = $pay_ar;
			foreach($pay_ar AS $k => $v){
				//注文種類＞bctype＞支払方法＞支払通貨
				$restid = 'c_'.$bctype.'_'.$pay_codeid[$k].'_0';
				
				//制限対象の支払方法を削除
				if($PayRestrictArray[$restid]){
					unset($pay_ar_edit[$k]);
				}
			}
			
			
			//▼支払方法
			$rg_pay_in = '<h4>支払方法</h4>';
			$rg_pay_in.= $form_b0;
			$rg_pay_in.= zSelectListSet($pay_ar_edit,$a['membertype'],'membertype','','','','',$cl_sel_ed);
			$rg_pay_in.= $change_card;
			$rg_pay_in.= $form_b1;
			
			//▼配送先
			$ssip_noradio = true;	//選択なし
			require('../util/inc_cart_f1_shipping.php');
			
			//▼配送先が登録されている
			if($add[1]){
				
				$rg_addr_in = '<h4>配送先</h4>';
				$rg_addr_in.= $form_b0;
				$rg_addr_in.= $ssip_in;
				$rg_addr_in.= '<a href="'.$link_ship.'" class="spc10 fl_r fnt12">配送先の変更はこちら</a>';
				$rg_addr_in.= $form_b1;
			
			}else{
				$rg_addr_in = '<h4>配送先</h4>';
				$rg_addr_in.= $form_b0;
				$rg_addr_in.= $ssip_in;
				$rg_addr_in.= $form_b1;
			}
			
			
			//▼商品情報
			//zGetTypePlanData(会員区分,注文区分,選択済み商品);
			$m_plan_ar = zGetTypePlanData($bctype,'c',$c_plan_ar);
			
			
			//▼商品詳細
			foreach($m_plan_ar AS $kp => $vp){
				
				//▼ポイント
				$in_point = '';
				foreach($vp['point'] AS $kpo => $vpo){
					$in_point.= '<p>'.$vpo['name'].' '.$vpo['amt'].'</p>';
					
					$tmp_p[$kpo]['name'] = $vpo['name'];	//ポイント名
					$tmp_p[$kpo]['amt'] += $vpo['total'];	//ポイント集計
				}
				
				//▼注文個数
				$oder_num_ar = ($vp['ol_piece'])? range(0,$vp['ol_piece']):range(0,15);
				unset($oder_num_ar[0]);		//不要な個数を削除
				
				//▼表示内容
				$rg_plan.= '<tr>';
				$rg_plan.= '<td>'.$kp.'</td>';
				$rg_plan.= '<td>'.$vp['name'].'</td>';
				$rg_plan.= '<td>'.$vp['sum'].' '.$base_cur.'</td>';
				$rg_plan.= '<td>'.$in_point.'</td>';
				$rg_plan.= '<td>';
				$rg_plan.= zSelectListSet($oder_num_ar,$vp['num'],'num['.$kp.']','▼選択','','','',$cl_sel_ed);
				$rg_plan.= '</td>';
				$rg_plan.= '</tr>';
				
				//▼合計金額　＞　単価*個数
				$sum_total+= $vp['total'];
			}
			
			//▼最終合計金額
			$sum_all = $sum_total + $f_jimu + $f_daibi;
			
			foreach($tmp_p AS $k => $v){
				$po_total.= '<p class="po_ttl">'.$v['name'].'<span class="spc10_l">'.$v['amt'].'</span></p>';
			}
			
			//▼合計表示
			$pin = '<span class="fl_r">：</span>';
			
			$pln_total = '<tr><th>合計ポイント'.$pin.'</th><td><span id="rgtPoint">'.$po_total.'</span></td></tr>';
			$pln_total.= '<tr><th>商品合計'.$pin.'</th><td><span id="rgtTotal">'.number_format($sum_total).'</span> '.$base_cur.'</td></tr>';
			$pln_total.= '<tr><th>事務処理手数料'.$pin.'</th><td><span id="rgtJimu">'.number_format($f_jimu).'</span> '.$base_cur.'</td></tr>';
			$pln_total.= '<tr><th>送料'.$pin.'</th><td><span id="rgtDaib">'.number_format($f_daibi).'</span> '.$base_cur.'</td></tr>';
			$pln_total.= '<tr><th>合計金額'.$pin.'</th><td><span id="rgtAll">'.number_format($sum_all).'</span> '.$base_cur.'</td></tr>';
			
			
			//▼選択商品
			$rg_plan_in = '<h4>定期購入商品</h4>';
			$rg_plan_in.= $form_b0;
			$rg_plan_in.= '<table class="notable">'.$rg_plan.'</table>';
			$rg_plan_in.= '<table class="notable2">'.$pln_total.'</table>';
			$rg_plan_in.= $form_b1;

			
			//▼次回購入予定
			$rg_next_in = '<h4>次回購入予定</h4>';
			$rg_next_in.= $form_b0;
			$rg_next_in.= $d_next.$alm1;
			$rg_next_in.= $form_b1;
			
			
			//----- 表示フォーム -----//
			$input_auto = '<input type="hidden" name="act"   value="regular">';
			$input_auto.= '<input type="hidden" name="top"   value="rgedit">';
			$input_auto.= '<input type="hidden" name="sort"  value="c">';
			$input_auto.= '<input type="hidden" name="bcty"  value="'.tep_db_input($bctype).'">';
			$input_auto.= '<input type="hidden" name="fsyu"  value="'.$f_jimu.'"  id="fSyu">';
			$input_auto.= '<input type="hidden" name="fdaib" value="'.$f_daibi.'" id="fDaib">';
			
			$order_form = '<div class="spc20">';
			$order_form.= '<form action="'.$form_action_to.'" method="POST" id="rgForm" class="form_max">';
			$order_form.= $input_auto;
			
			//▼申込み
			$order_form.= $form_a0;
			$order_form.= $rg_state_in;
			$order_form.= $form_a1;
			
			//▼期間
			$order_form.= $form_a0;
			$order_form.= $rg_term_in;
			$order_form.= $form_a1;
			
			//▼支払方法
			$order_form.= $form_a0;
			$order_form.= $rg_pay_in;
			$order_form.= $form_a1;
			
			//▼送付先
			$order_form.= $rg_addr_in;
			
			//▼購入商品
			$order_form.= $form_a0;
			$order_form.= $rg_plan_in;
			$order_form.= $form_a1;
			
			//▼次回予定
			$order_form.= $form_a0;
			$order_form.= $rg_next_in;
			$order_form.= $form_a1;
			
			$order_form.= '<div style="margin:50px 0; text-align:center;">';
			$order_form.= '<input type="button" class="btn" value="確認画面" id="rgCheck" '.$dis.'>';
			$order_form.= '<input type="submit" class="btn" value="この内容で登録する" id="rgAct" '.$dis.'>';
			$order_form.= '<a href=""><button type="button" class="btn btn_cancel spc10_l">やり直し</button></a>';
			$order_form.= '</div>';
			
			$order_form.= '</form>';
			$order_form.= '</div>';
			
		}else{
			
			//--- 定期購入なし ---//
			//▼定期購入設定
			$sort = 'c';

			//▼初回読込
			require('../util/inc_order_init.php');
			
			
			//▼商品リスト
			$odr_in1 = '<div class="form-group">';
			$odr_in1.= $input_list;
			$odr_in1.= '</div>';
			
			//▼商品詳細
			$odr_in2.= '<div class="spc50">';
			$odr_in2.= $err_text;
			$odr_in2.= '<h4>商品内容</h4>';
			$odr_in2.= $input_form;
			$odr_in2.= '</div>';
			
			$odr_in2.= '<div class="spc50 form-check">';
			$odr_in2.= '<h4>注文個数を選ぶ</h4>';
			$odr_in2.= $input_form1;
			$odr_in2.= '</div>';
			
			
			//▼表示設定
			if($m_plan_id){
				$order_form = $input_auto;
				$order_form.= $odr_in1;
				$order_form.= $odr_in2;
			}else{
				$order_form = $odr_in1;
			}
			
			
			//▼カート
			$show_cart = '<a href="'.$link_to.'" class="fl_r"><button type="button" class="btn">';
			$show_cart.= '<i class="fa fa-shopping-cart" aria-hidden="true" style="font-size:24px;"></i>';
			$show_cart.= '<span class="spc10_l" style="font-size:20px;">'.$ncart.'</span>';
			$show_cart.= '<span class="spc10_l">定期購入手続き</span>';
			$show_cart.= '</button></a>';
		}
		
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

	<style>
		.announce_table{margin:5px 0px 5px 0px; width:600px;}
		.announce_table tr{}
		.announce_table th{background:#DFDFDF;border:1px #FFFFFF solid; padding:2px 5px 2px 5px;}
		.announce_table td{background:#FFFFFF;border:1px #DFDFDF solid; padding:2px 5px 2px 5px;}
		
		.table thead th{background:#F4F4F4;}
		.table tbody tr{border-bottom:1px solid #E9E9E9;}
		.table.list_table tr{border:1px solid #E9E9E9;}
		.table.list_table tr .notable tr{ border:none;}
		
		.notable td{border:none; padding:2px 5px;}
		.notable td p{margin:0;padding:0;}
		
		.notable2 {margin:10px 0;}
		.notable2 tr{border-bottom:1px solid #E4E4E4;}
		.notable2 th{padding:5px;}
		.notable2 td{padding:5px; text-align:right;}
		
		.po_ttl{margin:0; font-size:12px;}
		
		.p_area{width:100%; border:1px solid #E4E4E4; padding:10px; overflow:hidden; border-radius:10px;}
		.list_table{max-width:600px; border:1px solid #E4E4E4;}
		
		.btn_cancel{background:#D4D4D4;}
		
		.addr0 {margin:5px 10px;}
		.addr0 p{padding:0;margin:0;line-height:130%;}
		.adZip{float:left;}
		.add_in{display:none; margin-top:20px; max-width:600px;}
		
		#rgAct{display:none;}
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

					<h2 style="overflow:hidden;">Regular order <?php echo $show_cart;?></h2>
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
<script src="../js/MyHelper.js" charset="UTF-8"></script>
<script>
	
	oN  = 0;
	$('#oNum').on('change',function(){
		oN  = $(this).val();
		dis = (oN)? false:true;
		$('#inCart').prop('disabled',dis);
	});

	var Cat = new jSendPostDataAj('xml_order_cart.php');
	$('#inCart').on('click',function(){
		var sData = {
				top   : 'cart',
				planid:'<?php echo $m_plan_id;?>',
				oNum  :oN,
				oSort :'<?php echo $sort;?>'
			};
		var Obj = Cat.sendPost(sData);
		
		Obj.done(function(response){
			
			res = response.trim();
			
			if(res == 'ok'){
				alert('カートに入れました');
				location.href='<?php echo $form_action_to;?>';
			}else{
				alert('データの設定に不備があります');
			}
		})
		.fail(function(){alert('データの確認ができません');});
	});
</script>
<script>
	$('.rgin').on('change',function(){
		aa = $('#rgCheck').prop('disabled');
		if(aa){
			$('#rgCheck').prop('disabled',false);
		}
	});
	
	$('#rgCheck').on('click',function(){
		
		var Cat = new jSendPostFormAj('xml_regular_culc.php','rgForm');
		var Obj = Cat.sendPost();
		
		Obj.done(function(response){
			
			res = JSON.parse(response.trim());
			
			if(res.state == 'ok'){
				alert('金額を再計算しました');
				
				ljTotalUpdate(res.adata);
				
				$('#rgCheck').fadeToggle(600,function(){
					$('#rgAct').prop('disabled',false);
					$('#rgAct').fadeToggle(600);
				});
			}else{
				alert('データの設定に不備があります');
			}
		})
		.fail(function(){alert('データの確認ができません');});
	});
	
	$('#rgAct').on('click',function(){
		if(confirm('この内容で登録しますか')){
			$('#rgForm').submit();
		}
	});
	
	function ljTotalUpdate(A){
		
		Pldt   = A.plan;
		tmpP   = {};
		sumTtl = 0;
		sumAll = 0;
		poTtl  = '';
		fsyu   = A.syu * 1;
		fdaib  = A.daib * 1;
		
		for(var kp in Pldt){
			
			var vp = Pldt[kp];
			
			for(var kpo in vp['point']){
				vpo = vp['point'][kpo];
				
				if(!tmpP[kpo]){tmpP[kpo] = {name:'',amt:0};}
				tmpP[kpo]['name'] = vpo['name'];
				tmpP[kpo]['amt'] += vpo['total'];
			}
			
			sumTtl+= vp['total'] * 1;
		}
		
		sumAll = A.sttl * 1;
		
		for(var k in tmpP){
			v  = tmpP[k];
			poTtl+= '<p class="po_ttl">'+v.name+'<span class="spc10_l">'+v.amt+'</span></p>';
		}
		
		$('#rgtPoint').html(ljNumFormat(poTtl));
		$('#rgtTotal').html(ljNumFormat(sumTtl));
		$('#rgtJimu').html(ljNumFormat(fsyu));
		$('#rgtDaib').html(ljNumFormat(fdaib));
		$('#rgtAll').html(ljNumFormat(sumAll));
		$('#fSyu').val(fsyu);
		$('#fDaib').val(fdaib);
	}
	
	function ljNumFormat(A){
		return A.toLocaleString();
	}
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
