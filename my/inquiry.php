<?php
require('includes/application_top.php');

if($_COOKIE['user_id']){
    $user_id   = $_COOKIE['user_id'];
    $head_user_name = $_COOKIE['user_name'].'様';
}else{
    $user_id = 0;
    //$head_user_name = 'ゲスト様';
    tep_redirect('../logout.php', '', 'SSL');
}

//▼ユーザー情報伝達
require ('inc_user_announce.php');


//$user_query = tep_db_query("
//	SELECT `email`
//	FROM `".TABLE_MEM00000."`
//	WHERE `memberid` = '".tep_db_input($user_id)."'
//");


//$user = tep_db_fetch_array($user_query);
$iq_title  = $_POST['iq_title'];
$iq_content  = $_POST['iq_content'];


if (isset($_POST['act']) && ($_POST['act'] == 'edit')) {
    $err = false;
    $err_empty = false;

    if(empty($iq_content) || empty($iq_title)){
        $err = true; $err_empty = true;
    }
}elseif($_POST['act'] == 'no_err'){

    $db_table = 'a_inquiry';
    $t_ai_id = 'a_inquiry_ai_id';
    $t_id = 'a_inquiry_id';

    $sql_data_array = array(
//			'a_notice_target'    => $p_target,
        'a_inquiry_title'   => $iq_title,
        'a_inquiry_content'   => $iq_content,
        'a_inquiry_memberid'   => $user_id,
        'a_inquiry_side' => 0,
        'date_create'        => 'now()',
        'date_update'        => 'now()',
        'state'              => '1'
    );


    $notice_id = zDBNewUniqueID($db_table,$sql_data_array,$t_ai_id,$t_id);

    $announce = '<div class="announce">';
    $announce.= 'お問い合わせを承りました。<br>';
    $announce.= '</div>';
    $echo_flag = 'announce';


    //▼会員ID
    $query = tep_db_query("SELECT `login_id`,`name1` FROM `".TABLE_MEM00000."` WHERE `memberid` = '".tep_db_input($user_id)."'");
    $mem0  = tep_db_fetch_array($query);

    //----- メール送信 -----//
    //▼送信設定
    $email            = zGetUserEmail($user_id);	//送信メールアドレス
//    $email            = 'fourloop.jp@gmail.com';	//送信メールアドレス
    $Efs_id           = $mem0['login_id'];			//会員ID　＞cisログインID
    $Euser_name       = $mem0['name1'];		//ユーザー名

    Email_Contact(
        $EmailHead,
        $EmailFoot,
        $email,
        $Euser_name,
        $iq_title,
        $iq_content
    );

}

//err text
if($err_empty    == true) { $edit_err_text  = '<p class="alert">未入力の項目があります。</p>'; }
//if($err_email == true) { $edit_err_text .= '<p class="alert">既に登録のあるメールアドレスです。</p>'; }

if(($_POST['act'] == 'edit') && ($err == false)){
    $item .= '<input type="hidden" name="iq_title" value="'.$iq_title.'">';             //希望確認
    $item .= '<input type="hidden" name="iq_content" value="'.$iq_content.'">';             //希望確認

    $edit_err_text = '以下の内容でお問い合わせを送信します。';
    $edit_form  = '<form name="inquiry" action="inquiry.php" method="post">';
    $edit_form .= '<input type="hidden" name="act" value="no_err">';
    $edit_form .= $item;
    $edit_form_ele_text = $edit_err_text;
    $edit_form_ele_1 = '<input class="form-control" type="text" name="title" value="'.$iq_title.'" disabled>';
    $edit_form_ele_2 = '<textarea class="form-control" name="content" cols="10" rows="5" disabled>'.$iq_content.'</textarea>';                      //希望
    $edit_form_ele_submit = '<input type="submit"  class="btn btn-default" value="送信する">';
    $edit_form_end  = '</form>';

}else{
    $edit_form  = '<form name="inquiry" action="inquiry.php" method="post">';
    $edit_form .= '<input type="hidden" name="act" value="edit">';
    $edit_form_ele_text = $edit_err_text;
//	$edit_form_ele_1 = '<input class="form-control" type="text" name="now_email" value="'.$now_email.'" disabled="disabled">';  //現在
    $edit_form_ele_1 = '<input class="form-control" type="text" name="iq_title">';
    $edit_form_ele_2 = '<textarea class="form-control" name="iq_content" cols="10" rows="10"></textarea>';                      //希望
    $edit_form_ele_submit = '<input type="submit" class="btn btn-default" value="確認">';
    $edit_form_end  = '</form>';
}


