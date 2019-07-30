<?php 
require('includes/application_top.php');
$pass_word = '12345';
//$aa = tep_encrypt_password($pass_word);

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

</head>
<body>
	<div id="wrapper">

		<div id="header"></div>

		<div id="contents" class="container-fluid">
			<div class="top_operation">
				<div class="inner">
					<?php echo $aa;?>
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
