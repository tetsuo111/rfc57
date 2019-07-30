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

$m_plan_id = $_GET['m_plan_id'];
$cont_set  = '?m_plan_id='.$m_plan_id;


//▼定期注文
$sort = 'c';

//Util
require('../util/inc_order_init.php');


$err_text = '<span class="alert">これはシミュレーションです。顧客の注文画面の表示を表しています。</span>';

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
	<link rel="stylesheet" type="text/css" href="../js/jquery-ui/jquery-ui.min.css">
	<link rel="stylesheet" type="text/css" href="../css/master.css"   media="all">
	
	<script src="../js/jquery-3.2.1.min.js" charset="UTF-8"></script>
	<script src="../js/jquery-migrate-1.4.1.min.js"   charset="UTF-8"></script>
	<script src="../js/jquery-ui/jquery-ui.min.js"    charset="UTF-8"></script>
	<script type="text/javascript">
		$(function() {
			var opmonth = ["1","2","3","4","5","6","7","8","9","10","11","12"];
			var opday   = ["日","月","火","水","木","金","土"];
			var dopt ={
				dateFormat :'yy-mm-dd',
				changeMonth:true,
				monthNames:opmonth,monthNamesShort:opmonth,
				dayNames:opday,dayNamesMin:opday,dayNamesShort:opday,
				showMonthAfterYear:true
			}
			
			$('#dLimit').datepicker(dopt);
		});
	</script>
	<style>
		.list_table{width:100%;}
		#dPay{display:none;padding:5px; width:400px; background:#F6F6F6;}
		.p_area{width:600px; border:1px solid #E4E4E4; padding:10px; overflow:hidden;}
		
		.notable td{border:none; padding:2px 5px;}
		
		.paytable th,.paytable td{padding:7px 10px;}
		.paytable tr{border-bottom:1px solid #E4E4E4;}
		.sub_in{padding:5px 0;}
		
		#ps2,#ps3,#ps4{display:none;}
		.cl2{background:#F4F4F4; border-bottom:1px solid #E4E4E4;border-top:1px solid #E4E4E4; }
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
					
					<h2>定期注文確認</h2>
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
						<div class="float_clear"></div>
						
						<div class="spc50" id="ps1">
							<h3 style="font-weight:800;">注文個数を選ぶ</h3>
							<?php echo $input_form1;?>
						</div>
						
						<div class="spc50" id="ps2">
							<h3 style="font-weight:800;">支払通貨を選ぶ</h3>
							<?php echo $input_form2;?>
						</div>
						
						<div class="spc50" id="ps3">
							<h3 style="font-weight:800;">支払方法を選ぶ</h3>
							<?php echo $input_form3;?>
						</div>
						
						<div class="spc50" id="ps4">
							<h3 style="font-weight:800;">合計金額</h3>
							<?php echo $input_form4;?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="float_clear"></div>
	</div>
	
	<div id="footer">
		<?php require('inc_master_footer.php'); ?>
	</div>
</div>
<script src="../js/MyHelper.js" charset="UTF-8"></script>
<?php require('../util/inc_order_scrpt.php');?>

</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
