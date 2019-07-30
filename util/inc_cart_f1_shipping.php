<?php

//▼配送先を取得
$query =  tep_db_query("
	SELECT
		`adrpost`      AS `post1`,
		CONCAT(`adr1`,`adr2`,`adr3`,'　',`adr4`) AS `addr1`,
		`phone`        AS `phone1`,
		`otheradrpost` AS `post2`,
		CONCAT(`otheradr1`,`otheradr2`,`otheradr3`,'　',`otheradr4`) AS `addr2`,
		`otherphone`   AS `phone2`,
		`othername1`   AS `name2`
	FROM  `".TABLE_MEM00001."`
	WHERE `memberid` = '".tep_db_input($user_id)."'
");


$f = tep_db_fetch_array($query);
for($i=0;$i<2;$i++){
	$n = $i+1;
	if($f['post'.$n]){
		$fname = ($i == 0)? $_COOKIE['user_name']:$f['name'.$n];
		$tmp = '<p>'.$f['post'.$n].'</p>';
		$tmp.= '<p>'.$f['addr'.$n].'</p>';
		$tmp.= '<p>'.$fname.' 様</p>';
		$tmp.= '<p>'.$f['phone'.$n].'</p>';
		$add[$i] = $tmp;
	}
}


if($add[1]){
	
	//▼配送先選択
	$s_ar = array();
	
	foreach($SipArray AS $k => $v){
		$s_in = '<div class="form-check">';
		$s_in.= '<p>';
		
		if($ssip_noradio){
			$s_in.= $v;
		}else{
			$s_in.= '<input type="radio" name="ssip" value="'.$k.'" class="sSip form-check-input" required> '.$v;
		}
		$s_in.= '</p>';
		$s_in.= '</div>';
		
		$s_ar[] = $s_in;
	}
	
	$ssip_in = '<table class="notable">';
	$ssip_in.= '<tr><th style="width:60px;">'.$s_ar[0].'</th><td><div class="addr0">'.$add[0].'</div></td></tr>';
	$ssip_in.= '<tr><th>'.$s_ar[1].'</th><td><div class="addr0">'.$add[1].'</div></td></tr>';
	$ssip_in.= '</table>';
	
}else{
	
	//▼電話番号
	$query =  tep_db_query("
		SELECT
			`phone1`
		FROM  `".TABLE_MEM00000."`
		WHERE `memberid` = '".tep_db_input($user_id)."'
	");

	$tel = tep_db_fetch_array($query);
	
	
	$ssip_in1 = '<div id="regAddr" style="display:none; margin-top:20px;">';
	$ssip_in1.= '<ul>';
	$ssip_in1.= '<li>'.$f['post1'].'</li>';
	$ssip_in1.= '<li>'.$f['addr1'].'</li>';
	$ssip_in1.= '<li>'.$_COOKIE['user_name'].'</li>';
	$ssip_in1.= '<li>'.$tel['phone1'].'</li>';
	$ssip_in1.= '</ul>';
	$ssip_in1.= '</div>';
	
	$ssip_in2 = '<div id="regShip" style="display:none; margin-top:20px;">';
	$ssip_in2.= '<p>送り先を登録してください</p>';
	$ssip_in2.= '<ul class="ship_input">';
	$ssip_in2.= '<li style="overflow:hidden;">';
	$ssip_in2.= '<label>郵便番号'.I_MUST.'</label>';
	$ssip_in2.= '<div class="form-inline zip_area" style="width:100%;">';
	$ssip_in2.= '<input type="text" id="Za" class="form-control adZip iShip" style="width:4em;" name="o_zip_a" maxlength="3" value="">';
	$ssip_in2.= '<span class="fl_l"> - </span>';
	$ssip_in2.= '<input type="text" id="Zb" class="form-control adZip iShip spc10_r" style="width:5em;" name="o_zip_b" maxlength="4" value="" size="5" maxlength="4">';
	$ssip_in2.= '<button type="button" class="btn" id="aFromZip" disabled>郵便番号から検索</button>';
	$ssip_in2.= '</div>';
	$ssip_in2.= '</li>';

	$ssip_in2.= '<li class="add_in">';
	$ssip_in2.= '<label>都道府県'.I_MUST.'</label>';
	$ssip_in2.= '<input type="text" id="Apref"  class="form-control iShip" name="o_pref" value="" required>';
	$ssip_in2.= '</li>';

	$ssip_in2.= '<li class="add_in">';
	$ssip_in2.= '<label>市区'.I_MUST.'</label>';
	$ssip_in2.= '<input type="text" id="Acity"  class="form-control iShip" name="o_city" value="" required>';
	$ssip_in2.= '</li>';
	
	$ssip_in2.= '<li class="add_in">';
	$ssip_in2.= '<label>町村 番地'.I_MUST.'</label>';
	$ssip_in2.= '<input type="text" id="Aarea"  class="form-control iShip" name="o_area" value="" required>';
	$ssip_in2.= '</li>';

	$ssip_in2.= '<li class="add_in">';
	$ssip_in2.= '<label>建物名</label>';
	$ssip_in2.= '<input type="text" id="Astrt"  class="form-control iShip" name="o_strt" value="">';
	$ssip_in2.= '</li>';

	$ssip_in2.= '<li class="add_in">';
	$ssip_in2.= '<label>宛名'.I_MUST.'</label>';
	$ssip_in2.= '<input type="text" id="Aname"  class="form-control iShip" name="o_name" value="">';
	$ssip_in2.= '</li>';
	
	$ssip_in2.= '<li class="add_in">';
	$ssip_in2.= '<label>電話番号'.I_MUST.'</label>';
	$ssip_in2.= '<input type="tel" id="Aphone"  class="form-control iShip" name="o_phone" value="" pattern="[0-9-]">';
	$ssip_in2.= '</li>';
	
	$ssip_in2.= '</ul>';
	
	$ssip_in2.= '</div>';
	
	//配送先の登録
	$ssip_in = '<p class="alert">通常、荷物はどこに送りますか？</p>';
	$ssip_in.= '<button type="button" class="btn addSip"         id="aSipa" ship-data="a">登録住所に送る</button>';
	$ssip_in.= '<button type="button" class="btn addSip spc10_l" id="aSipb" ship-data="b">別の住所に送る</button>';
	$ssip_in.= '<input  type="hidden" name="ssip"   class="sSip"   id="selSip" value="">';
	$ssip_in.= '<input  type="hidden" name="noship" value="aa">';
	$ssip_in.= $ssip_in1;
	$ssip_in.= $ssip_in2;
}

?>