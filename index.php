<?php 
require('includes/application_top.php');

//backup
//<div class="spc20"><a href="new_regisitration.php"><img src="front/img/sign_up.png"></a></div>
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php echo $favicon."\n"; ?>
	<title><?php echo $title;?></title>
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="format-detection" content="telephone=no">
	<meta name="format-detection" content="email=no">
	<meta name="google" value="notranslate">
	<link rel="stylesheet" type="text/css" href="css/cssreset.css" media="all">
	<link rel="stylesheet" type="text/css" href="css/common.css"   media="all">
	<link rel="stylesheet" href="js/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="js/bootstrap/css/font-awesome.min.css" />
	
	<link rel="stylesheet" type="text/css" href="front/css/top.css"      media="all">
	<script src="../js/jquery-3.2.1.min.js" charset="UTF-8"></script>
	<script src="../js/bootstrap/js/bootstrap.min.js" charset="UTF-8"></script>
	<script src="../js/jquery-migrate-1.4.1.min.js" charset="UTF-8"></script>
	
	<style>
		#wrapper{
			background:url("front/img/top_01.jpg") no-repeat;
			background-size:cover;
			background-position:top center;
		}

		@media screen and (max-width:544px) { 
			#wrapper{
				background-size:contain;
			}
		}
		
	</style>
</head>
<body>
	<div id="wrapper">

		<div id="header"></div>

		<div id="contents" class="container-fluid">
			<div class="top_operation row">
				<div class="inner col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<div><a href="login.php"><img src="front/img/sign_in.png"></a></div>
				</div>
			</div>
		</div>
		
		<div id="footer">
			<?php require('inc_top_footer.php');?>
		</div>
	</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
