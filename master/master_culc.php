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

//▼ID取得
$culc_id  = $_GET['m_culc_id'];
$cont_set = '?m_culc_id='.$culc_id;


function calCheckO($id){

	if($id == 'o'){
		$data_id2 = 0;
	}else{
		$data_id2 = ($id)? $id : 'null';
	}
	
	return $data_id2;
}


/*-------- リスト取得 --------*/
//▼費用項目
if(zItemList()){
	$item_ar = zItemList() + array('o' =>'商品の合計金額','a'=>'商品の個数');
}else{
	$item_ar = array('o' =>'商品の合計金額','a'=>'商品の個数');
}



//▼変数取得

$m_culc_name         = $_POST['m_culc_name'];
$m_culc_target_id1   = $_POST['m_culc_target_id1'];
$m_culc_operator_in  = $_POST['m_culc_operator_in'];
$m_culc_target_id2   = $_POST['m_culc_target_id2'];
$m_culc_operator_out = $_POST['m_culc_operator_out'];
$m_culc_number       = $_POST['m_culc_number'];
$m_culc_treat_broken = $_POST['m_culc_treat_broken'];
$m_culc_max_value    = $_POST['m_culc_max_value'];
$m_culc_min_value    = $_POST['m_culc_min_value'];
$m_culc_range_from   = $_POST['m_culc_range_from'];
$m_culc_range_to     = $_POST['m_culc_range_to'];


