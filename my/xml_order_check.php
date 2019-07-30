<?php
require('includes/application_top.php');


//▼初期設定
$p_num     = $_POST['num'];				//注文個数
$p_limit   = $_POST['order_limit'];		//入金期限

$p_sort    = $_POST['sort'];			//定期確認
$p_deliver = $_POST['deliver'];			//選択コース


//▼支払設定
$p_payment     = $_POST['odr_payment'];		//支払データ
$odr_charge_ar = zJSToArry(str_replace('\\','' ,$p_payment));	//支払設定

//▼送付先住所
$p_ssip  = $_POST['ssip'];		//送り先設定
$p_zipa  = $_POST['o_zip_a'];	//郵便番号a
$p_zipb  = $_POST['o_zip_b'];	//郵便番号b
$p_pref  = $_POST['o_pref'];		//都道府県
$p_city  = $_POST['o_city'];		//市区
$p_area  = $_POST['o_area'];		//町村番地
$p_name  = $_POST['o_name'];		//宛名
$p_phone = $_POST['o_phone'];		//電話番号


/*----- エラーチェック -----*/
//▼注文個数
if(!$p_num)    {$err = true;}

//▼支払方法
if(!$p_payment){
	$err = true;
	
}else{
	foreach($odr_charge_ar AS $pd){
		
		if((isset($pd['curid']))AND(isset($pd['payid']))){
			//設定されていれば何もしない
		}else{
			$err = true;
			$err_ar[] = 'pay';
		}
	}
}

//▼入金期限
if(!$p_limit)  {
	$err = true;
}else if($p_limit < date("Y-m-d")){
	$err      = true;
	$err_ar[] = 'limit2';
}

//▼住所確認
if(!$p_ssip){$err = true;}
if(($p_ssip == 'b')AND($_POST['noship'])){
	if(!$p_zipa) {$err = true;}
	if(!$p_zipb) {$err = true;}
	if(!$p_pref) {$err = true;}
	if(!$p_city) {$err = true;}
	if(!$p_area) {$err = true;}
	if(!$p_name) {$err = true;}
	if(!$p_phone){$err = true;}
	$err_ar[] = 'sip';
}

//▼定期確認
if($p_sort == 'c'){
	if(!$p_deliver){$err = true;}
}

//▼エラー判定
$res = ($err == false)? 'ok':"nodata";

//$string = json_encode($_POST);
//write_log($string,'w');

$ar['status'] = $res;
$ar['err']    = $err_ar;
echo json_encode($ar);
?>