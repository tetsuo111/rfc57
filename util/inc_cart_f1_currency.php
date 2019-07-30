<?php

//基準通貨しかなければ処理を飛ばす
if(count($cur_ar) == 1){
	
	//▼基準通貨のみを登録
	$p_cur_in = '<div class="form-check">';
	$p_cur_in.= '<label class="form-check-label">';
	$p_cur_in.= '<input type="radio" class="i_radio form-check-input" name="cur" value="0" checked> 「'.$base_cur.'」で払う';
	$p_cur_in.= '</label>';
	$p_cur_in.= '</div>';
	
	//▼支払方法表示
	$issHide2 = 'isHide';
	$isshow3  = 'isShow';
	
	//▼引継ぎ用配列
	$psingle = $sum_ar[0];
	$paysel_single = array(
		'curid'   => '0',
		'curname' => $psingle['cur'],
		'amt'     => $psingle['sum'],
		'rate'    => $psingle['rate']
	);
	
}else{
	
	//▼支払通貨確認　＞　注文編集対応
	$selected_cur = (count($charge_ar) == 1)? key($charge_ar) :'';

	//▼支払通貨選択
	foreach($cur_ar AS $k => $v){
		$ch_cur   = ($selected_cur === $k)? 'checked':'';
		$p_cur_in.= '<div class="form-check">';
		$p_cur_in.= '<label class="form-check-label"><input type="radio" class="i_radio form-check-input" name="cur" value="'.$k.'" '.$ch_cur.'> 「'.$base_cur.'」で払う</label>';
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
	
	
	//▼複数通貨の設定
	if(count($sum_ar) > 1){
		
		$ch_cura = (count($charge_ar) > 1)? 'checked':'';
		
		$p_cur_in.= '<div class="form-check">';
		$p_cur_in.= '<label class="form-check-label"><input type="radio" class="i_radio form-check-input" name="cur" value="a" '.$ch_cura.'> 個別に設定する</label>';
		$p_cur_in.= '</div>';
		
		$p_small  = '<div id="dPay" class="fl_r">'.$cur_small.'</div>';
	}
	
	//▼支払方法ボタン
	$btn_ps3 = '<button type="button" class="spc10 btn" id="nxtPs3">支払方法を選ぶ</button>';
}

//▼支払通貨
$p_cur = '<div class="fl_l">'.$p_cur_in.'</div>';
$p_cur.= ($p_small)? $p_small:'';

?>