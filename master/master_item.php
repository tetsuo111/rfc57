<?php 
require('includes/application_top.php');

if(($_COOKIE['master_id']) && ($_COOKIE['master_permission'])){
	$master_id = $_COOKIE['master_id'];
	$head_master_name = $_COOKIE['master_name'].'様';
}else{
	//$head_master_name = 'ゲスト様';
	tep_redirect('logout.php', '', 'SSL');
}


//-------- 全体設定 --------//
//▼とび先設定
$form_action_to = basename($_SERVER['PHP_SELF']);

//★
$item_id  = $_GET['m_item_id'];
$cont_set = '?m_item_id='.$item_id;

//▼通貨設定
$CurArray = zCurrencyList();

//▼変数設定
$p_item_id       = $_POST['m_item_id'];
$p_name          = $_POST['m_item_name'];
$p_fixamount     = $_POST['m_item_fixamount'];
$p_memberamount  = $_POST['m_item_memberamount']?$_POST['m_item_memberamount']:0;
$p_specialamount = $_POST['m_item_specialamount']?$_POST['m_item_specialamount']:0;
$p_resource      = $_POST['m_item_resource']?$_POST['m_item_resource']:0;
$p_currency_id   = $_POST['m_item_currency_id'];
$p_taxtype       = $_POST['m_item_taxtype'];


