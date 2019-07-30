<?php 
//★初期設定
$query =  tep_db_query("
	SELECT
		`m_plan_id`            AS `id`,
		`m_plan_name`          AS `name`,
		`m_plan_limited_id`    AS `limited_id`,
		`m_plan_rank_id`       AS `rank_id`,
		`m_plan_grank_id`      AS `grank_id`,
		`m_plan_sort`          AS `sort`,
		`m_plan_sum_resource`  AS `sum_resource`,
		`m_plan_o_must`        AS `o_must`,
		`m_plan_o_limit_times` AS `o_limit_times`,
		`m_plan_o_limit_piece` AS `o_limit_piece`,
		`m_plan_taxtype`       AS `taxtype`,
		`m_plan_caution`       AS `caution`,
		`m_plan_condition`     AS `condition`
	FROM  `".TABLE_M_PLAN."`
	WHERE `state` = '1'
	AND   `m_plan_sort` = '".tep_db_input($sort)."'
	ORDER  BY `m_plan_id` ASC
");


//▼状況設定
$cond_ar = array('a'=>'表示','b'=>'非表示');

while($a = tep_db_fetch_array($query)) {
	$operation = '<a href="'.$form_action_to.'?m_plan_id='.$a['id'].'"><button type="button" style="font-size:11px;">編集する</button></a>';
	
	//▼有効無効設定
	$cond    = ($a['condition'] == 'b')? 'b':'a';
	$cl_cond = ($cond == 'b')? 'ng':'ok';
	
	$on_able = '<p style="line-height:26px;">';
	$on_able.= '<input type="hidden" id="cD'.$a['id'].'" value="'.$cond.'">';
	$on_able.= '<span id="sH'.$a['id'].'" class="fl_l">';
	$on_able.= '<span class="'.$cl_cond.'">'.$cond_ar[$cond].'</span>';
	$on_able.= '</span>';
	$on_able.= '<button type="button" id-data="'.$a['id'].'" class="spc10_l chAble fl_r">変更する</button>';
	$on_able.= '<p>';
	
	$list_in.= '<tr>';
	$list_in.= '<td>'.$a['id'].'</td>';
	$list_in.= '<td>'.$a['name'].'</td>';
	//$list_in.= '<td>'.number_format($a['sum_resource']).' '.$base_cur.'</td>';
	$list_in.= '<td>'.$on_able.'</td>';
	$list_in.= '<td>'.$operation.'</td>';
	$list_in.= '</tr>';
	
	if($a['id'] == $m_plan_id){
		$p_id            = $a['id'];
		$p_name          = $a['name'];
		$p_limited_id    = $a['limited_id'];
		$p_rank_id       = $a['rank_id'];
		$p_grank_id      = $a['grank_id'];
		$p_type          = $a['type'];
		$p_sum           = $a['sum'];
		$p_sum_resource  = $a['sum_resource'];
		$p_o_must        = $a['o_must'];
		$p_o_limit_times = $a['o_limit_times'];
		$p_o_limit_piece = $a['o_limit_piece'];
		$p_plan_taxtype  = $a['taxtype'];
		$p_caution       = $a['caution'];
	}
}

//----- 表示リスト -----//
$list_head = '<th style="width:40px;">商品ID</th>';
$list_head.= '<th>商品名</th>';
//$list_head.= '<th>コミッション原資</th>';
$list_head.= '<th style="width:100px;">表示</th>';
$list_head.= '<th style="width:120px;">操作</th>';


$input_list = '<table class="input_list">'  ;
$input_list.= '<tr>'.$list_head.'</tr>';
$input_list.= $list_in;
$input_list.= '</table>' ;


//----- 自動登録 -----//
if($p_id && $sort == 'c'){
	
	//▼自動登録確認
	$query_add = tep_db_query("
		SELECT 
			`m_plan_add_plan_id` AS `add_pid`,
			`m_plan_add_amount`  AS `add_amt`
		FROM  `".TABLE_M_PLAN_ADD."`
		WHERE `state` = '1'
		AND   `m_plan_id` = '".tep_db_input($p_id)."'
	");
	
	$dt = tep_db_fetch_array($query_add);
	
	$p_add_plan_id = $dt['add_pid'];
	$p_add_amount  = $dt['add_amt'];
}

?>