//問い合わせ履歴の表示
$query = tep_db_query("
	SELECT
	 `a_inquiry_memberid` AS `memberid`, 
	 `a_inquiry_id` AS `inquiry_id`, 
	 `a_inquiry_title` AS `title`, 
	 `a_inquiry_content` AS `content`, 
	 `a_inquiry_detail_id` AS `detail_id`,
	 `a_inquiry_side` AS `side`,
	  `date_create`,`date_update`,
	  `mem00000`.`name1` AS `name`
	FROM `a_inquiry` INNER JOIN `mem00000` ON `mem00000`.`memberid` = `a_inquiry`.`a_inquiry_memberid` 
	WHERE `state` = 1 AND `a_inquiry_detail_id` = 0 AND `a_inquiry_memberid` = ".$user_id." AND `a_inquiry_done` = 0 
	ORDER BY `date_update` DESC 
");


//▼表示設定
$iq_list_tr = '';
while($a = tep_db_fetch_array($query)) {
    $iq_list_tr .= '<tr>';
    if($a['side']==0){
        $iq_list_tr .= '<td>'.$a['name'].' 様</td>';
    }else{
        $iq_list_tr .= '<td>'.$a['name'].'</td>';
    }
    $iq_list_tr .= '<td>'.$a['inquiry_id'].'</td>';
    $iq_list_tr .= '<td><a href="./inquiry_answer.php?id='.$a['inquiry_id'].'">'.$a['title'].'</a></td>';
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
<head>
    <meta http-equiv="Content-Type"         content="text/html; charset=<?php echo CHARSET; ?>">
    <meta http-equiv="Content-Style-Type"  content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
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
    <link rel="stylesheet" type="text/css" href="../js/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="../js/bootstrap/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="../css/my.css"       media="all">

    <script src="../js/jquery-3.2.1.min.js"            charset="UTF-8"></script>
    <script src="../js/bootstrap/js/bootstrap.min.js" charset="UTF-8"></script>

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
                    <?php echo $warning; ?>

                    <div class="area1">
                        <?php
                        if($echo_flag == 'announce'){
                            echo $announce;
                        }else{
                            ?>
                            <?php echo $edit_form;?>
                            <?php echo $edit_form_ele_text; ?>
                            <div class="form_group form_area">
                                <?php if($_POST['act'] == 'edit'): ?>
                                    <h3>お問い合わせ内容の確認</h3>
                                <?php else: ?>
                                    <h3>お問い合わせ</h3>
                                <?php endif; ?>
                                <ul class="form_table">
                                    <li>
                                        <div class="form_el row">
                                            <h4>タイトル</h4>
                                            <div><?php echo $edit_form_ele_1; ?></div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="form_el row">
                                            <h4>お問い合わせ内容</h4>
                                            <div><?php echo $edit_form_ele_2; ?></div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="submit_area spc20">
                                <?php echo $edit_form_ele_submit;?>
                            </div>
                            <?php echo $edit_form_end;?>
                        <?php } ?>
                    </div>



                </div>
            </div>

            <div id="left2" class="col-xs-12 col-md-12 col-md-8 col-lg-10">
                <div class="inner">
                    <div class="part">

                        <div class="area1">
                            <h3>お問い合わせ履歴</h3>
                            <?php echo $input_list;?>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <div id="footer">
        <?php require('inc_user_footer.php');?>
    </div>
    <script src="../js/MyHelper.js" charset="UTF-8"></script>
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
