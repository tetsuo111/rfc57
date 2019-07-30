<?php 
//----- 入力フォーム -----//
//▼自動入力要素
$input_auto = '<input type="hidden" name="act" value="process">';
$button_del = '<input type="submit" class="form_submit" name="act_del" value="削除">';

//▼登録ボタン
$input_button = '<input type="submit" class="form_submit" name="act_send" value="この内容で登録する">';
$input_button.= ($_GET['m_plan_id'])? $button_del:'';
$input_button.= '<a class="spc10_l" href="'.$form_action_to.'">クリア</a>';


//★入力項目
$st   = 'style="width:100px;"';
$req  = 'required';
$read = 'readonly';

$im_plan_id            = '<input type="text" class="input_text" name="m_plan_id"   value="'.$p_id.'"   id="pID" '.$req.'  '.$st.' '.(($m_plan_id)? $read:'').' pattern="^([a-zA-Z0-9-_]{1,3})$">';
$im_plan_name          = '<input type="text" class="input_text" name="m_plan_name" value="'.$p_name.'" '.$req.'>';
$im_plan_caution       = '<textarea name="m_plan_caution" class="txarea">'.$p_caution.'</textarea>';

//▼自動入力
$im_plan_sum           = '<input type="text" id="sumFix"  class="input_text" name="m_plan_sum"          value="'.$p_sum.'"          '.$read.' '.$st.'> '.$base_cur;
$im_plan_sum_resource  = '<input type="text" id="sumReso" class="input_text" name="m_plan_sum_resource" value="'.$p_sum_resource.'" '.$read.' '.$st.'> '.$base_cur;


//▼チェックリスト
$im_plan_o_must        = '<input type="checkbox" class="i_radio" name="m_plan_o_must" value="'.$p_o_must.'" >必須';

//▼リスト対応
$im_plan_limited_id    = zSelectListSet($rank_ar ,$p_limited_id   ,'m_plan_limited_id'   ,'▼限定なし');
$im_plan_rank_id       = zSelectListSet($rank_ar ,$p_rank_id      ,'m_plan_rank_id'      ,'▼全対象');
$im_plan_grank_id      = zSelectListSet($rank_ar ,$p_grank_id     ,'m_plan_grank_id'     ,'▼ランクなし');
$im_plan_o_limit_times = zSelectListSet($LimitNum,$p_o_limit_times,'m_plan_o_limit_times','▼制限なし');
$im_plan_o_limit_piece = zSelectListSet($LimitNum,$p_o_limit_piece,'m_plan_o_limit_piece','▼制限なし');
$im_plan_taxtype       = zRadioSet($TaxType ,$p_plan_taxtype      ,'m_plan_taxtype'      ,'required');


//▼選択内容を取得
if($m_plan_id){
	$ar = zPlanItemPoint($m_plan_id);
	$im_pitem_ar = $ar['item'];			//品目
	$im_ppoi_ar  = $ar['point'];		//ポイント
}


//▼手数料追加
require('mut_fee_init.php');


//▼品目表示
foreach($item_ar AS $k => $vitm){
	//設定品目数
	$s_item = $im_pitem_ar[$k]['num'];
	
	$checkl.= '<tr>';
	$checkl.= '<th><p class="it_name"><a title="'.$vitm['name'].'" href="#">・'.$vitm['name'].'</a></p></th>';													//品目名
	$checkl.= '<td>'.number_format($vitm['famt']).' '.$cur_ar[$vitm['cur_id']].'</td>';		//単価
	$checkl.= '<td><input type="text" name="item['.$k.']" class="pd_m itmF" value="'.$s_item['num'].'" id-data="'.$k.'" size="2" pattern="^[0-9]$"> 個</td>';
	$checkl.= '</tr>';
}
$im_plan_js_item  = '<table class="notable">'.$checkl.'</table>';


//▼付与ポイント
foreach($point_ar AS $kpoi => $vpoi){
	//ポイント数量
	$s_poi = $im_ppoi_ar[$kpoi]['amt'];
	
	$poil.= '<tr>';
	$poil.= '<th>・'.$vpoi.'</th>';								//ポイント名
	$poil.= '<td><input type="text" name="poi['.$kpoi.']" class="pd_m" value="'.$s_poi.'" size="2" pattern="^[0-9]+"></td>';
	$poil.= '</tr>';
}

