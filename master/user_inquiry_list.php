<?php
require('includes/application_top.php');

if(($_COOKIE['master_id']) && ($_COOKIE['master_permission'])){
    $master_id = $_COOKIE['master_id'];
    $head_master_name = $_COOKIE['master_name'].'様';
}else{
    //$head_master_name = 'ゲスト様';
    tep_redirect('logout.php', '', 'SSL');
}

/*----- リスト表示 -----*/
//▼初期設定
$query = tep_db_query("
	SELECT
	 `a_inquiry_memberid` AS `memberid`, 
	 `a_inquiry_id` AS `inquiry_id`, 
	 `a_inquiry_title` AS `title`, 
	 `a_inquiry_content` AS `content`, 
	 `a_inquiry_detail_id` AS `detail_id`,
	  `date_create`,`date_update`,
	  `mem00000`.`name1` AS `name`
	FROM `a_inquiry` INNER JOIN `mem00000` ON `mem00000`.`memberid` = `a_inquiry`.`a_inquiry_memberid` 
	WHERE `state` = 1 AND `a_inquiry_detail_id` = 0 AND `a_inquiry_done` = 0 
	ORDER BY `date_update` DESC 
");


//▼表示設定
$iq_list_tr = '';
while($a = tep_db_fetch_array($query)) {
    $iq_list_tr .= '<tr>';
    $iq_list_tr .= '<td>'.$a['name'].'　様</td>';
    $iq_list_tr .= '<td>'.$a['inquiry_id'].'</td>';
    $iq_list_tr .= '<td><a href="./user_inquiry_answer.php?id='.$a['inquiry_id'].'">'.$a['title'].'</a></td>';
    $iq_list_tr .= '<td>'.$a['content'].'</td>';
    $iq_list_tr .= '<td>'.$a['date_update'].'</td>';
    $iq_list_tr .= '</tr>';
}


$list_head = '<tr><th>送信者</th><th>お問い合わせID</th><th>件名</th><th>内容</th><th>お問い合わせ日時</th></tr>';

$input_list = '<table class="table table-bordered" style="font-size:11px;">';
$input_list.= '<tr>'.$list_head.'</tr>';
$input_list.= $iq_list_tr;
$input_list.= '</table>';


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
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
        var opmonth = ["1","2","3","4","5","6","7","8","9","10","11","12"];
        var opday   = ["日","月","火","水","木","金","土"];
        var dopt ={
            dateFormat :'yy-mm-dd',
            changeMonth:true,
            monthNames:opmonth,monthNamesShort:opmonth,
            dayNames:opday,dayNamesMin:opday,dayNamesShort:opday,
            showMonthAfterYear:true
        }

        $(function() {
            var Pkeep = '<?php echo $keep;?>';
            if(!Pkeep){	$('#dReceive').datepicker(dopt);}
            $('#dFigure').datepicker(dopt);
        });

        function jSetPayRateAmt(Amt){$('#paidAmt').val(Amt);}
    </script>
    <style>
        .order_list th{line-height:110%;}
        .ok{color:#00F; font-weight:800; text-align:center;}
        .done{background:#E0E0E0;}
        .name_err{background:#F00; color:#FFF; line-height:100%;padding:5px 10px;font-weight:800;}

        .btn_rcv  {padding:2px 0; font-size:11px;width:180px;line-height:110%;}
        .btn_rcval{padding:2px 0; font-size:11px;width:70px;color:#00F;font-weight:800;min-height:34px;}

        #jDoneForm      {display:none;}
        #jDoneForm.dShow{display:block;}
    </style>
</head>
<body>
<div id="wrapper">

    <?php echo $pop;?>
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
            </div>
            <div id="left2" class="col-xs-12 col-md-12 col-md-8 col-lg-10">
                <div class="inner">
                    <div class="part">

                        <div class="area1">
                            <h2>お問い合わせ一覧</h2>
                            <?php echo $input_list;?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/MyHelper.js" charset="UTF-8"></script>

    <div id="footer">
        <?php require('inc_master_footer.php');?>
    </div>
</div>
<script>
    $('#qrcode').qrcode({width: 196, height: 196, text:'<?php echo $linkurl;?>'});
    $('.onQR').on('click',function(){
        $('#Pop').toggleClass('isOpen');
    });
</script>
<script>
    $(function () {
        var clipboard = new Clipboard('.onClip');

        clipboard.on('success',function(e){
            e.clearSelection();
            alert('コピーしました');
        });
        clipboard.on('error', function(e) {
            alert('コピーに失敗しました');
        });
    });
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
