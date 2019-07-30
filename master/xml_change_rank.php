<?php
require('includes/application_top.php');
	
//▼初期設定
$res = "err";
$top      = $_POST['top'];
$memberid = $_POST['mid'];
$bctype   = $_POST['bct'];

//▼データ取得
if(($_POST['top'] == "crank")AND($memberid)){

	//▼登録用配列
	$data_array = array(
		'bctype' => (($bctype)? $bctype:'null')
	);
	
	//▼登録DB
	$db_table = TABLE_MEM00000;
	$w_set    = "`memberid`='".$memberid."'";
	tep_db_perform($db_table ,$data_array ,'update' ,$w_set);
	
	//▼情報の登録
	$res = 'ok';

}elseif(($_POST['top'] == "cinviter")AND($memberid)){


    tep_db_query("UPDATE `position` SET position_inviter=".$bctype." WHERE memberid=".$memberid);


    $query = tep_db_query("SELECT * FROM `position` WHERE position_id=".$bctype);
    $a = tep_db_fetch_array($query);

    tep_db_query("UPDATE mem00000 SET chain=".$a['memberid']." WHERE memberid=".$memberid);


    //▼情報の登録
    $res = 'ok';

}

echo $res;
?>