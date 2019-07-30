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
$link_to = 'master_plan_setting.php';

$b_unit = zGetSysSetting('sys_base_currency_unit');


/*----- リスト取得 -----*/
//▼注文グループ
$tmp_al = range('A','Z');
foreach($tmp_al AS $v){$group_list[$v] = $v;}

//▼回数指定
$max_limit = zGetSysSetting('sys_max_order_limit') + 1;
for($i=1;$i<$max_limit;$i++){
	$limit_ar[$i] = $i;
}


//▼商品取得
$query =  tep_db_query("
	SELECT
		`m_plan_id`            AS `id`,
		`m_plan_name`          AS `name`,
		`m_plan_sum`           AS `sum`,
		`m_plan_o_group`       AS `o_group`,
		`m_plan_o_first`       AS `o_first`,
		`m_plan_o_after`       AS `o_after`,
		`m_plan_o_limit_times` AS `o_l_t`,
		`m_plan_o_limit_piece` AS `o_l_p`,
		`m_plan_o_autoship`    AS `o_auto`,
		`m_plan_o_must`        AS `o_must`,
		`m_plan_o_caution`     AS `o_caution`,
		`m_plan_condition`     AS `condition`
	FROM  `".TABLE_M_PLAN."`
	WHERE `state` = '1'
	AND  `m_plan_condition` = 'a'
	ORDER BY `m_plan_id` ASC
");

//▼配列に格納
$i = 0;

while($b = tep_db_fetch_array($query)) {
	
	if($b['o_group']){
		$order_ar[$b['o_group']][] = $b;
	}else{
		$order_ar[$i][] = $b;
	}
	
	$i++;
}

//▼表示の成形
foreach($order_ar AS $k => $dat){
	
	//▼フォームの初期設定
	$rspan = (count($dat) > 1)? 'rowspan="'.count($dat).'"':'';
	$list_in_tr = '';
	$input_auto = '';
	
	foreach($dat AS $kd => $a){
		
		/*----- 注文の設定 -----*/
		//▼初期化
		$order_part = '';
		
		//▼注文表示
		if($kd == 0){
			
			//▼値の取得
			$checked_f    = ($a['o_first'] == 'a')? 'checked' : '';
			$checked_a    = ($a['o_after'] == 'a')? 'checked' : '';
			$checked_auto = ($a['o_auto']  == 'a')? 'checked' : '';
			$checked_m    = ($a['o_must']  == 'a')? 'checked' : '';
			
			//▼内容設定
			$order_group = ($a['o_group'])? $a['o_group'] :'-';
			$in_first    = '<input type="checkbox" name="o_first"    value="a" '.$checked_f.'>';
			$in_after    = '<input type="checkbox" name="o_after"    value="a" '.$checked_a.'>';
			$limit_times = zSelectListSet($limit_ar,$a['o_l_t'],'o_limit_times', '上限なし','','');
			$limit_piece = zSelectListSet($limit_ar,$a['o_l_p'],'o_limit_piece', '上限なし','','');
			$autoship    = '<input type="checkbox" name="o_autoship" value="a" '.$checked_auto.'>';
			$order_must  = '<input type="checkbox" name="o_must"     value="a" '.$checked_m.'>';
			
			//▼状況表示
			$condition   = ($a['condition'] == 'a')? '有効':'無効';
			$cl_codition = ($a['condition'] != 'a')? 'class="no_use"':'';
			
			//▼注文ボタン
			$input_auto.= '<input type="hidden" name="top"    value="porder">';
			$input_auto.= '<input type="hidden" name="ogroup" value="'.(($a['o_group'])? $a['o_group']:'0').'">';
			
			//▼注意事項
			$caution = '<textarea name="o_caution" class="o_remarks">'.$a['o_caution'].'</textarea>';
			
			$operation   = '<button type="button" onClick="jOrderSet(\''.$k.'\');">左の内容で登録</button>';
			
			//▼表示
			$order_part = '<td '.$rspan.'>'.$order_group.'</td>';
			$order_part.= '<td '.$rspan.'>'.$in_first.'</td>';
			$order_part.= '<td '.$rspan.'>'.$in_after.'</td>';
			$order_part.= '<td '.$rspan.'>'.$limit_times.'</td>';
			$order_part.= '<td '.$rspan.'>'.$limit_piece.'</td>';
			$order_part.= '<td '.$rspan.'>'.$autoship.'</td>';
			$order_part.= '<td '.$rspan.'>'.$order_must.'</td>';
			$order_part.= '<td '.$rspan.'>'.$caution.'</td>';
			$order_part.= '<td '.$rspan.'>'.$operation.'</td>';
		}
		
		//▼登録ID
		$input_auto .= '<input type="hidden" name="sendids[]" value="'.$a['id'].'">';
		
		//▼表示設定
		$list_in_tr.= '<tr>';
		$list_in_tr.= '<td>'.$a['name'].'</td>';
		$list_in_tr.= '<td class="num_in">'.number_format($a['sum']).' '.$b_unit.'</td>';
		$list_in_tr.= '<td>'.$condition.'</td>';
		$list_in_tr.= $order_part;
		$list_in_tr.= '</tr>';
	}
	
	$list_in.= '<form id="'.$k.'" method="POST">'.$input_auto.$list_in_tr.'</form>';
}




/*----- 表示フォーム -----*/
if($end == 'end'){
	
	$input_form = '<p>登録しました</p>';
	$input_form.= '<a href="'.$form_action_to.'">商品の登録を続ける</a>';
	
}else{

	//★表示リスト
	$list_head = '<th>商品名</th>';
	$list_head.= '<th>金額</th>';
	$list_head.= '<th>状況</th>';
	$list_head.= '<th>注文グループ</th>';
	$list_head.= '<th>初回</th>';
	$list_head.= '<th>初回以降</th>';
	$list_head.= '<th>回数上限</th>';
	$list_head.= '<th>個数上限</th>';
	$list_head.= '<th>定期購入</th>';
	$list_head.= '<th>必須</th>';
	$list_head.= '<th>注意事項</th>';
	$list_head.= '<th>操作</th>';

	
	$input_list = '<table class="input_list">';
	$input_list.= '<tr>'.$list_head.'</tr>';
	$input_list.= $list_in;
	$input_list.= '</table>' ;
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
		.input_list {text-align:center;}
		.input_list td.num_in{text-align:right;}
		.input_list button{font-size:11px; padding:2px 5px;}
		.o_remarks{resize:none; height:auto;}
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
				
				<h2>注文設定</h2>
				<div class="m_area">
					<div>
						<?php echo $input_list;?>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div id="footer">
		<?php require('inc_master_footer.php'); ?>
	</div>
</div>
<script src="../js/MyHelper.js"></script>
<script>
	function jOrderSet(fID){
		
		var Cat1 = new jSendPostFormAj('xml_order_setting_save.php',fID);
		var Obj   = Cat1.sendPost();
		
		if(Obj){
			Obj.done(function(response){
				
				if(response == 'ok'){
					alert('登録しました');
					
				}else{
					alert('情報が登録できません');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown){
				alert("データの登録に失敗しました");
			});
		}else{
			alert("データが不正です");
		}
	}
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
