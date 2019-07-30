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
$form_action_to = basename($_SERVER['PHP_SELF']);

//▼ID設定
$cost_id  = $_GET['m_cost_id'];
$cont_set = '?m_cost_id='.$cost_id;

$cur_unit = zGetSysSetting('sys_base_currency_unit');
$culcList = zCulcList();		//計算式一覧


//★変数設定
$p_name     = $_POST['m_cost_name'];
$p_culc_id  = $_POST['m_cost_culc_id'];
$p_taxtype  = $_POST['m_cost_taxtype'];


/*-------- データ処理 --------*/
if($_POST['act'] == 'process'){
	
	/*----- エラーチェック -----*/
	$err = false;
	
	if(!$p_name)   {$err = true; $err_text.= '<p class="alert">項目名を入力してください</p>';}
	if(!$p_culc_id){$err = true; $err_text.= '<p class="alert">金額を入力してください</p>';}
	if($p_taxtype == ''){$err = true; $err_text.= '<p class="alert">課税種別を選択してください</p>';}
	
	
	//▼表示設定
	if(($err == false)AND(!$_POST['act_cancel'])){    //エラーなし
		
		//----- 初期設定 -----//
		$db_table = TABLE_M_COST;			//登録DB設定
		$t_ai_id = 'm_cost_ai_id';			//自動登録ID
		$t_id    = 'm_cost_id';				//テーブルID
		
		
		//▼登録チェック
		$query_check = tep_db_query("
			SELECT 
				`".$t_id."`
			FROM  `".$db_table."`
			WHERE `state` = '1'
			AND   `".$t_id."` = '".tep_db_input($cost_id)."'
		");
		
		
		//----- 情報登録 -----//
		//▼登録情報
		$sql_data_array = array(
			'm_cost_name'    => $p_name,
			'm_cost_culc_id'  => $p_culc_id,
			'm_cost_taxtype' => $p_taxtype,
			'date_create'    => 'now()',
			'state'          => '1'
		);
		
		
		/*----- DB登録 -----*/
		if($_POST['act_del']){
			
			$del_array = array(
				'date_update' => 'now()',
				'state'       => 'z'
			);
			
			$w_set = "`".$t_id."`='".$cost_id."' AND `state`='1'";
			tep_db_perform($db_table,$del_array,'update',$w_set);
			
			//▼終了テキスト
			$end_text = '削除しました';
		
		}else{
			
			if ($b = tep_db_fetch_array($query_check)){
				//更新登録
				zDBUpdate($db_table,$sql_data_array,$b[$t_id]);

			}else{
				//新規登録
				zDBNewUniqueID($db_table,$sql_data_array,$t_ai_id,$t_id);
			}
			
			//▼終了テキスト
			$end_text = '登録しました';
		} 
		
		//▼終了処理
		$end = 'end';
		
	}
	
	
}else{

	//▼初期設定
	$query =  tep_db_query("
		SELECT
			`m_cost_id`        AS `id`,
			`m_cost_name`      AS `name`,
			`m_cost_culc_id`   AS `culc_id`,
			`m_cost_taxtype`   AS `taxtype`,
			`m_cost_condition` AS `condition`
		FROM  `".TABLE_M_COST."`
		WHERE `state` = '1'
		ORDER BY `m_cost_id` ASC
	");
	
	while($a = tep_db_fetch_array($query)) {
		$operation = '<a href="'.$form_action_to.'?m_cost_id='.$a['id'].'"><button type="button">編集する</button></a>';
		
		$list_in.= '<tr>';
		$list_in.= '<td>'.$a['id'].'</td>';
		$list_in.= '<td>'.$a['name'].'</td>';
		$list_in.= '<td>'.$culcList[$a['culc_id']].'</d>';
		$list_in.= '<td>'.$operation.'</td>';
		$list_in.= '</tr>';
		
		if($a['id'] == $cost_id){
			//▼値の取得
			$p_id        = $a['id'];
			$p_name      = $a['name'];
			$p_culc_id   = $a['culc_id'];
			$p_taxtype   = $a['taxtype'];
			$p_condition = $a['condition'];
		}
	}
}



/*----- 表示フォーム -----*/
if($end == 'end'){
	
	$input_form = '<p>'.$end_text.'</p>';
	$input_form.= '<a href="'.$form_action_to.'">費用項目の登録を続ける</a>';
	
}else{

	//----- 表示リスト -----//
	$list_head = '<th>番号</th>';
	$list_head.= '<th>費用項目名</th>';
	$list_head.= '<th>金額</th>';
	$list_head.= '<th>操作</th>';
	
	$input_list = '<table class="input_list">'  ;
	$input_list.= '<tr>'.$list_head.'</tr>';
	$input_list.= $list_in;
	$input_list.= '</table>' ;


	//----- 登録フォーム -----//
	//▼自動入力要素
	$input_auto = '<input type="hidden" name="act" value="process">';

	$button_del = '<input type="submit" class="form_submit" name="act_del" value="削除">';

	//▼登録ボタン
	$input_button = '<input type="submit" class="form_submit" name="act_send" value="この内容で登録する">';
	$input_button.= ($_GET['m_cost_id'])? $button_del:'';
	$input_button.= '<a class="spc10_l" href="'.$form_action_to.'">クリア</a>';

	//▼入力項目
	$req = 'required';
	$im_cost_name    = '<input type="text" class="input_text" name="m_cost_name"   value="'.$p_name.'"   '.$req.'>';
	$im_cost_culc_id = zSelectListSet($culcList,$p_culc_id,'m_cost_culc_id','▼計算方法を選択','','',$req);

	//▼消費税対応
	$im_cost_taxtype = '<div>'. zRadioSet($TaxType,$p_taxtype,'m_cost_taxtype',$req).'</div>';
	
	//▼必須
	$must = I_MUST;
	
	//▼表示フォーム
	$input_form = '<form action="'.$form_action_to.$cont_set.'" method="post">';
	$input_form.= $input_auto;
	$input_form.= '<table class="input_form">';
	$input_form.= '<tr><th>項目名'.$must.'</th><td>'.$im_cost_name.'</td></tr>';
	$input_form.= '<tr><th>金額'.$must.'</th><td>'.$im_cost_culc_id.'</td></tr>';
	$input_form.= '<tr><th>課税種別'.$must.'</th><td>'.$im_cost_taxtype.'</td></tr>';
	$input_form.= '</table>';
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
		.m_area .m_list_area {width:600px;}
		.input_list{width:100%;}
		.m_area .m_input_area{width:450px;}
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
					
					<h2>手数料登録</h2>
					<div class="m_area">
						<div class="m_list_area">
							<?php echo $input_list;?>
						</div>
						<div class="m_input_area">
							<div class="m_inner">
								<?php echo $err_text;?>
								<?php echo $input_form;?>
							</div>
						</div>
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
