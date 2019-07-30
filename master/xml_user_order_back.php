<?php
require('includes/application_top.php');
	
//▼初期設定
$res      = "err";
$top      = $_POST['top'];
$order_id = $_POST['order_id'];

$d_back   = $_POST['date_back'];	//返却日
$d_culc   = $_POST['date_culc'];	//計算日
$memo     = $_POST['memo'];			//備考

$plan_back = $_POST['pbnum'];		//返品商品


//▼データ取得
if($top && $order_id && $d_back && $d_culc && $memo && $plan_back){
	
	//▼データ取得
	$rate_ar  = zRateList();						//通貨レート
	$tax_rate = zGetSysSetting('sys_tax_rate');		//消費税率
	
	
	//----- 全注文情報取得 -----//
	//▼詳細情報を取得
	$query = tep_db_query("
		SELECT
			`user_order_id`,
			`position_id`,
			`user_id`,
			`user_o_detail_js_plan`  AS `plan`,
			`user_o_detail_js_item`  AS `item`,
			`user_o_detail_js_point` AS `point`,
			`user_o_detail_js_culc`  AS `culc`,
			`user_o_detail_js_sum`   AS `sum`,
			`user_o_detail_js_fee`   AS `fee`
		FROM `".TABLE_USER_O_DETAIL."`
		WHERE `user_order_id` = '".tep_db_input($order_id)."'
		AND   `state`         = '1'
	");
	
	
	//▼注文復元
	$b_dtail = tep_db_fetch_array($query);
	
	$dt_plans_ar = json_decode($b_dtail['plan'] ,true);	//商品　カートID
	$dt_item_ar  = json_decode($b_dtail['item'] ,true);	//品目　planid　　＞item_id
	$dt_point_ar = json_decode($b_dtail['point'],true);	//ポイント
	$dt_sum_ar   = json_decode($b_dtail['sum']  ,true);	//合計
	$dt_culc_ar  = json_decode($b_dtail['culc'] ,true);	//商品手数料
	$dt_fee_ar   = json_decode($b_dtail['fee']  ,true);	//支払手数料
	
	
	//----- 計算個数処理 -----//
	//▼カート情報取得　＞注文済みかつ有効なカート
	$query = tep_db_query("
		SELECT
			`user_id`,
			`user_o_cart_id`        AS `cart_id`,
			`plan_id`,
			`user_o_cart_number`    AS `num`,
			`user_o_cart_sort`      AS `sort`
		FROM `".TABLE_USER_O_CART."`
		WHERE `order_id` = '".tep_db_input($order_id)."'
		AND   `user_o_cart_condition` = 'a'
		AND   `state` = '1'
	");
	
	
	//▼注文商品ごとに格納
	while($a = tep_db_fetch_array($query)){
		
		//▼計算用
		$tmp     = $a;
		$cart_id = $a['cart_id'];
		
		//▼新しい個数を計算
		//注文個数 - 返品個数
		$num        = $a['num'] - $plan_back[$a['plan_id']];
		$tmp['num'] = $num;
		
		
		//▼計算用配列に格納
		if($num > 0){
			$cart_ar['re'][$a['plan_id']]  = $tmp;		//再計算
		}else{
			$cart_ar['del'][$a['plan_id']] = $tmp;		//削除
			
			//登録対象から削除
			unset($dt_plans_ar[$cart_id]);
		}
		
		//▼初回セット検出
		if($a['sort'] == 'a'){
			$reset_rank = true;		//ランクを元に戻す
		}
		
		if(!$user_id){
			$user_id = $a['user_id'];
		}
	}
	
	
	//----- カート更新 -----//
	//▼カート情報を取得
	$re_ar   = $cart_ar['re'];
	$del_ar  = $cart_ar['del'];
	
	
	//odr00000 odr00001から計算
	//▼DB定義
	$tb_ordr = TABLE_USER_ORDER;		//注文
	$tb_cart = TABLE_O_CART;			//カート
	$tb_dtai = TABLE_USER_O_DETAIL;		//詳細
	
	$tb_o00  = TABLE_ODR00000;			//注文情報0
	$tb_o01  = TABLE_ODR00001;			//注文情報1
	
	$tb_m00  = TABLE_MEM00000;			//顧客情報
	$tb_m22  = TABLE_MEM02002;			//調整金
	
	
	//----- 顧客情報 -----//
	//▼顧客情報を取得
	$query = tep_db_query("
		SELECT
			`memberid`,
			`login_id`,
			`name1`,
			`bctype`
		FROM `".$tb_m00."`
		WHERE `memberid` = '".tep_db_input($user_id)."'
	");
	
	//▼注文商品ごとに格納
	$a = tep_db_fetch_array($query);
	
	$mem_bctype = $a['bctype'];
	
	//▼調整金定型
	$add_adjust_ar = array(
		'memberid' => $a['memberid'],
		'login_id' => $a['login_id'],
		'name1'    => $a['name1'],
		'calcdate' => $d_culc,
		'memo'     => $memo
	);
	
	
	//----- 調整金計算 -----//
	if($re_ar){
		
		//再計算のみ
		//▼商品単価
		$query = tep_db_query("
			SELECT
				`itemid`,
				`price_outtax`,
				`point`
			FROM `".$tb_o01."`
			WHERE `orderid` = '".tep_db_input($order_id)."'
		");
		
		//▼再計算配列に追加
		while($b = tep_db_fetch_array($query)){
			$re_ar[$b['itemid']]['price'] = $b['price_outtax'];
			$re_ar[$b['itemid']]['point'] = $b['point'];
			
			//▼計算個数
			//$b['itemid'] は plan_id
			$c_num = $re_ar[$b['itemid']]['num'];
			
			//▼合計金額　＞商品単価 * 商品個数
			$total_notax   += $b['price_outtax'] * $c_num;
			$total_resource+= $b['point']        * $c_num;
		}
		
		//▼品目配列を作成
		foreach($dt_item_ar AS $dtitem){
			
			//▼品目の価格を取得
			foreach($dtitem AS $k => $v){
				$item_ar[$k] = $v['famount'];
			}
		}
		
		
		//▼手数料再計算
		//手数料の再計算は出荷前のみ
		foreach($dt_fee_ar AS $vf){
			
			$tfee_ar = '';
			for($i=0;$i<count($vf);$i++){
				
				$cdt = $vf[$i];
				
				//▼計算実行
				if($top == 'back_after' && $cdt['name'] != '消費税'){
					
					//出荷後の消費税以外（代引き手数料など）はそのまま加算
					//＞注文金額に含めて計算する
					$tsum = $cdt['amt'];
					
				}else{
					
					//▼初期設定
					$cid    = $cdt['culcid'];		//計算ID
					$cname  = $cdt['name'];			//手数料名
					$cur_id = $cdt['currencyid'];	//通貨名
					
					//--- 手数料計算 ---//
					//▼計算設定開始
					$clc = new ClucFeeAmount($cid);
					$tgt = $clc->pGetItemId();		//計算品目を取得
					
					//▼取得id
					$id1 = $tgt['target1_id'];
					$id2 = $tgt['target2_id'];
					
					//▼金額を設定
					$clc->amount1 = ($id1)? $item_ar[$id1]:$total_notax;
					$clc->amount2 = ($id2)? $item_ar[$id2]:0;
					
					//▼計算実行
					$tsum = $clc->pStartCulc();
				}
				
				if($tsum){
					$tfee_ar[] = array('culcid'=> $cid,'name'=>$cname,'amt'=>$tsum,'currencyid'=>$cur_id);
					
					//▼合計手数料
					$fee_total+= $tsum;
				}
			}
			
			//▼注文全体の手数料
			$odr_fee_ar[] = $tfee_ar;
		}
		
		//▼手数料集計
		require('../util/inc_cart_prc_fee.php');
		
		
		//--- 調整金登録 ---//
		//受け取り金額の差額を調整金に回す
		//▼受け取り金額
		$query = tep_db_query("
			SELECT
				`recmoney`
			FROM `".$tb_o00."`
			WHERE `orderid` = '".tep_db_input($order_id)."'
		");
		
		//請求金額＆支払金額
		$b = tep_db_fetch_array($query);
		$c_recmoney = $total_notax + $fee_total;
		
		//=======================
		//▼調整金の登録　＞受け取り金額 － 計算金額
		$add_adjust_ar['money'] = $b['recmoney'] - $c_recmoney;
		//=======================
		
		//--- データ更新 ---//
		//▼Order更新
		$up_odr_ar = array(
			'user_order_amount'    => $total_notax + $fee_total,
			'user_order_date_back' => $d_back,
			'user_order_resource'  => $total_resource,
			'date_update'          => 'now()'
		);
		
		zDBUpdate($tb_ordr,$up_odr_ar,$order_id);
		
		
		//▼Cart更新
		foreach($re_ar AS $planid => $cart){
			
			//▼更新用配列
			$up_cart_ar = array(
				'user_o_cart_number'      => $cart['num'],
				'date_update'             => 'now()',
				'user_o_cart_b_num'       => $plan_back[$planid],
				'user_o_cart_b_date_back' => $d_back,
				'user_o_cart_b_memo'      => $memo,
			);
			
			zDBUpdate($tb_cart,$up_cart_ar,$cart['cart_id']);
		}
		
		
		//Odr00000　更新
		//Odr00001　更新
		//▼登録データ
		$ci_sumintax  = $total_notax + $fee['tax']['base'];		//商品代金合計（税込）
		$ci_sumouttax = $total_notax;							//商品代金合計（税抜き）
		$ci_sumtax    = $fee['tax']['base'];					//消費税合計
		$ci_sendfee   = $fee['syu']['base'];					//出荷事務手数料
		$ci_daibiki   = $fee['daib']['base'];					//代引手数料
		$ci_sumprice  = $total_notax + $fee_total;				//合計金額　商品合計+支払手数料
		$ci_sumpoint  = $total_resource;						//合計原資
		
		//▼odr00000
		$up_o00_ar = array(
			'sumitem_intax'  => $ci_sumintax,
			'sumitem_outtax' => $ci_sumouttax,
			'sumtax'         => $ci_sumtax,
			'sendfee'        => $ci_sendfee,
			'daibikifee'     => $ci_daibiki,
			'sumprice'       => $ci_sumprice,
			'sumpoint'       => $ci_sumpoint,
			'recmoney'       => $c_recmoney
		);
		
		//▼検索設定
		$w_o0_set = "`orderid`='".tep_db_input($order_id)."'";
		tep_db_perform($tb_o00,$up_o00_ar,'update',$w_o0_set);
		
		
		//▼合計金額更新
		foreach($dt_sum_ar AS $k => $vs){
			$dtt_sum         = $vs;
			$dtt_sum['sum']  = $total_notax;
			$dtt_sum['base'] = $total_notax / $vs['rate'];
			
			//▼データ更新
			$dt_sum_ar[$k]   = $dtt_sum;
		}
		
		
		//----- 注文詳細 -----//
		//▼商品単位で処理
		foreach($re_ar AS $k => $v){
			
			//▼単体商品で計算
			$qty     = $v['num'];
			$cart_id = $v['cart_id'];
			$plan_id = $k;
			
			//▼一時登録
			$dttmp   = $dt_plans_ar[$cart_id];
			
			$price_a = $v['price'];
			$price_b = $v['price'] * $qty;
			$tax_a   = zCulcTax($price_a,$tax_rate);		//単体基準消費税
			$tax_b   = zCulcTax($price_b,$tax_rate);		//単体基準消費税
			
			//▼odr00001
			$up_o01_ar[] = array(
				'qty'          => $qty,
				'price_intax'  => ($price_a + $tax_a),
				'price_outtax' => $price_a,
				'tax'          => $tax_b,
				'sumprice'     => ($price_b + $tax_b),
				'point'        => $v['point'],
				'sumpoint'     => $v['point'] * $qty
			);
			
			$w_o1_set = "`orderid`='".tep_db_input($order_id)."' AND `itemid`='".tep_db_input($plan_id)."'";
			tep_db_perform($tb_o01,$up_o01_ar,'update',$w_o1_set);
			
			//▼更新用
			$prs_ar = $dttmp['price'];
			$price  = '';
			
			foreach($prs_ar AS $cur_id => $vpr){
				
				//▼金額データ
				$tmp_price = $vpr;
				
				//▼金額再計算
				$a0 = $price_a;
				$a1 = $price_a / $rate_ar[$cur_id]['rate'];
				$b0 = $price_b;
				$b1 = $price_b / $rate_ar[$cur_id]['rate'];
				
				$tmp_price['amt_a']  = $a0;
				$tmp_price['base_a'] = $a1;
				$tmp_price['amt_b']  = $b0;
				$tmp_price['base_b'] = $b1;
				
				//▼登録用配列
				$price[] = $tmp_price;
			}
			
			$dttmp['num']         = $v['num'];
			$dttmp['sumresource'] = $v['resource'] * $v['num'];
			$dttmp['price']       = $price;
			$dttmp['base_a']      = $price_a;
			$dttmp['tax_a']       = $tax_a;
			$dttmp['base_b']      = $price_b;
			$dttmp['tax_b']       = $tax_b;
			
			//▼登録データ更新
			$dt_plans_ar[$cart_id] = $dttmp;
		}
		
		
		
		//----- 全体の履歴を保存して終了 -----//
		//▼詳細再計算
		$up_d_plan  = json_encode($dt_plans_ar);
		$up_d_point = json_encode($dt_point_ar);
		$up_d_sum   = json_encode($dt_sum_ar);
		$up_d_culc  = json_encode($dt_culc_ar);
		$up_fee     = json_encode($dt_fee_ar);
		
		//▼Detail　　更新
		$up_dtai_ar = array(
			'user_order_id'          => $order_id,
			'position_id'            => $b_dtail['position_id'],
			'user_id'                => $b_dtail['user_id'],
			'user_o_detail_js_plan'  => $up_d_plan,
			'user_o_detail_js_item'  => $b_dtail['item'],
			'user_o_detail_js_point' => $up_d_point,
			'user_o_detail_js_culc'  => $up_d_culc,
			'user_o_detail_js_sum'   => $up_d_sum,
			'user_o_detail_js_fee'   => $up_fee,
			'date_create'            => 'now()',
			'state'                  => '1'
		);
		
		
		//▼検索設定
		$old_ar     = array('date_update' => 'now()','state' => '1');
		$w_dtai_set = "`user_order_id`='".tep_db_input($order_id)."' AND `state`='1'";
		tep_db_perform($tb_dtai,$old_ar,'update',$w_dtai_set);
		
		//▼データ更新
		tep_db_perform($tb_dtai,$up_dtai_ar);
	}
	
	
	//----- データ削除 -----//
	if($del_ar){
		
		if($re_ar){
			
			//--- データ一部削除 ---//
			//必要金額は再計算しているので単に削除
			foreach($del_ar AS $pln_id => $dt){
				
				//▼情報取得
				$cart_id           = $dt['cart_id'];		//カートID
				$del_cart_ar       = '';					//削除配列
				$del_ar[$pln_id]['del_num'] = $plan_back[$pln_id];	//削除数を追加
				
				//▼カート削除
				$del_cart_ar = array(
					'user_o_cart_b_master_id' => $_COOKIE['master_id'],
					'user_o_cart_b_num'       => $plan_back[$pln_id],
					'ser_o_cart_b_date_back'  => $d_back,
					'user_o_cart_b_memo'      => $memo,
					'state'                   => 'b'
				);
				
				$w_cart_set = "`user_o_cart_id`='".tep_db_input($cart_id)."' AND `state`='1'";
				tep_db_input($tb_cart,$del_cart_ar,'update',$w_cart_set);
				
				
				//▼odr00001削除
				$del_o01_ar = array('delete_flg' => '1'	);
				$w_o01_set  = "`orderid`='".tep_db_input($order_id)."' AND `itemid`='".tep_db_input($pln_id)."'";
				tep_db_input($tb_o01,$del_o01_ar,'update',$w_o01_set);
			}
			
		}else{
			
			
			//--- データ全部削除 ---//
			//オーダー全返品の場合には受け取り金額を返す
			//▼受け取り金額
			$query = tep_db_query("
				SELECT
					`recmoney`
				FROM `".$tb_o00."`
				WHERE `orderid` = '".tep_db_input($order_id)."'
			");
			
			$b = tep_db_fetch_array($query);
			
			//▼出荷後返金の場合には消費税以外の手数料を引く
			//消費税は常に調整金に含める
			if($top == 'back_after'){
				
				for($i =0;$i<count($dt_fee_ar);$i++){
				
					//▼手数料データ
					$fee_dt = $dt_fee_ar[$i];
					
					//▼手数料を計算
					foreach($fee_dt AS $kf => $vf){
						
						//▼消費税以外を加算
						if($vf['amt'] != '消費税'){
							//▼合計手数料
							$after_fee+= $vf['amt'];
						}
					}
				}
			}
			
			//======================
			//▼調整金追加
			//出荷後の場合にh　受け取り金額 － 出荷手数料
			$add_adjust_ar['money']+= ($b['recmoney'] - $after_fee);
			//======================
			
			
			//--- データ削除 ---//
			//▼注文削除
			$del_order_ar = array(
				'user_order_date_back' => $d_back,
				'date_update'          => 'now()',
				'state'                => 'b'
			);
			$w_odr_set = "`user_order_id` = '".tep_db_input($order_id)."' AND `state`='1'";
			$bod = tep_db_perform($tb_ordr,$del_order_ar,'update',$w_odr_set);
			
			
			//▼odr00000　削除
			$del_o0_ar = array(
				'editdate' => 'now()',
				'editflag' => '-1',
				'empid'    => $_COOKIE['master_id'],
				'candate'  => 'now()'
			);
			
			$w_o00_set = "`orderid` = '".tep_db_input($order_id)."'";
			$bo0 = tep_db_perform($tb_o00,$del_o0_ar,'update',$w_o00_set);
			
			
			//▼ランク削除
			//ユーザー以外は初回注文返品でランク削除
			if($reset_rank && $mem_bctype != '21'){
				
				//▼顧客ID
				$memid = $add_adjust_ar['memberid'];
				
				//▼削除配列
				$rank_del_ar = array(
					'editdate' => 'now()',
					'bctype'   => 'null'
				);
				
				//▼検索設定
				$w_set = "`memberid`='".tep_db_input($memid)."'";
				tep_db_perform($tb_o00,$rank_del_ar,'update',$w_set);
			}
		}
	}
	
	
	
	//▼最後に調整金を登録して終了
	if($add_adjust_ar['money'] != 0){
		//料金0の場合には調整金なしもあり得る
		tep_db_perform($tb_m22,$add_adjust_ar);
	}
	
	//▼情報の登録
	$res = 'ok';
}


/*==========================*/
function zzMstring($ar){
	
	foreach($ar AS $k0 => $v0){
		
		if(is_array($v0)){
			
			foreach($v0 AS $k1 => $v1){
				
				if(is_array($v1)){
					
					$string.= $k1."-----\n";
					foreach($v1 AS $k2 => $v2){
						
						if(is_array($v2)){
							
							$string.= "-----\n";
							foreach($v2 AS $k3 => $v3){
								$string.= $k3.' > '.$v3."\n";
							}
							
							$string.= "-----\n";
						}else{
							$string.= $k2.' > '.$v2."\n";
						}
					}
					
					$string.= "----------\n";
				}else{
					$string.= $k1.' > '.$v1."\n";
				}
			}
			$string.= "\n";
		}else{
			
			$string.= $k0.' > '.$v0."\n";
		}
	}
	
	return $string;
}


//-------- for test --------//
$string.= "dt_plans_ar\n";
$string.= "----------------\n";
$string.= zzMstring($dt_plans_ar);
$string.= "\n";

$string.= "sum_ar\n";
$string.= "----------------\n";
$string.= zzMstring($dt_sum_ar);
$string.= "\n";

$string.= "adjust\n";
$string.= "----------------\n";
$string.= zzMstring($add_adjust_ar);
$string.= $fee_total."\n";
$string.= "\n";

$string.= "del_ar\n";
$string.= "----------------\n";
$string.= zzMstring($del_ar);
$string.= "\n";

$string.= "update\n";
$string.= "----------------\n";
$string.= 'upodr:'.$bod."\n";
$string.= 'upo00:'.$bo0."\n";
$string.= "\n";

//write_log($string,'w');

echo $res;
?>