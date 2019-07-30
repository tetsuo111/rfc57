<?php 
require('includes/application_top.php');

if($_COOKIE['user_id']){
	$user_id        = $_COOKIE['user_id'];
	$user_email     = $_COOKIE['user_email'];
	$head_user_name = $_COOKIE['user_name'].'様';
	$position_id    = $_COOKIE['position_id'];
}else{
	//$head_user_name = 'ゲスト様';
	tep_redirect('../logout.php', '', 'SSL');
}


//▼とび先設定
$form_action_to = basename($_SERVER['PHP_SELF']);
$link_to        = 'order_cart.php';
$link_address   = 'edit_user_address.php';


$m_plan_id = $_GET['m_plan_id'];
$cont_set  = '?m_plan_id='.$m_plan_id;


//----- 商品一覧 -----//
//▼アクティブ確認
if($user_id){
	
	//▼住所確認
	$query_a =  tep_db_query("
		SELECT
			`memberid`
		FROM  `".TABLE_MEM00001."`
		WHERE `memberid` = '".tep_db_input($user_id)."'
	");

	if(!tep_db_num_rows($query_a)){
		$err_addr = true;
	}
	
}else{
	$err_pos = true;
}


/*======================================
	ユーザー情報取得
======================================*/
//▼ユーザー情報伝達
require ('inc_user_announce.php');


//----- エラーチェック -----//
if($err_pos){
	
	//▼ポジションエラー
	$order_form = '<p class="alert">ログイン情報が正しくありません</br>';
	$order_form.= '一度ログアウトし再度ログインしてください</p>';
	
}else if($err_addr){
	
	//▼アドレス
	$order_form = '<p class="alert">住所情報が登録されていません</br>';
	$order_form.= '注文の前に住所を登録してください</p>';
	$order_form.= '<a href="'.$link_address.'">住所情報を登録する</a>';
	
}else{
	

	//----- 注文確認 -----//
	//▼カート確認　初回注文があれば追加注文
	$query_a =  tep_db_query("
		SELECT
			`user_o_cart_id`
		FROM `".TABLE_USER_O_CART."`
		WHERE `state`       = '1'
		AND   `user_id` = '".tep_db_input($user_id)."'
		AND   `user_o_cart_sort` = 'a'
		AND   `user_o_cart_condition` IN ('1','a')
	");
	

	if(tep_db_num_rows($query_a)){
		$sort = 'b';
	}else{
		$sort = 'a';
	}
	
	//▼初回読込
	require('../util/inc_order_init.php');
	

	//▼商品リスト
	$odr_in1 = '<div class="form-group">';
	$odr_in1.= $input_list;
	$odr_in1.= '</div>';
	
	
	//▼商品詳細
	$odr_in2.= '<div class="spc50">';
	$odr_in2.= $err_text;
	$odr_in2.= '<h4>商品内容</h4>';
	$odr_in2.= $input_form;
	$odr_in2.= '</div>';
	
	$odr_in2.= '<div class="spc50 form-check">';
	$odr_in2.= '<h4>注文個数を選ぶ</h4>';
	$odr_in2.= $input_form1;
	$odr_in2.= '</div>';
	
	
	//▼表示設定
	if($m_plan_id){
		$order_form = $input_auto;
		$order_form.= $odr_in1;
		$order_form.= $odr_in2;
	}else{
		$order_form = $odr_in1;
	}
	
	
	//▼カート
	//カートの中身はorder_initで取得
	$show_cart = '<a href="'.$link_to.'" class="fl_r"><button type="button" class="btn">';
	$show_cart.= '<i class="fa fa-shopping-cart" aria-hidden="true" style="font-size:24px;"></i>';
	$show_cart.= '<span class="spc10_l" style="font-size:20px;">'.$ncart.'</span>';
	$show_cart.= '<span class="spc10_l">カートを見る</span>';
	$show_cart.= '</button></a>';
}


?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type"         content="text/html; charset=<?php echo CHARSET; ?>">
	<meta http-equiv="Content-Style-Type"   content="text/css">
	<meta http-equiv="Content-Script-Type"  content="text/javascript">
	<meta http-equiv="X-UA-Compatible"      content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php echo $favicon."\n"; ?>
	<title><?php echo $title;?></title>
	<meta name="description"       content="">
	<meta name="keywords"          content="">
	<meta name="robots"            content="noindex,nofollow,noarchive">
	<meta name="format-detection" content="telephone=no">
	<meta name="format-detection" content="email=no">
	<link rel="stylesheet" type="text/css" href="../css/cssreset.css" media="all">
	<link rel="stylesheet" type="text/css" href="../css/common.css"   media="all">
	<link rel="stylesheet" type="text/css" href="../js/jquery-ui/jquery-ui.min.css">
	<link rel="stylesheet" type="text/css" href="../js/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="../js/bootstrap/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="../css/my.css"       media="all">
	
	<script src="../js/jquery-3.2.1.min.js"            charset="UTF-8"></script>
	<script src="../js/jquery-migrate-1.4.1.min.js"   charset="UTF-8"></script>
	<script src="../js/bootstrap/js/bootstrap.min.js" charset="UTF-8"></script>

	<style>
		.announce_table{margin:5px 0px 5px 0px; width:600px;}
		.announce_table tr{}
		.announce_table th{background:#DFDFDF;border:1px #FFFFFF solid; padding:2px 5px 2px 5px;}
		.announce_table td{background:#FFFFFF;border:1px #DFDFDF solid; padding:2px 5px 2px 5px;}
		
		.p_area{width:100%; border:1px solid #E4E4E4; padding:10px; overflow:hidden; border-radius:10px;}
		
		.table thead th{background:#F4F4F4;}
		.table tbody tr{border-bottom:1px solid #E9E9E9;}
		.table.list_table tr{border:1px solid #E9E9E9;}
		.table.list_table tr .notable tr{ border:none;}
		
		.notable td{border:none; padding:2px 5px;}
		.notable2 {width:100%;}
		.notable2 tr{border:none;}
	</style>
</head>
<body>
<div id="wrapper">
	
	<div id="header">
		<?php require('inc_user_header.php');?>
	</div>
	
	<div class="container-fluid">
		<div id="content" class="row">
			
			<div id="left1" class="col-md-4 col-lg-2">
				<div class="inner">
					<div class="u_menu_area">
						<?php require('inc_user_left.php'); ?>
					</div>
				</div>
			</div>
		
		
			<div id="left2" class="col-xs-12 col-md-12 col-md-8 col-lg-10">
				<div class="inner">
					
					<div>
						<?php echo $my_nav;?>
					</div>

					<h2 style="overflow:hidden;">Order <?php echo $show_cart;?></h2>
					<div>
						<?php echo $order_form;?>
					</div>
				</div>
			</div>
		</div>
	</div>
		
	<div id="footer">
		<?php require('inc_user_footer.php');?>
	</div>
</div>
<script src="../js/MyHelper.js" charset="UTF-8"></script>
<script>
	
	oN  = 0;
	$('#oNum').on('change',function(){
		oN  = $(this).val();
		dis = (oN)? false:true;
		$('#inCart').prop('disabled',dis);
	});
	
	var Cat = new jSendPostDataAj('xml_order_cart.php');
	$('#inCart').on('click',function(){
		var sData = {
				top   : 'cart',
				planid:'<?php echo $m_plan_id;?>',
				oNum  :oN,
				oSort :'<?php echo $sort;?>'
			};
		var Obj = Cat.sendPost(sData);
		
		Obj.done(function(response){
			
			res = response.trim();
			
			if(res == 'ok'){
				alert('カートに入れました');
				location.href='<?php echo $form_action_to;?>';
			}else{
				alert('データの設定に不備があります');
			}
		})
		.fail(function(){alert('データの確認ができません');});
	});
</script>

</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