/*-------- データ処理 --------*/
if($_POST['act'] == 'process'){
	
	/*----- エラーチェック -----*/
	$err = false;
	
	if(!$m_culc_name)              {$err = true; $err_text.= '<span class="alert">表示名を入力してください</span>';}
	if(!isset($m_culc_target_id1)) {$err = true; $err_text.= '<span class="alert">対象ID1を入力してください</span>';}
	if(!$m_culc_operator_out)      {$err = true; $err_text.= '<span class="alert">外部操作を入力してください</span>';}
	if(!$m_culc_number)            {$err = true; $err_text.= '<span class="alert">計算数値を入力してください</span>';}
	if(!$m_culc_treat_broken)      {$err = true; $err_text.= '<span class="alert">端数処理を入力してください</span>';}
	
	
	//▼表示設定
	if(($err == false)AND(!$_POST['act_cancel'])){    //エラーなし
		
		/*----- 初期設定 -----*/
		//▼共通設定
		$db_table = TABLE_M_CULC;			//登録DB設定
		$t_ai_id = 'm_culc_ai_id';			//自動登録ID
		$t_id    = 'm_culc_id';				//テーブルID
		
		
		//▼登録チェック
		$query_check = tep_db_query("
			SELECT 
				`".$t_id."`
			FROM  `".$db_table."`
			WHERE `state` = '1'
			AND   `".$t_id."` = '".tep_db_input($culc_id)."'
		");
		
		//▼0対策
		$data_id1 = calCheckO($m_culc_target_id1);
		$data_id2 = calCheckO($m_culc_target_id2);
		
		if(($m_culc_target_id2 != 'o')AND($m_culc_target_id2)){
			$ope_in   = ($m_culc_target_id2)? $m_culc_operator_in: 'null';
		}
		
		//★登録情報
		$sql_data_array = array(
			'm_culc_name'         => $m_culc_name,
			'm_culc_target_id1'   => $data_id1,
			'm_culc_operator_in'  => $ope_in,
			'm_culc_target_id2'   => $data_id2,
			'm_culc_operator_out' => $m_culc_operator_out,
			'm_culc_number'       => $m_culc_number,
			'm_culc_treat_broken' => $m_culc_treat_broken,
			'm_culc_max_value'    => ($m_culc_max_value)? $m_culc_max_value : 'null',
			'm_culc_min_value'    => ($m_culc_min_value)? $m_culc_min_value : 'null',
			'm_culc_range_from'   => (is_null($m_culc_range_from))? 'null':$m_culc_range_from,
			'm_culc_range_to'     => (is_null($m_culc_range_to))?   'null':$m_culc_range_to,
			'date_create' => 'now()',
			'state'       => '1'
		);
		
		
		/*----- DB登録 -----*/
		if($_POST['act_del']){
			
			$del_array = array(
				'date_update' => 'now()',
				'state'       => 'z'
			);
			
			$w_set = "`".$t_id."`='".$culc_id."' AND `state`='1'";
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
			`m_culc_id`   AS `id`,
			`m_culc_name`,
			`m_culc_target_id1`,
			`m_culc_operator_in`,
			`m_culc_target_id2`,
			`m_culc_operator_out`,
			`m_culc_number`,
			`m_culc_treat_broken`,
			`m_culc_max_value`,
			`m_culc_min_value`,
			`m_culc_range_from`,
			`m_culc_range_to`
		FROM  `".TABLE_M_CULC."`
		WHERE `state` = '1'
		ORDER BY `m_culc_id` ASC
	");
	
	//▼データ取得
	while($a = tep_db_fetch_array($query)) {
		$operation = '<a href="'.$form_action_to.'?m_culc_id='.$a['id'].'">編集する</a>';
		$list_in.= '<tr><td>'.$a['m_culc_name'].'</td><td>'.$operation.'</td></tr>';
		
		if($a['id'] == $culc_id){
			
			$m_culc_name         = $a['m_culc_name'];
			$m_culc_range_from   = $a['m_culc_range_from'];
			$m_culc_range_to     = $a['m_culc_range_to'];
			$m_culc_target_id1   = ($a['m_culc_target_id1'] == '0')? 'o':$a['m_culc_target_id1'];
			$m_culc_operator_in  = $a['m_culc_operator_in'];
			$m_culc_target_id2   = ($a['m_culc_target_id2'] == '0')? 'o':$a['m_culc_target_id2'];
			$m_culc_operator_out = $a['m_culc_operator_out'];
			$m_culc_number       = $a['m_culc_number'];
			$m_culc_treat_broken = $a['m_culc_treat_broken'];
			$m_culc_max_value    = $a['m_culc_max_value'];
			$m_culc_min_value    = $a['m_culc_min_value'];
		}
	}
}




/*----- 表示フォーム -----*/
if($end == 'end'){
	
	$input_form = '<p>'.$end_text.'</p>';
	$input_form.= '<a href="'.$form_action_to.'">計算式の登録を続ける</a>';
	
}else{
	
	/*--- 表示リスト ---*/
	$list_head = '<th>表示計算式</th><th>操作</th>';

	$input_list = '<table class="input_list">'  ;
	$input_list.= '<tr>'.$list_head.'</tr>';
	$input_list.= $list_in;
	$input_list.= '</table>' ;

	
	/*--- 登録フォーム ---*/
	//▼自動入力要素
	$input_auto = '<input type="hidden" name="act" value="process">';

	$button_del = '<input type="submit" class="form_submit" name="act_del" value="削除">';

	//▼登録ボタン
	$input_button = '<input type="submit" class="form_submit" name="act_send" value="この内容で登録する">';
	$input_button.= ($_GET['m_culc_id'])? $button_del:'';
	$input_button.= '<a class="spc10_l" href="'.$form_action_to.'">クリア</a>';

	
	//▼登録項目
	$pattern = 'pattern="[0-9\.]+"';
	$require = 'required';
	
	//▼範囲指定
	$range_in = '<input type="text" name="m_culc_range_from" class="rg" value="'.$m_culc_range_from.'" placeholder="開始値"> ～ ';
	$range_in.= '<input type="text" name="m_culc_range_to"   class="rg" value="'.$m_culc_range_to.'"   placeholder="終了値">';
	
	$range = '内部計算の結果が　';
	$range.= $range_in;
	$range.= '　の時に適用する　';
	$range.= '<p class="alert">「 開始値 ≦（内部計算）＜ 終了値 」</p>';
	
	//▼計算名
	$name = '<input type="text" name="m_culc_name" style="width:130px;" value="'.$m_culc_name.'" '.$require.'>';
	
	//▼内部計算
	$culc_in = '(　';
	$culc_in.= zSelectListSet($item_ar      ,$m_culc_target_id1  ,'m_culc_target_id1'  ,'▼対象1');
	$culc_in.= zSelectListSet($OperatorArray,$m_culc_operator_in ,'m_culc_operator_in' ,'▼');
	$culc_in.= zSelectListSet($item_ar      ,$m_culc_target_id2  ,'m_culc_target_id2'  ,'▼対象2');
	$culc_in.=  '　)　';
	
	//▼外部計算
	$opr_out  = zSelectListSet($OperatorArray,$m_culc_operator_out,'m_culc_operator_out','▼'  ,'' ,'' ,$require);
	
	$culc_out = '<input type="text" class="cnum" name="m_culc_number" value="'.$m_culc_number.'" '.$require.' place>';
	$culc_out.= '<span class="spc10_l alert">※5%は「0.05」</span>';
	
	//▼追加処理
	$brake = zSelectListSet($BrakeArray,$m_culc_treat_broken,'m_culc_treat_broken','▼端数','','',$require);		//端数処理
	$max   = '<input type="text" class="cnum" name="m_culc_max_value" value="'.$m_culc_max_value.'" '.$pattern.' placeholder="最大金額">';	//最大値
	$min   = '<input type="text" class="cnum" name="m_culc_min_value" value="'.$m_culc_min_value.'" '.$pattern.' placeholder="最小金額">';	//最小値
	$alm2  = '<p class="alert">「 最小金額 ≦　計算結果　≦ 最大金額 」</p>';
	
	//▼表示項目
	$head_in = '<tr>';
	$head_in.= '<th>表示計算式</th>';
	$head_in.= '<th>内部計算</th>';
	$head_in.= '<th>処理</th>';
	$head_in.= '<th>計算数値</th>';
	$head_in.= '<th>端数処理</th>';
	$head_in.= '<th>最大値</th>';
	$head_in.= '<th>最小値</th>';
	$head_in.= '</tr>';
	
	
	//▼表示内容
	$body_in = '<tr><th>適用範囲</th><td>'.$range.'</td></tr>';
	$body_in.= '<tr><th>表示計算式</th><td>'.$name.'</td></tr>';
	$body_in.= '<tr><th>内部計算</th><td>'.$culc_in.'</td></tr>';
	$body_in.= '<tr><th>処理</th><td>'.$opr_out.'</td></tr>';
	$body_in.= '<tr><th>計算数値</th><td>'.$culc_out.'</td></tr>';
	$body_in.= '<tr><th>端数処理</th><td>'.$brake.'</td></tr>';
	$body_in.= '<tr><th>最小・最大</th><td>'.$min.' ～ '.$max.$alm2.'</td></tr>';
	
	
	//▼表示フォーム
	$input_form = '<form action="'.$form_action_to.$cont_set.'" method="post">';
	$input_form.= $input_auto;
	$input_form.= '<table class="input_form">';
	//$input_form.= $head_in;
	$input_form.= $body_in;
	$input_form.= '</table>';
	$input_form.= '<div class="spc50">';
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
		.m_area .m_list_area_f  {width:400px;}
		.m_area .m_input_area_f {width:585px;}
		.m_area .m_input_area_f .m_inner{width:545px;}
		.cnum{width:90px;}
		.input_form td{padding:10px 5px;}
		.input_form td input{padding:5px;}
		.rg{width:90px;}
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
					
					<h2>計算式登録</h2>
					<div class="m_area">
						<div class="m_list_area_f">
							<?php echo $input_list;?>
						</div>
						<div class="m_input_area_f">
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