<?php 
require('includes/application_top.php');

if(($_COOKIE['master_id']) && ($_COOKIE['master_permission'])){
	$master_id = $_COOKIE['master_id'];
	$head_master_name = $_COOKIE['master_name'].'様';
}else{
	//$head_master_name = 'ゲスト様';
	tep_redirect('logout.php', '', 'SSL');
}


/*-------- 設定取得 --------*/
//▼初期設定
$query =  tep_db_query("
	SELECT
		`m_point_id`      AS `id`,
		`m_point_name`    AS `name`,
		`m_point_r_uni`   AS `r_uni`,
		`m_point_r_point` AS `r_point`
	FROM  `".TABLE_M_POINT."`
	WHERE `state` = '1'
	ORDER BY `m_point_id` ASC
");

while($a = tep_db_fetch_array($query)) {
	
	$ch_u = ($a['r_uni']   == 'a')? 'checked':'';
	$ch_p = ($a['r_point'] == 'a')? 'checked':'';
	$r_uni   = '<input type="checkbox" class="rUni"   value="a" data-id="'.$a['id'].'" '.$ch_u.'>';
	$r_point = '<input type="checkbox" class="rPoint" value="a" data-id="'.$a['id'].'" '.$ch_p.'>';
	
	//▼表示項目
	$list_in.= '<tr><td>'.$a['name'].'</td><td>'.$r_uni.'</td><td>'.$r_point.'</td></tr>';
}


/*----- 表示フォーム -----*/
//▼リストヘッド
$list_head = '<th>ポイント</th><th>ユニレベル<br>報酬対象</th><th>ポイント<br>報酬対象</th>';

$input_list = '<table class="input_list">'  ;
$input_list.= '<tr>'.$list_head.'</tr>';
$input_list.= $list_in;
$input_list.= '</table>';

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
		.m_area .m_input_area {width:400px;}
		.m_area .m_input_area .m_inner{width:360px;}
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
					
					<h2>報酬比率登録</h2>
					<div class="m_area">
						<div class="m_list_area">
							<?php echo $input_list;?>
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
<script src="../js/MyHelper.js"></script>
<script>
	var Cat2 = new jSendPostDataAj('xml_reward_setting_save.php');
	
	//data saving
	function jRewardSetting(rType,ID,Val){
		var sData = {top:'rewset',rtype:rType,sendid:ID,setval:Val};
		var Obj   = Cat2.sendPost(sData);
		
		if(Obj){
			Obj.done(function(response){
				if(response != 'ok'){
					alert('有効なデータではありません');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown){
				alert("データの登録に失敗しました");
			});
		}else{
			alert('データが送信できません');
		}
	};
	
	//uni level setting
	$('.rUni').on('change',function(){
		var A = $(this).attr('data-id');
		var B = $(this).prop('checked');
		var V = (B)? 'a':'b';
		jRewardSetting('uni',A,V);
	});
	
	//point level setting
	$('.rPoint').on('change',function(){
		var C = $(this).attr('data-id');
		var D = $(this).prop('checked');
		var V = (D)? 'a':'b';
		jRewardSetting('point',C,V);
	});
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