$im_plan_js_point = '<table class="notable">'.$poil.'</table>';


//★登録フォーム
$must = I_MUST;
$alm1 = '<span class="alert spc10_l">半角英数3文字以内(-_可)</span>';
$alm2 = '<span class="alert spc10_l">通貨レートで変動します</span>';

$alm3 = '<span class="alert spc10_l">1人の顧客が購入できる回数の上限</span>';
$alm4 = '<span class="alert spc10_l">1回の注文で注文できる個数の上限</span>';

$alm5 = '<p class="alert">設定した商品を購入できる下限のランクのこと<br>例えば、この商品は「販売店」以上が購入できる　など</p>';
$alm6 = '<p class="alert">この商品を購入したときに購入者に与えられるランク</p>';

$alm7 = '<p class="alert">設定した商品を購入できるランク。<br>設定したランクだけが買うことができる</p>';

$alm8 = '<p class="alert spc10">定期購入の自動登録。<br>設定した商品を購入した後に定期購入として追加される。</p>';


$input_form = '<form action="'.$form_action_to.$cont_set.'" method="post">';
$input_form.= $input_auto;
$input_form.= '<table class="input_form">';
$input_form.= '<tr><th>商品ID'.$must.'</th><td>'.$im_plan_id.$alm1.'</td></tr>';
$input_form.= '<tr><th>商品名'.$must.'</th><td>'.$im_plan_name.'</td></tr>';
$input_form.= '<tr><th>限定ランク</th><td>'.$im_plan_limited_id.$alm7.'</td></tr>';
$input_form.= '<tr><th>以上ランク</th><td>'.$im_plan_rank_id.$alm5.'</td></tr>';
$input_form.= '<tr><th>獲得ランク</th><td>'.$im_plan_grank_id.$alm6.'</td></tr>';
$input_form.= '<tr><th>購入品目'.$must.'</th><td>'.$im_plan_js_item.'</td></tr>';
$input_form.= '<tr><th>付与ポイント</th><td>'.$im_plan_js_point.'</td></tr>';
$input_form.= '<tr><th>合計価格</th><td>'.$im_plan_sum.$alm2.'</td></tr>';
$input_form.= '<tr><th>合計コミッション原資</th><td>'.$im_plan_sum_resource.'</td></tr>';
$input_form.= '<tr><th>回数制限</th><td>'.$im_plan_o_limit_times.$alm3.'</td></tr>';
$input_form.= '<tr><th>個数制限</th><td>'.$im_plan_o_limit_piece.$alm4.'</td></tr>';
$input_form.= '<tr><th>消費税</th><td>'.$im_plan_taxtype.'</td></tr>';
$input_form.= '<tr><th>注意事項</th><td>'.$im_plan_caution.'</td></tr>';

//▼連動購入
if($sort == 'c'){
	
	//▼初回購入商品
	$query =  tep_db_query("
		SELECT
			`m_plan_id`            AS `id`,
			`m_plan_name`          AS `name`
		FROM  `".TABLE_M_PLAN."`
		WHERE `state` = '1'
		AND   `m_plan_sort` = 'a'
		ORDER  BY `m_plan_id` ASC
	");
	
	while($a = tep_db_fetch_array($query)) {
		$first_plan_ar[$a['id']] = $a['name'];
	}
	
	//▼連動購入
	$in_first    = zSelectListSet($first_plan_ar,$p_add_plan_id,'m_plan_add_plan_id' ,'▼追加なし');
	$in_amt      = '<input type="number" name="m_plan_add_amount" style="width:40px;padding:5px;" value="'.$p_add_amount.'" min="0">';
	
	$cl_ad = 'class="spc10"';
	
	$im_plan_add = '<div>'.$in_first.'を購入した後、</div>';
	$im_plan_add.= '<div '.$cl_ad.'><span class="alert">定期購入</span>としてこの商品を'.$in_amt.'個登録する</div>';

	$input_form.= '<tr><th>自動追加</th><td>'.$im_plan_add.$alm8.'</td></tr>';
}

//$input_form.= '<tr><th>商品手数料</th><td><div id="FeeA">'.$im_payment_culcid.'</div>'.$add_culc.'</td></tr>';
$input_form.= '</table>';
$input_form.= '<div class="spc20">';
$input_form.= $input_button;
$input_form.= '</div>';
$input_form.= '</form>';

?>