//-------- データ処理 --------//
if($_POST['act'] == 'process'){
	
	//----- エラーチェック -----//
	$err = false;
	
	//★表示名
	if(!$p_item_id)         {$err = true; $err_text.= '<span class="alert">項目名を入力してください</span>';}
	if(!$p_name)            {$err = true; $err_text.= '<span class="alert">項目名を入力してください</span>';}
	if(!isset($p_fixamount)){$err = true; $err_text.= '<span class="alert">単価を入力してください</span>';}
	//if(!$p_memberamount)    {$err = true; $err_text.= '<span class="alert">会員価格を入力してください</span>';}
	//if(!$p_specialamount)   {$err = true; $err_text.= '<span class="alert">特別価格を入力してください</span>';}
	if($p_currency_id == ''){$err = true; $err_text.= '<span class="alert">計算通貨を入力してください</span>';}
//	if($p_taxtype)         {$err = true; $err_text.= '<span class="alert">課税種別を入力してください</span>';}
	
	//▼表示設定
	if(($err == false)AND(!$_POST['act_cancel'])){    //エラーなし
		
		//----- 初期設定 -----//
		//★
		$db_table = TABLE_M_ITEM;		//登録DB設定
		$t_ai_id = 'm_item_ai_id';		//自動登録ID
		$t_id    = 'm_item_id';			//テーブルID
		
		
		//▼登録チェック
		$query_check = tep_db_query("
			SELECT 
				`".$t_id."`
			FROM  `".$db_table."`
			WHERE `state` = '1'
			AND   `".$t_id."` = '".tep_db_input($item_id)."'
		");
		
		
		//----- 情報登録 -----//
		//★登録情報
		$sql_data_array = array(
			'm_item_id'            => $p_item_id,
			'm_item_name'          => $p_name,
			'm_item_fixamount'     => $p_fixamount,
			'm_item_memberamount'  => $p_memberamount,
			'm_item_specialamount' => $p_specialamount,
			'm_item_resource'      => $p_resource,
			'm_item_currency_id'   => $p_currency_id,
			'm_item_taxtype'       => $p_taxtype,
			'date_create'          => 'now()',
			'state'                => '1'
		);

		//----- DB登録 -----//
		if($_POST['act_del']){
			
			$del_array = array(
				'date_update' => 'now()',
				'state'       => 'z'
			);
			
			$w_set = "`".$t_id."`='".$item_id."' AND `state`='1'";
			tep_db_perform($db_table,$del_array,'update',$w_set);
			
			//▼終了テキスト
			$end_text = '削除しました';
		
		}else{
			
			if ($b = tep_db_fetch_array($query_check)){
				
				//更新登録
				zDBUpdate($db_table,$sql_data_array,$b[$t_id]);

			}else{
				//新規登録
				tep_db_perform($db_table,$sql_data_array);
//				zDBNewUniqueID($db_table,$sql_data_array,$t_ai_id,$t_id);
			}
			
			//▼終了テキスト
			$end_text = '登録しました';
		} 
		
		//▼終了処理
		$end = 'end';
		
	}
	
	
}else{

	//★初期設定
	$query =  tep_db_query("
		SELECT
			`m_item_id`            AS `id`,
			`m_item_name`          AS `name`,
			`m_item_fixamount`     AS `fixamount`,
			`m_item_memberamount`  AS `memberamount`,
			`m_item_specialamount` AS `specialamount`,
			`m_item_resource`      AS `resource`,
			`m_item_currency_id`   AS `currency_id`,
			`m_item_taxtype`       AS `taxtype`
		FROM  `".TABLE_M_ITEM."`
		WHERE `state` = '1'
		ORDER BY `m_item_id` ASC
	");
	
	//★
	while($a = tep_db_fetch_array($query)) {
		$operation = '<a href="'.$form_action_to.'?m_item_id='.$a['id'].'"><button type="button">編集する</button></a>';
		$i_cur = $CurArray[$a['currency_id']];
		
		$list_in.= '<tr>';
		$list_in.= '<td>'.$a['id'].'</td>';
		$list_in.= '<td>'.$a['name'].'</td>';
		$list_in.= '<td>'.number_format($a['fixamount']).$i_cur.'</td>';
		$list_in.= '<td>'.$operation.'</td>';
		$list_in.= '</tr>';
		
		if($a['id'] == $item_id){
			//▼値の取得
			$p_item_id       = $a['id'];
			$p_name          = $a['name'];
			$p_fixamount     = $a['fixamount'];
			$p_memberamount  = $a['memberamount'];
			$p_specialamount = $a['specialamount'];
			$p_resource      = $a['resource'];
			$p_currency_id   = $a['currency_id'];
			$p_taxtype       = $a['taxtype'];
		}
	}
}



//----- 表示フォーム -----//
if($end == 'end'){
	
	$input_form = '<p>'.$end_text.'</p>';
	$input_form.= '<a href="'.$form_action_to.'">登録を続ける</a>';
	
}else{

	//----- 表示リスト -----//
	$list_head = '<th>商品番号</th>';
	$list_head.= '<th>商品名</th>';
	$list_head.= '<th>単価</th>';
	$list_head.= '<th>操作</th>';

	$input_list = '<table class="input_list">'  ;
	$input_list.= '<tr>'.$list_head.'</tr>';
	$input_list.= $list_in;
	$input_list.= '</table>' ;

	
	//----- フォーム表示 -----//
	//▼自動入力要素
	$input_auto = '<input type="hidden" name="act" value="process">';
	$button_del = '<input type="submit" class="form_submit" name="act_del" value="削除">';

	//▼登録ボタン
	$input_button = '<input type="submit" class="form_submit" name="act_send" value="この内容で登録する">';
	$input_button.= ($_GET['m_item_id'])? $button_del:'';
	$input_button.= '<a class="spc10_l" href="'.$form_action_to.'">クリア</a>';

	//★入力項目
	$st   = 'style="width:70px;"';
	$req  = 'required';
	$read = ($p_item_id)? 'readonly':'required';
	$im_item_id            = '<input type="text" class="input_text ids" name="m_item_id"            '.$read.' value="'.$p_item_id.'" pattern="^([a-zA-Z0-9-_]{1,3})$">';
	$im_item_name          = '<input type="text" class="input_text"     name="m_item_name"          '.$req.'  value="'.$p_name.'">';
	$im_item_fixamount     = '<input type="text" class="input_text"     name="m_item_fixamount"     '.$req.'  value="'.$p_fixamount.'" '.$st.'>';
	$im_item_memberamount  = '<input type="text" class="input_text"     name="m_item_memberamount"  '.$req.'  value="'.$p_memberamount.'" '.$st.'>';
	$im_item_specialamount = '<input type="text" class="input_text"     name="m_item_specialamount" '.$req.'  value="'.$p_specialamount.'" '.$st.'>';
	$im_item_resource      = '<input type="text" class="input_text"     name="m_item_resource" value="'.$p_resource.'" '.$st.'>';

	//▼選択対応
	$im_item_currency_id = zRadioSet($CurArray,$p_currency_id,'m_item_currency_id',$req);		//支払通貨
	$im_item_taxtype     = zRadioSet($TaxType ,$p_taxtype,'m_item_taxtype',$req);		//消費税

	//▼表示フォーム
	$alm1 = '<span class="alert spc10_l">半角英数3文字以内</span>';
	$input_form = '<form action="'.$form_action_to.$cont_set.'" method="post">';
	$input_form.= $input_auto;
	$input_form.= '<table class="input_form">';
	$input_form.= '<tr><th>品目ID</th><td>'.$im_item_id.$alm1.'</td></tr>';
	$input_form.= '<tr><th>品目名</th><td>'.$im_item_name.'</td></tr>';
	$input_form.= '<tr><th>単価</th><td>'.$im_item_fixamount.'</td></tr>';
	//$input_form.= '<tr><th>会員単価</th><td>'.$im_item_memberamount.'</td></tr>';
	//$input_form.= '<tr><th>特別単価</th><td>'.$im_item_specialamount.'</td></tr>';
	$input_form.= '<tr><th>コミッション原資</th><td>'.$im_item_resource.'</td></tr>';
	$input_form.= '<tr><th>計算通貨</th><td>'.$im_item_currency_id.'</td></tr>';
	$input_form.= '<tr><th>課税種別</th><td>'.$im_item_taxtype.'</td></tr>';
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
		input[required]{background:#FEE;}
		input[readonly]{background:#CFCFCF;border:1px solid #C4C4C4;}
		.input_text.ids{width:70px;}
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
					
					<h2>取扱品目登録</h2>
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
