<?php 
//▼計算式リスト
$culc_ar  = zCulcList();

//▼支払方法手数料
$query =  tep_db_query("
	SELECT
		`culc_id`            AS `cid`,
		`m_payment_fee_name` AS `name`
	FROM  `".TABLE_M_COST."`
	WHERE `state` = '1'
	AND   `payment_id` = '".tep_db_input($payment_id)."'
");


//▼登録設定
$stc    = 'style="margin-right:10px;"';
$cl_clc = 'class="culcs"';				//変化検出用

if(tep_db_num_rows($query)){
	$j = 0;
	
	while($b = tep_db_fetch_array($query)){
		$cld   = ($im_payment_culcid)? 'class="spc10"':'';
		$im_payment_culcid.= '<div '.$cld.'>';
		$im_payment_culcid.= '名前：<input type="text" size="6" name="culc['.$j.'][name]" value="'.$b['name'].'" '.$cl_clc.' '.$stc.'>';
		$im_payment_culcid.= zSelectListSet($culc_ar,$b['cid'],'culc['.$j.'][id]','▼手数料','','','',$cl_clc);
		$im_payment_culcid.= '</div>';
	}
}else{
	
	$im_payment_culcid = '名前：<input type="text" size="6" name="culc[0][name]" value="" '.$stc.' '.$cl_clc.'>';
	$im_payment_culcid.= zSelectListSet($culc_ar,$p_culcid,'culc[0][id]','▼手数料','','','',$cl_clc);
}

//▼手数料追加
$add_culc = '<div class="spc10">';
$add_culc.= '<button type="button" id="AddCulc" disabled>手数料を追加</button>';
$add_culc.= '<button type="button" id="RmvCulc" class="spc10_l">削除</button>';
$add_culc.= '</div>';

//javascript引継ぎ用
$culc_base = '<div class="spc10">';
$culc_base.= '名前：<input type="text" size="6" name="culc[A][name]" value="" '.$stc.' '.$cl_clc.'>';
$culc_base.= zSelectListSet($culc_ar,'','culc[A][id]','▼手数料','','','',$cl_clc);
$culc_base.= '</div>';
?>