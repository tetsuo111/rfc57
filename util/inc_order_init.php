<?php

//----- リスト作成 -----
$cur_ar   = zCurrencyList();		//通貨
$rank_ar  = zRankList();			//ランク
$point_ar = zPointList();			//ポイント
$rate_ar  = zRateList();			//通貨レート
$culc_ar  = zCulcList();			//手数料計算
$pay_ar   = zPaymentList();			//支払方法

$base_cur = $cur_ar[0];				//基準通貨

//▼品目一覧
$query =  tep_db_query("
	SELECT
		`m_item_id`          AS `id`,
		`m_item_name`        AS `name`,
		`m_item_fixamount`   AS `famt`,
		`m_item_currency_id` AS `cur_id`
	FROM  `".TABLE_M_ITEM."`
	WHERE `state` = '1'
	ORDER BY `m_item_id` ASC
");

while($a = tep_db_fetch_array($query)) {
	$item_ar[$a['id']] = $a;
}


//▼初期設定 通貨id (支払ID,支払名)
$pay_way_ar["0"]["0"] = '銀行振込';

//▼通貨別支払方法
$query =  tep_db_query("
	SELECT
		`m_payment_id`     AS `paymentid`,
		`m_payment_name`   AS `name`,
		`m_payment_target` AS `target`
	FROM  `".TABLE_M_PAYMENT."`
	WHERE `state` = '1'
	ORDER BY `m_payment_id` ASC
");

while($a = tep_db_fetch_array($query)) {
	
	//▼対応通貨配列
	$tmp_ar = explode('-',trim($a['target'],'-'));
	
	//▼通貨毎に　支払方法を振り分け
	foreach($tmp_ar AS $vcurid){
		$pk = $a['paymentid'];
		$pay_way_ar[$vcurid][$pk] = $a['name'];
	}
}

$pay_way_ar['a']['a'] = ' 内容ごとに個別に選択';


//----- 注文表示 -----//
$cart_page = basename($_SERVER['PHP_SELF'],'.php');

if(
	($cart_page == 'order_regular_cart')
	OR($cart_page == 'order_regular')
){
	$cart_sort = "'c'";
}else{
	$cart_sort = "'a','b'";
}


//▼選択中のカートを見る
$query_crt =  tep_db_query("
	SELECT
		`plan_id`,
		`user_o_cart_number` AS `num`
	FROM `".TABLE_USER_O_CART."`
	WHERE `state`       = '1'
	AND   `user_id` = '".tep_db_input($user_id)."'
	AND   `user_o_cart_condition` = '1'
	AND   `user_o_cart_sort` IN (".$cart_sort.")
");

$ncart = 0;
while($cr = tep_db_fetch_array($query_crt)) {
	$cart_ar[$cr['plan_id']] = $cr['num'];
	
	$ncart++;
}


//注文検索
$search_plan = ($m_plan_id)? "AND `m_plan_id` = '".tep_db_input($m_plan_id)."'":"";

//▼ランク取得
$query_rank = tep_db_query("
	SELECT 
		`u`.`bctype`,
		`r`.`m_rank_id`         AS `r_id`,
		`r`.`m_rank_order`      AS `r_order`
	FROM      `".TABLE_MEM00000."` `u`
	LEFT JOIN `".TABLE_M_RANK."` `r` ON `r`.`m_rank_bctype` = `u`.`bctype`
	WHERE `u`.`memberid` = '".tep_db_input($user_id)."'
	AND   `r`.`state`    = '1'
");


//▼注文ランク条件
if($bctt = tep_db_fetch_array($query_rank)){
	
	//▼検索条件があればそのまま
	if($search_plan){
		$rank_search = $search_plan;
		
	}else{
		
		//▼以上ランクを取得
		$query_rank = tep_db_query("
			SELECT
				`p`.`m_plan_id`,
				`p`.`m_plan_name`,
				`r`.`m_rank_id`    AS `r_id`,
				`r`.`m_rank_order` AS `order`
			FROM `".TABLE_M_PLAN."` `p`
			LEFT JOIN `".TABLE_M_RANK."` `r` ON `r`.`m_rank_id` = `p`.`m_plan_rank_id`
			WHERE `r`.`state`    = '1'
			AND   `p`.`state`    = '1'
			AND   `r`.`m_rank_order` <= ".tep_db_input($bctt['r_order'])."
		");
		
		if(tep_db_num_rows($query_rank)){
			
			//▼データIDを取得
			while($c = tep_db_fetch_array($query_rank)){
				$for_get_ovpl.= (($for_get_ovpl)? ",'":"'").$c['m_plan_id']."'";
			}
			
			//▼以上ランク設定
			$over_in = "OR(`m_plan_id` IN(".$for_get_ovpl."))";
		}
		
		//▼検索指定
		$rank_search ="AND (";
		$rank_search.= "((`m_plan_limited_id` = '".tep_db_input($bctt['r_id'])."')AND(`m_plan_rank_id` = 0))";	//限定ランク
		$rank_search.= $over_in;													//以上ランク
		$rank_search.= "OR((`m_plan_limited_id` = 0)AND(`m_plan_rank_id` = 0))";								//指定なし
		$rank_search.= ")";
	}
	
}else{
	
	//▼ユーザー以外の登録
	$query_rank = tep_db_query("
		SELECT 
			`m_rank_id` AS `aiid`
		FROM      `".TABLE_M_RANK."` 
		WHERE `m_rank_bctype` = '21'
		AND   `state`    = '1'
	");
	
	$ai = tep_db_fetch_array($query_rank);
	
	$rank_search = "AND `m_plan_limited_id` != '".tep_db_input($ai['aiid'])."'";
	$rank_search.= $search_plan;
	
}


//▼初期設定　＞　限定ランク以外の商品
$query =  tep_db_query("
	SELECT
		`m_plan_id`            AS `id`,
		`m_plan_name`          AS `name`,
		`m_plan_rank_id`       AS `rank_id`,
		`m_plan_grank_id`      AS `grank_id`,
		`m_plan_sort`          AS `sort`,
		`m_plan_sum_resource`  AS `sum_resource`,
		`m_plan_o_must`        AS `o_must`,
		`m_plan_o_limit_times` AS `o_limit_times`,
		`m_plan_o_limit_piece` AS `o_limit_piece`,
		`m_plan_caution`       AS `caution`
	FROM  `".TABLE_M_PLAN."`
	WHERE `state` = '1'
	AND   `m_plan_sort`      = '".tep_db_input($sort)."'
	AND   `m_plan_condition` = 'a'
	".$rank_search."
	ORDER  BY `m_plan_id` ASC
");

$op_text = ($user_id)? '内容を見る':'顧客画面での表示を見る';


while($a = tep_db_fetch_array($query)) {

	if($uorder_id){
		//編集の場合
		$operation = '';
		
	}else{
		
		//新規注文の場合
		if($m_plan_id){
			$operation = '<a href="'.$form_action_to.'">';
			$operation.= '<button type="button" class="btn btn-small">一覧に戻る</button>';
			$operation.= '</a>';
		}else{
			$operation = '<a href="'.$form_action_to.'?m_plan_id='.$a['id'].'">';
			$operation.= '<button type="button" class="btn btn-small">'.$op_text.'</button>';
			$operation.= '</a>';
		}
	}
	
	$cl_l = '';
	if($a['id'] == $m_plan_id){
		
		$p_id            = $a['id'];
		$p_name          = $a['name'];
		$p_rank_id       = $a['rank_id'];
		$p_grank_id      = $a['grank_id'];
		$p_sort          = $a['sort'];
		$p_sum_resource  = $a['sum_resource'];
		$p_o_must        = $a['o_must'];
		$p_o_limit_times = $a['o_limit_times'];
		$p_o_limit_piece = $a['o_limit_piece'];
		$p_caution       = $a['caution'];
		
		//$cl_l = 'class="lsel"';
	}
	
	//選択
	$sel = ($cart_ar[$a['id']])? '<i class="fa fa-check-circle spc10_r" style="color:#999;" aria-hidden="true"></i>': '';
	
	$list_in.= '<tr '.$cl_l.'>';
	$list_in.= '<th>'.$a['id'].'</td>';
	$list_in.= '<td>'.$sel.$a['name'].'</td>';
	$list_in.= '<td>'.$operation.'</td>';
	$list_in.= '</tr>';
}

//----- 表示リスト -----//
$list_head = '<th>商品ID</th><th>商品名</th><th>操作</th>';

$input_list = '<table class="table">';
$input_list.= '<thead>';
$input_list.= '<tr>'.$list_head.'</tr>';
$input_list.= '</thead>';
$input_list.= '<tbody>';
$input_list.= $list_in;
$input_list.= '</tbody>';
$input_list.= '</table>' ;

$form_ctl = 'form-control';


//----- 注文フォーム ----//
//詳細取得
if($m_plan_id){
	
	//--- 商品内容 ---//
	$ar = zPlanItemPoint($m_plan_id);
	$p_js_item  = $ar['item'];			//設定した品目
	$p_js_point = $ar['point'];			//設定したポイント
	$o_detail   = array();				//詳細登録用
	
	
	
	foreach($p_js_item AS $k => $v){

		//▼設定した品目データ
		$it = $item_ar[$v['id']];
		
		$iname  = $it['name'];
		$inum   = $v['num'];
		$iprice = $it['famt'] * $v['num'];
		$icur   = $cur_ar[$it['cur_id']];
		
		
		$tt.= '<tr>';
		$tt.= '<td>・'.$iname.'</td>';
		$tt.= '<td>数量:'.$inum.'</td>';
		$tt.= '<td>金額:'.number_format($iprice).$icur.'</td>';
		$tt.= '</tr>';
		
		//合計金額用　＞通貨毎に分類
		$sum_ar[$it['cur_id']]['sum'] += $iprice;
		$sum_ar[$it['cur_id']]['cur']  = $icur;
		$sum_ar[$it['cur_id']]['rate'] = $rate_ar[$it['cur_id']]['rate'];
		$sum_ar[$it['cur_id']]['base']+= $iprice / $rate_ar[$it['cur_id']]['rate'];
		
		//詳細登録用
		$o_detail['item'][$k] = array(
			'id'    => $k,
			'name'  => $iname,
			'num'   => $inum,
			'price' => $iprice,
			'cur'   => $icur
		);
		
	}
	

	$list_item = '<table class="notable">'.$tt.'</table>';
	
	//▼ポイント表示
	foreach($p_js_point AS $k => $v){
		
		//ポイント配列
		$iname = $point_ar[$k];
		$iamt  = $v['amt'];
		
		$tp.= '<tr>';
		$tp.= '<td>'.$iamt.$iname.'</td>';
		$tp.= '</tr>';
		
		//詳細登録用
		$o_detail['point'][$k] = array(
			'id'   => $k,
			'name' => $iname,
			'amt'  => $iamt
		);
	}
	
	$list_point = '<table class="notable">'.$tp.'</table>';
	
	//----- 支払設定 -----//
	if($sum_ar){
		
		//▼注文個数
		$oder_num_ar = ($p_o_limit_piece)? range(0,$p_o_limit_piece):range(0,15);
		unset($oder_num_ar[0]);
		$in_num      = zSelectListSet($oder_num_ar,$order_num,'order_num','▼個数を選択','','','','class="form-control" id="oNum" required') .'個';
		
		
		//--- 合計金額 ---//
		//▼合計金額計算
		foreach($sum_ar AS $k => $v){
			$list_sum.= '<p>'.number_format($v['sum']).$v['cur'].'</p>';
			
			//合計表示用
			if($v['cur'] != $base_cur){
				$prate .= '<p>1'.$v['cur'].'='.(1/$v['rate']).$base_cur.'</p>';
			}
			
			//金額
			$ptotal+= $v['base'];
		}
		
		
		//--- 商品手数料 ---//
		//▼商品手数料
		$query =  tep_db_query("
			SELECT
				`m_cost_name`    AS `name`,
				`m_cost_culc_id` AS `culc_id`
			FROM  `".TABLE_M_COST."`
			WHERE `state` = '1'
			AND   `m_cost_plan_id` = ".tep_db_input($m_plan_id)."
		");

		while($cdt = tep_db_fetch_array($query)) {
			$cost_ar[] = $cdt;
			
			//▼取得用
			$for_get_culc.= (($for_get_culc)? ",'":"'").$cdt['culc_id']."'";
		}
		
		
		//▼商品手数料の設定があれば計算
		if($cost_ar){
			
			//▼商品合計金額　＞　手数料計算用
			$item_total = $ptotal;
			
			//手数料は全て基準通貨換算
			$fcur_id = 0;
			
			//▼設定手数料毎に計算
			foreach($cost_ar AS $cdt){
				
				//▼初期設定
				$cid   = $cdt['culc_id'];
				$cname = $cdt['name'];
				
				//--- 手数料計算 ---//
				//▼計算設定開始
				$clc = new ClucFeeAmount($cid);
				$tgt = $clc->pGetItemId();		//計算品目を取得
				
				//▼取得id
				$id1 = $tgt['target1_id'];
				$id2 = $tgt['target2_id'];
				
				
				//▼金額を設定
				$clc->amount1 = ($id1)? $item_ar[$id1]:$item_total;
				$clc->amount2 = ($id2)? $item_ar[$id2]:0;
				
				//▼計算実行
				$tsum = $clc->pStartCulc();
				
				//▼表示内容
				$pct.= '<tr><td>・'.$cdt['name'].'</td><td>'.number_format($tsum).' '.$base_cur.'</td></tr>';
				$ptotal+= $tsum;
				
				
				//合計計算用
				$sum_ar[$fcur_id]['sum'] += $tsum;
				$sum_ar[$fcur_id]['cur']  = $base_cur;
				$sum_ar[$fcur_id]['rate'] = $rate_ar[$fcur_id]['rate'];
				$sum_ar[$fcur_id]['base']+= $tsum / $rate_ar[$fcur_id]['rate'];
				
				
				//詳細登録用
				$o_detail['culc'][$cid] = array(
					'id'      => $cid,
					'name'    => $cname,
					'amt'     => $tsum,
					'curname' => $base_cur
				);
			}
		}
		
		//▼手数料表示
		$p_cost = '<table class="notable">'.$pct.'</table>';
		
		
		//▼最終登録用
		$o_detail['sum'] = $sum_ar;
		
		
		//----- 支払通貨 -----//
		//▼支払通貨確認
		$selected_cur = (count($charge_ar) == 1)? key($charge_ar) :'';

		//▼支払通貨選択
		foreach($cur_ar AS $k => $v){
			$ch_cur = ($selected_cur === $k)? 'checked':'';

			$p_cur_in.= '<div class="form-check">';
			$p_cur_in.= '<label class="form-check-label"><input type="radio" class="i_radio form-check-input" name="cur" value="'.$k.'" '.$ch_cur.'> 「'.$v.'」で払う</label>';
			$p_cur_in.= '</div>';
		}
		
		//▼スタイル指定
		$st_a   = 'style="width:80px;"';
		$st_c   = 'style="width:60px;"';
		$read   = 'readonly';
		
		//▼合計配列　＞$k：通貨ID　$vs：合計内容
		foreach($sum_ar AS $k => $vs){
			$tsamll   = '';				//一時保管用
			$vcur     = $vs['cur'];		//設定通貨
			$c_cur_ar = $cur_ar;		//選択用
			unset($c_cur_ar[$k]);		//自身の通貨を抜く
			
			$id_cur = 'id-cur="'.$k.'"';
			$fl     = 'class="fl_l"';
			
			
			//--- 個別選択 ---//
			//数量設定
			$amt_main = '<span '.$fl.'>：<input type="text"   name="mamt['.$k.']" '.$st_a.' value="'.$vs['sum'].'" '.$read.'> '.$vcur.'を</span>';
			$amt_sub  = '<span '.$fl.'>：<input type="number" name="samt['.$k.']" '.$st_a.' class="sAmt " '.$id_cur.' pattern="^[0-9]+" min="0" max="'.$vs['sum'].'"> '.$vcur.'を</span>';
			
			//通貨選択
			$cur_main = '<span '.$fl.' style="margin-left:10px;"><input type="hidden" name="mcur['.$k.']" value="'.$k.'">「'.$vcur.'」で払う</span>';
			$cur_sub  = '<span '.$fl.' style="margin-left:10px;">'.zSelectListSet($c_cur_ar,$data_in,'scur['.$k.']','▼通貨','','','',$id_cur.' class="sCur"').'で払う</span>';
			
			$in_main = '<p class="sub_in float_clear">'.$amt_main.$cur_main.'</p>';
			$in_sub  = '<p class="sub_in float_clear">'.$amt_sub.$cur_sub.'</p>';
			
			//表示設定
			$small_in.= '<tr>';
			$small_in.= '<td>'.$vs['sum'].' '.$vcur.'の内 </td>';
			$small_in.= '<td><div class="sSmall" id-cur="'.$k.'">'.$in_main.' '.$in_sub.'</div></td>';
			$small_in.= '</tr>';
		}
		
		$cur_small = '<table class="notable2">'.$small_in.'</table>';
		
		
		//▼支払通貨選択
		if(count($sum_ar) > 1){
			
			$ch_cura = (count($charge_ar) > 1)? 'checked':'';
			
			$p_cur_in.= '<div class="form-check">';
			$p_cur_in.= '<label class="form-check-label"><input type="radio" class="i_radio form-check-input" name="cur" value="a" '.$ch_cura.'> 個別に設定する</label>';
			$p_cur_in.= '</div>';
			
			$p_small  = '<div id="dPay" class="fl_r">'.$cur_small.'</div>';
		}
		
		$p_cur = '<div class="fl_l">'.$p_cur_in.'</div>';
		$p_cur.= ($p_small)? $p_small:'';
	}
	
}else{
	$list_item  = '-';
	$list_point = '-';
}


$in_id      = $p_id;
$in_name    = $p_name;
$in_grank   = $rank_ar[$p_grank_id];
$in_item    = $list_item;
$in_point   = $list_point;
$in_caution = $p_caution;
$in_sum     = $list_sum;
$in_cost    = $p_cost;

$in_total_s = number_format($ptotal).' '.$base_cur;


//確認ボタン
$dis_n = ($uorder_id)? '':'disabled="disabled"';
$btn_cart = '<button type="button" class="spc10 btn" id="inCart" '.$dis_n.'>';
$btn_cart.= '<i class="fa fa-shopping-cart" aria-hidden="true"></i>';
$btn_cart.= '<span class="spc10_l btn btn-default">カートに入れる</span>';
$btn_cart.= '</button>';




//----- 表示フォーム -----//
//▼注文内容
$input_form = '<table class="table list_table"">';
$input_form.= '<tr><th style="width:110px;">商品番号</th><td style="min-width:150px;">'.$in_id.'</td></tr>';
$input_form.= '<tr><th>商品名</th>      <td>'.$in_name.'</td></tr>';
$input_form.= '<tr><th>開始ランク</th>  <td>'.$in_grank.'</td></tr>';
$input_form.= '<tr><th>商品内容</th>    <td>'.$in_item.'</td></tr>';
$input_form.= '<tr><th>獲得ポイント</th><td>'.$in_point.'</td></tr>';
$input_form.= '<tr><th>注意事項</th>    <td>'.$in_caution.'</td></tr>';
$input_form.= '<tr><th>商品合計</th>    <td>'.$in_sum.'</td></tr>';
$input_form.= '<tr><th>手数料</th>      <td>'.$in_cost.'</td></tr>';
$input_form.= '<tr><th>合計金額</th>    <td>'.$in_total_s.'</td></tr>';
$input_form.= '</table>';


//▼注文個数
$stx = 'style="display:flex; justify-content:flex-start; align-items:center;"';
$input_form1 = '<div class="p_area">';
$input_form1.= '<div '.$stx.'>'.$in_num.'</div>';
$input_form1.= '</div>';
$input_form1.= $btn_cart;

?>