<?php 

/*
$menu_ul.= '<a class="u_menu_a" href="order.php"><li>ご注文</li></a>';
$menu_ul.= '<a class="u_menu_a" href="order_list.php"><li>ご注文一覧</li></a>';
$menu_ul.= '<a class="u_menu_a" href="info_bank.php"><li>ご入金先</li></a>';
*/

//--------------メニュー設定--------------
//▼ログイン中
$menu_ul = '<p class="u_menu-head">Menu</p>';
$menu_ul.= '<ul class="u_menu_ul">';
$menu_ul.= '<a class="u_menu_a" href="index.php"><li>Top</li></a>';
$menu_ul.= '<a class="u_menu_a" href="news.php"><li>新着ニュース</li></a>';
$menu_ul.= '<a class="u_menu_a" href="edit_user_info.php"><li>会員情報</li></a>';
$menu_ul.= '<a class="u_menu_a" href="index.php"><li>アフィリエイトバナー</li></a>';
$menu_ul.= '<a class="u_menu_a" href="order.php"><li>商品購入</li></a>';
$menu_ul.= '<a class="u_menu_a" href="index.php"><li>ご紹介実績</li></a>';
$menu_ul.= '<a class="u_menu_a" href="index.php"><li>報酬</li></a>';
$menu_ul.= '<a class="u_menu_a" href="doc_dl.php"><li>資料ダウンロード</li></a>';
$menu_ul.= '<a class="u_menu_a" href="qanda.php"><li>Q&A　よくある質問</li></a>';
//$menu_ul.= '<a class="u_menu_a" href="edit_user_info.php"><li>User Data</li></a>';
//$menu_ul.= '<a class="u_menu_a" href="order.php"><li>Order</li></a>';
//$menu_ul.= '<a class="u_menu_a" href="qanda.php"><li>Q & A</li></a>';
//$menu_ul.= '<a class="u_menu_a" href="doc_dl.php"><li>Documents</li></a>';
//$menu_ul.= '<span class="u_menu_a" onclick="alert(\'準備中\');"><li>Chart</li></span>';
//$menu_ul.= '<a class="u_menu_a" href="../plugin"><li>Chart</li></a>';
$menu_ul.= $input_wc_buy;
$menu_ul.= '</ul>';

$menu_ul.= '<div class="spc50">';
$menu_ul.= '<p class="u_menu-head">Account</p>';
$menu_ul.= '<ul class="u_menu_ul">';
$menu_ul.= '<a class="u_menu_a" href="edit_data_bank.php"><li>報酬受取口座</li></a>';
$menu_ul.= '<a class="u_menu_a" href="edit_data_email.php"><li>メールアドレス変更</li></a>';
$menu_ul.= '<a class="u_menu_a" href="edit_data_pass.php"><li>パスワード変更</li></a>';
$menu_ul.= '<a class="u_menu_a" href="../logout.php"><li>ログアウト</li></a>';
$menu_ul.= '</ul>';
$menu_ul.= '</div>';

echo $menu_ul;
?>
