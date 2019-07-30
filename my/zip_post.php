<?php
require('includes/application_top.php');

//▼データの読み込み
$top = $_POST['top'];

//▼初期設定
$res = "nodata";

//▼データ取得
if(($top == "ziproma")AND(!empty($_POST['zip']))){

	//データ取得
	$zip_cord = $_POST['zip'];
	$zipdata  = file_get_contents("http://address.sijisuru.com/Yubin/disp?post=".$zip_cord);
	$zipdata = str_replace(array(' ',"\r","\n","\t"),'',$zipdata);

	//前半削除
	$str     = '<h2>住所</h2></div><divstyle="line-height:2em;"class=\'row\'>';
	$len     = mb_strlen($str);
	$pos     = mb_strpos($zipdata,$str);
	$zipdata = mb_substr($zipdata,$pos+$len);

	//後半削除
	$str     = '<h2>';
	$pos     = mb_strpos($zipdata,$str);
	$zipdata = mb_substr($zipdata,0,$pos);

	//カナ別に振り分け
	$zipdata = strip_tags(str_replace('</div>','</div>-',$zipdata));
	$dt_ar   = explode('--',trim($zipdata,'--'));

	$zip1    = explode("-", $dt_ar[0]);		//漢字
	$zip2    = explode("-", $dt_ar[1]);		//カナ
	$zip3    = explode("-", $dt_ar[2]);		//ローマ


	/*===========================
	■ローマ字市区町村対策
	===========================*/
	//都道府県用
	$search_pref = array(' ','-to','-TO','-fu','-FU','-KEN','-ken');
	$replac_pref = array('-',''   ,''   ,''   ,''   ,''    ,'');

	$zip3[0] = str_replace($search_pref,$replac_pref,strtolower($zip3[0]));
	$zip3[0] = ucfirst($zip3[0]);

	//市区町村用
	$search_city = array(' ','-ku-','-KU-','-shi-','-SHI-');
	$replac_city = array('-','-ku@','-KU@','-shi@','-SHI@','');

	//市区判定用
	$city   = '';
	$city_t = '';
	$city_t = str_replace($search_city,$replac_city,strtolower($zip3[1]));
	$ar = explode('@',$city_t);

	foreach($ar AS $c){
		$city.= ucfirst($c).',';
	}
	$zip3[1] = $city;


	//データ成形
	$zip_array = array(
		"Char" => array($zip1[0], $zip1[1], $zip1[2]),
		"Kana" => array($zip2[0], $zip2[1], $zip2[2]),
		"Roma" => array($zip3[0], $zip3[1],ucfirst(strtolower($zip3[2])))
	);

	if(!empty($zip_array)){
		$res = 'ok';
	}
}


//▼出力設定
$array["result"] = $res;
$array["ZipData"] = $zip_array;

//▼JSON形式に変換して出力
echo json_encode($array);

require(DIR_WS_INCLUDES . 'application_bottom.php');
?>