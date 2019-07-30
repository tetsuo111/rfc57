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

//▼コミッション段数
$max_level = zGetSysSetting('sys_max_commission_level');


/*-------- リスト設定 --------*/
//▼ランクリスト
$rank_ar = zRankList();

//▼コミッションリスト
$uni_ar  = zRankUniRateList();


//▼ポイント設定
$query =  tep_db_query("
	SELECT
		`m_point_id`      AS `id`,
		`m_point_name`    AS `name`
	FROM  `".TABLE_M_POINT."`
	WHERE `state` = '1'
	AND   `m_point_r_uni` = 'a'
	ORDER BY `m_point_id` ASC
");

while($a = tep_db_fetch_array($query)) {
	$point_ar[$a['id']] = $a['name'];
}


/*-------- データ処理 --------*/
//▼値の取得
$rank_id  = $_GET['m_rank_id'];
$cont_set = '?m_rank_id='.$rank_id;
$dis_r    = ($_GET['m_rank_id'])? '':'disabled';
$r_rate   = $_POST['r_rate'];


if($_POST['act'] == 'process'){
	
	/*----- エラーチェック -----*/
	$err = false;
	
	//★表示名
	if(!$r_rate){$err = true; $err_text.= '<span class="input_alert">報酬の設定がありません</span>';}
	
	
	//▼表示設定
	if(($err == false)OR(!$_POST['act_cancel'])){    //エラーなし
		
		/*----- 初期設定 -----*/
		//★
		$db_table = TABLE_M_RANK;			//登録DB設定
		$t_ai_id = 'm_rank_ai_id';			//自動登録ID
		$t_id    = 'm_rank_id';				//テーブルID
		
		
		//▼登録チェック
		$query_check = tep_db_query("
			SELECT 
				`".$t_id."`
			FROM  `".$db_table."`
			WHERE `state` = '1'
			AND   `".$t_id."` = '".tep_db_input($rank_id)."'
		");
		
		//▼有効な値のみを格納
		foreach($r_rate AS $kl => $t_data){
			foreach($t_data AS $kp => $vrate){
				if($vrate > 0){
					$tmp[$kl][$kp] = $vrate;
				}
			}
		}
		
		//▼配列を変換
		$r_uni = zToJSText($tmp);
		
		//★登録情報
		$sql_data_array = array(
			'm_rank_r_uni' => $r_uni,
			'date_update'  => 'now()'
		);
		
		
		/*----- DB登録 -----*/
		if($_POST['act_del']){
			
			//▼削除用配列
			$del_array = array(
				'm_rank_r_uni'=> 'null',
				'date_update' => 'now()'
			);
			
			$w_set = "`".$t_id."`='".$rank_id."' AND `state`='1'";
			tep_db_perform($db_table,$del_array,'update',$w_set);
			
			//▼終了テキスト
			$end_text = '削除しました';
		
		}else{
			
			//▼有効なデータがあれば
			if ($b = tep_db_fetch_array($query_check)){
				//更新登録
				zDBUpdate($db_table,$sql_data_array,$b[$t_id]);
			}
			
			//▼終了テキスト
			$end_text = '登録しました';
		} 
		
		//▼終了処理
		$end = 'end';
	}
}


/*----- 表示フォーム -----*/
if($end == 'end'){
	
	$input_form = '<p>'.$end_text.'</p>';
	$input_form.= '<a href="'.$form_action_to.'">報酬の登録を続ける</a>';
	
}else{
	
	/*--- ランク一覧---*/
	//▼項目名
	$list_head = '<th>ランク名</th><th>状況</th><th>操作</th>';
	
	//▼表示内容
	foreach($rank_ar AS $kr => $vrank){
		$operation = '<a href="'.$form_action_to.'?m_rank_id='.$kr.'">このランクの報酬を設定する</a>';
		$cl_sel    = ($kr == $rank_id)? 'class="sel"':'';
		$cond      = ($uni_ar[$kr])? '設定済':'<span class="alert">未設定</span>';
		$list_in.= '<tr '.$cl_sel.'><td>'.$vrank.'</td><td>'.$cond.'</td><td>'.$operation.'</td></tr>';
	}
	
	//▼表示フォーム
	$input_list = '<table class="input_list">'  ;
	$input_list.= '<tr>'.$list_head.'</tr>';
	$input_list.= $list_in;
	$input_list.= '</table>' ;


	/*--- 表示フォーム ---*/
	//▼自動入力要素
	$input_auto = '<input type="hidden" name="act" value="process">';

	$button_del = '<input type="submit" class="form_submit" name="act_del" value="削除">';

	//▼登録ボタン
	$input_button = '<input type="submit" class="form_submit" name="act_send" value="この内容で登録する" id="Act" disabled>';
	$input_button.= ($_GET['m_rank_id'])? $button_del:'';
	$input_button.= '<a class="spc10_l" href="'.$form_action_to.'">クリア</a>';
	
	
	//▼表示中のランク
	$select_rank = ($rank_id)? $rank_ar[$rank_id]:'-';
	
	//▼項目名
	foreach($point_ar AS $kp => $npoint){
		$table_head.= '<th>'.$npoint.'</th>';
	}
	
	
	//▼報酬設定
	$reward_ar = ($uni_ar[$rank_id])? zJSToArry($uni_ar[$rank_id]):'';
	
	//▼表示内容
	for($i=0;$i<$max_level;$i++){
		//▼初期化
		$form_in = '';
		$lev = $i + 1;
		
		//▼ポイントごとに表示
		foreach($point_ar AS $kp => $vt){
			$rank_rate = ($reward_ar)? $reward_ar[$lev][$kp]:'';
			$in = '<input type="text" class="reward" name="r_rate['.$lev.']['.$kp.']" value="'.$rank_rate.'" pattern="[0-9\.]+" '.$dis_r.'>';
			$form_in.= '<td>'.$in.' %</td>';
		}
		
		$form_in_tr.= '<tr><td>'.$lev.'段目</td>'.$form_in.'</tr>';
	}
	
	//▼表示フォーム
	$input_form = '<form action="'.$form_action_to.$cont_set.'" method="post" id="RewordForm">';
	$input_form.= $input_auto;
	$input_form.= '<h4>選択中のランク：'.$select_rank.'</h4>';
	$input_form.= '<table class="input_form">';
	$input_form.= '<tr><th>段数</th>'.$table_head.'</tr>';
	$input_form.= $form_in_tr;
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
	.reward{width:40px;}
	.sel{background:#99F;}
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
					
					<h2>ランク登録</h2>
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
	<script>
		var Flag = '<?php echo $rank_id;?>';
		
		if(Flag){
			$('#RewordForm').on('change',function(){
				var D = $('.reward');
				var A = 0;
				
				for(var i=0;i < D.length;i++){
					if(D[i].value){
						A = A + (D[i].value * 1);
					}
				}
				
				var dis = (A > 0)? false : true;
				$('#Act').prop('disabled',dis);
			});
		}
	</script>
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
