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
for($i=1;$i<16;$i++){
	$limit_ar[$i] = $i;
}


//▼商品取得
$query =  tep_db_query("
	SELECT
		`m_plan_id`        AS `id`,
		`m_plan_name`      AS `name`,
		`m_plan_sum`       AS `sum`,
		`m_plan_o_group`   AS `o_group`,
		`m_plan_condition` AS `condition`
	FROM  `".TABLE_M_PLAN."`
	WHERE `state` = '1'
	ORDER BY `m_plan_id` ASC
");

//★
while($a = tep_db_fetch_array($query)) {
	
	//▼内容取得
	$order_group = zSelectListSet($group_list,$a['o_group'],'order_group','グループなし',$a['id'],'jGetGroup('.$a['id'].')');
	$condition   = ($a['condition'] == 'a')? '有効':'無効';
	$cl_codition = ($a['condition'] != 'a')? 'class="no_use"':'';
	
	//▼表示設定
	$list_in.= '<tr><form>';
	$list_in.= '<td>'.$a['name'].'</td>';
	$list_in.= '<td class="num_in">'.number_format($a['sum']).' '.$b_unit.'</td>';
	$list_in.= '<td>'.$condition.'</td>';
	$list_in.= '<td>'.$order_group.'</td>';
	$list_in.= '</form></tr>';
	
	
	if($a['id'] == $rank_id){
		$m_plan_name = $a['name'];
	}
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
	var Cat1 = new jSendPostDataAj('xml_order_save.php');
	
	function jGetGroup(ID){
		var aa   = $('#'+ID).val();
		var Data = {top:'porder',sendid:ID,ogroup:((!aa)? 0:aa)};
		var Obj   = Cat1.sendPost(Data);

		if(Obj){
			
			Obj.done(function(response){
				if(response != 'ok'){
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
