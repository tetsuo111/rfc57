<?php 
require('includes/application_top.php');

if(($_COOKIE['master_id']) && ($_COOKIE['master_permission'])){
	$master_id = $_COOKIE['master_id'];
	$head_master_name = $_COOKIE['master_name'].'様';
}else{
	//$head_master_name = 'ゲスト様';
	tep_redirect('logout.php', '', 'SSL');
}


/*-------- 全体設定 --------*/
//▼とび先設定
$form_action_to     = basename($_SERVER['PHP_SELF']);
$form_action_return = 'master_plan_pay_setting_list.php';

//★
$plan_id  = $_GET['m_plan_id'];
$cont_set = '?m_plan_id='.$plan_id;

$disabled = ($_GET['m_plan_id'])? '':'disabled';

//▼基準通貨の設定
$base    = zGetSysSetting('sys_base_currency');
$base_cr = zGetSysSetting('sys_base_currency_unit');



/*-------- リスト取得 --------*/
//▼費用項目
$query_item = tep_db_query("
	SELECT
		`m_item_id`        AS `id`,
		`m_item_name`      AS `name`,
		`m_item_detail`    AS `i_detail`,
		CAST(`m_item_culc_id` AS CHAR)   AS `i_cid`,
		`m_item_culc_unit` AS `unit`
	FROM  `".TABLE_M_ITEM."`
	WHERE `state` = '1'
	ORDER BY `m_item_id` ASC
");

while($b = tep_db_fetch_array($query_item)) {
	//▼計算単位別に振分
	if($b['unit'] == 'a'){
		$item_ar[$b['id']] = $b;
	}else if($b['unit'] == 'b'){
		$pay_ar[$b['id']] = $b;
	}
}


//▼費用詳細
$query_detail = tep_db_query("
	SELECT
		`m_detail_id`       AS `id`,
		`m_detail_name`     AS `name`,
		`m_detail_sys_name` AS `sys_name`
	FROM  `".TABLE_M_DETAIL."`
	WHERE `state` = '1'
	ORDER BY `m_detail_id` ASC
");

while($d = tep_db_fetch_array($query_detail)) {
	$detail_ar[$d['sys_name']] = $d;
}



//▼通常リスト
$point_ar = zPointList();	//ポイント
$rank_ar  = zRankList();	//ランク



/*-------- データ処理 --------*/
//▼変数取得
$payid = $_POST['itemid'];


if($_POST['act'] == 'process'){
	
	/*----- エラーチェック -----*/
	$err = false;
	
	//▼表示設定
	if(($err == false)OR(!$_POST['act_cancel'])){    //エラーなし
		
		/*----- 初期設定 -----*/
		//★
		$db_table = TABLE_M_PLAN;			//登録DB設定
		$t_ai_id  = 'm_plan_ai_id';			//自動登録ID
		$t_id     = 'm_plan_id';				//テーブルID
		
		
		//▼登録チェック
		$query_check = tep_db_query("
			SELECT 
				`".$t_id."`
			FROM  `".$db_table."`
			WHERE `state` = '1'
			AND   `".$t_id."` = '".tep_db_input($plan_id)."'
		");
		
		
		/*----- 登録情報 -----*/
		//▼値の設定
		$js_item_pay = ($payid)?  zToJSText($payid) : 'null';		//ポイント項目
		$sql_data_array = array(
			'm_plan_item_pay' => $js_item_pay,
			'date_update'     => 'now()'
		);
		
		
		/*----- DB登録 -----*/
		if ($b = tep_db_fetch_array($query_check)){
			//更新登録
			zDBUpdate($db_table,$sql_data_array,$b[$t_id]);
		}
		
		//▼終了テキスト
		$end = 'end';
		
	}
	
	
}else{

	//▼商品一覧
	$query =  tep_db_query("
		SELECT
			`m_plan_id`         AS `id`,
			`m_plan_rank_id`    AS `rank_id`,
			`m_plan_sum`        AS `sum`,
			`m_plan_name`       AS `name`,
			`m_plan_item`       AS `item`,
			`m_plan_item_pay`   AS `i_pay`,
			`m_plan_detail`     AS `detail`,
			`m_plan_point`      AS `point`,
			`m_plan_condition`  AS `condition`
		FROM  `".TABLE_M_PLAN."`
		WHERE `state` = '1'
		AND  `m_plan_id` = '".tep_db_input($plan_id)."'
		ORDER BY `m_plan_id` ASC
	");
	
	//▼
	if($a = tep_db_fetch_array($query)) {
		$m_plan_name  = $a['name'];
		$sum          = $a['sum'];
		$condition    = $a['condition'];
		$rank_name    = ($a['rank_id'])? $rank_ar[$a['rank_id']]:'全対象';
		$d_item_ar    = ($a['item'])?   zJSToArry($a['item'])   :'';		//費用条件
		$d_item_payar = ($a['i_pay'])?  zJSToArry($a['i_pay'])  :'';		//合計費用条件
		$d_detail_ar  = ($a['detail'])? zJSToArry($a['detail']) :'';		//費用費用詳細
		$d_point_ar   = ($a['point'])?  zJSToArry($a['point'])  :'';		//ポイント条件
	}
}



/*-------- フォーム表示 --------*/
//▼自動入力要素
$input_auto = '<input type="hidden" name="act" value="process">';

//▼登録ボタン
$input_button = '<input type="submit" class="form_submit" name="act_send" value="この内容で登録する" '.$disabled.'>';
$input_button.= '<a class="spc10_l"   href="'.$form_action_to.'">クリア</a>';


/*----- 表示フォーム -----*/
if($end == 'end'){
	
	$input_form = '<p>登録しました</p>';
	$input_form.= '<a href="'.$form_action_return.'">商品の選択に戻る</a>';
	
}else{
	
	//▼入力制限
	$input_control = 'required pattern="^[0-9\.]+$"';
	
	/*--- 基本設定 ---*/
	//▼選択状況
	if($condition == 'a'){
		$p_use = '有効';
		
	}else if($condition == 'b'){
		$p_use = '無効';
	}
	
	
	//▼基本設定
	$form_b_in = '<tr><th>商品名</th><td>'.$m_plan_name.'</td></tr>';
	$form_b_in.= '<tr><th>対象ランク</th><td>'.$rank_name.'</td></tr>';
	$form_b_in.= '<tr><th>有効無効</th><td>'.$p_use.'</td></tr>';
	
	
	/*--- 費用設定 ---*/
	//$item_ar　＞費用項目
	//$detail_ar＞社内内訳
	//$d_item_ar＞設定した費用項目
	//▼単体費用
	$form_in     = mSetPlanSetting($item_ar,$detail_ar,$plan_id,$d_item_ar,$d_detail_ar,$input_control,'text');
	
	//▼合計費用
	$form_pay_in = mSetPlanSetting($pay_ar ,$detail_ar,$plan_id,$d_item_ar,$d_detail_ar,$input_control,'',$d_item_payar);
	
	/*--- ポイント設定 ---*/
	//▼ポイント内容
	foreach($point_ar AS $kp => $vp){
		//各値の設定
		if($plan_id){$v_point = ($d_point_ar[$kp])? $d_point_ar[$kp] : '0';}
		
		$inp       = '<p class="num_in">'.zCheckNum($v_point).'</p>';
		$form_p_in.= '<tr><th>'.$vp.'</th><td>'.$inp.'</td></tr>';
	}
	
	
	/*--- 表示フォーム ---*/
	//▼基本
	$input_form = '<form action="'.$form_action_to.$cont_set.'" method="post">';
	$input_form.= $input_auto;
	$input_form.= '<h3>基本設定</h3>';
	$input_form.= '<table class="input_form">';
	$input_form.= $form_b_in;
	$input_form.= '</table>';
	
	//▼費用単価
	$input_form.= '<div class="spc20">';
	$input_form.= '<h3>単体費用</h3>';
	$input_form.= '<table class="input_form">';
	$input_form.= '<tr><th>費用名</th><th>合計金額　['.$base_cr.']</th><th colspan="2">費用詳細　['.$base_cr.']</th></tr>';
	$input_form.= $form_in;
	$input_form.= '<tr><th>合計</th><td class="num_in">'.$base.number_format($sum).'</td></tr>';
	$input_form.= '</table>';
	$input_form.= '</div>';
	
	//▼会計時費用
	$input_form.= '<div class="spc20">';
	$input_form.= '<h3>合計費用</h3>';
	$input_form.= '<table class="input_form">';
	$input_form.= '<tr><th>費用名</th><th>合計金額　['.$base_cr.']</th><th colspan="2">費用詳細　['.$base_cr.']</th></tr>';
	$input_form.= $form_pay_in;
	$input_form.= '</table>';
	$input_form.= '</div>';
	
	//▼ポイント
	$input_form.= '<div class="spc20">';
	$input_form.= '<h3>ポイント設定</h3>';
	$input_form.= '<table class="input_form">';
	$input_form.= $form_p_in;
	$input_form.= '</table>';
	$input_form.= '</div>';
	
	$input_form.= '<div class="spc20">';
	$input_form.= $input_button;
	$input_form.= '</div>';
	$input_form.= '</form>';
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<?php echo $favicon."\n"; ?>
	<title><?php echo $title;?></title>
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="robots" content="noindex,nofollow,noarchive">
	<meta name="format-detection" content="telephone=no">
	<meta name="format-detection" content="email=no">
	<link rel="stylesheet" type="text/css" href="../css/cssreset.css" media="all">
	<link rel="stylesheet" type="text/css" href="../css/common.css"   media="all">
	<link rel="stylesheet" type="text/css" href="../css/master.css"   media="all">
	<script src="../js/jquery-3.2.1.min.js" charset="UTF-8"></script>
	<style>
		.input_text{width:100%;}
		.input_form input{text-align:right;}
		.input_form th{width:140px;}
		.m_input_area h3{font-weight:800;}
		
		.num_in{text-align:right;}
		
		.dt_in li{margin:3px 0;}
		.dt_in input{width:100px; padding:0 5px;}
		.dt_l{border-right:1px solid #FFF;}
		.dt_r{border-left:1px solid #FFF;}
		
		#left2 .inner{padding-bottom:100px;}
	</style>
</head>
<body id="body">
<div id="wrapper">
	
	<div id="header">
		<?php require('inc_master_header.php');?>
	</div>
	<div id="head_line">
		<?php require('inc_master_head_line.php');?>
	</div>
	
	<div id="content">
		<div class="content_outer">
			<div id="left1">
				<div class="inner">
					<?php require('inc_master_left.php'); ?>
				</div>
			</div>
		
			<div id="left2">
				<div class="inner">
				
					<div class="admin_menu">
						<?php require('inc_master_menu.php');?>
					</div>
					
					<h2>商品設定</h2>
					<div class="m_input_area spc20">
						<?php echo $err_text;?>
						<?php echo $input_form;?>
					</div>
				</div>
			</div>
			
			<div class="float_clear"></div>
		</div>
	</div>
	
	<div id="footer">
		<?php require('inc_master_footer.php'); ?>
	</div>
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
