<style>
	.menu_ct2_outer {margin-top:15px;}
	
	.menu_ct1 {margin:0 10px;}
	.menu_ct1 li {margin-right:10px; float:left;}
	.menu_ct1 li a{background:#777; padding:10px 13px; text-decoration:none;color:#FFFFFF;}
	.menu_ct1 li a:hover{opacity:0.8;}
	
	/*.menu_ct2 li{padding:10px;float:left;background:#F5F5F5;color:#666666;margin-right:1px;}*/
	/*.menu_area{padding:30px 0; background:#E5E5E5; width:1060px; margin:0 auto;}*/
	
	.menu_ct2 li{padding:10px;float:left;background:#E5E5E5;color:#666666;margin-right:1px;}
	.menu_ct2 li:hover{cursor:pointer;}

	.menu_area{padding:30px 0; background:#F5F5F5; width:1060px; margin:0 auto;}
	
	.menu_nolink{background:#777; padding:10px 13px; text-decoration:none; color:#FFFFFF;}
	
	/*
		site color #033
		rgb(0,51,51,1)
	*/
</style>
<script type="text/javascript">

function GetPageNow(PageTitle,Tabname,PageID){
	//var TabColorSelect = '#E5E5E5'
	var TabColorSelect = '#F5F5F5'
	
	//指定箇所の表示を変更
	document.getElementById(Tabname).style.backgroundColor = TabColorSelect;
	document.getElementById("PageTitle").innerHTML         = PageTitle;
	document.getElementById(PageID).style.backgroundColor  = '#033';
}
</script>


<?php

/*----------メニュー定義----------*/
//▼システム設定
$tab_set0 = '<ul class="menu_ct2">';
$tab_set0.= '<li id="tab01" OnClick="location.href=\'zsys_setting.php\';">■システム設定　＞</li>';
$tab_set0.= '</ul>';

$box0_1 ='<ul class="menu_ct1">';
$box0_1.='<li><a id="zsys_setting"     href="zsys_setting.php">このサイトで使う数値を登録する</a>　＞</li>';
$box0_1.='<li><a id="zsys_master_bank" href="zsys_master_bank.php">振込先銀行登録.</a></li>';
$box0_1.='</ul>';

$box0_2 ='<ul class="menu_ct1">';
$box0_2.='<li><a id="zsys_start_user"  href="zsys_start_user.php">初期状態に戻す</a></li>';
$box0_2.='</ul>';


//▼マスター設定
$tab_set1 = '<ul class="menu_ct2">';
$tab_set1.= '<li id="tab01" OnClick="location.href=\'master_rank.php\';">■マスター登録　＞</li>';
$tab_set1.= '<li id="tab02" OnClick="location.href=\'master_currency_setting.php\';">通貨レート設定.</li>';
$tab_set1.= '<li id="tab03" OnClick="location.href=\'master_plan_first.php\';">■商品設定　＞</li>';
$tab_set1.= '<li id="tab04" OnClick="location.href=\'master_payment_restriction.php\';">支払設定　＞</li>';
$tab_set1.= '<li id="tab05" OnClick="location.href=\'master_order_first.php\';">注文シミュレーション.</li>';
$tab_set1.= '</ul>';

$box1_1 ='<ul class="menu_ct1">';
$box1_1.='<li><a id="master_rank"      href="master_rank.php">ランク</a>　＞</li>';
$box1_1.='<li><a id="master_point"     href="master_point.php">ポイント</a>　＞</li>';
$box1_1.='<li><a id="master_currency"  href="master_currency.php">利用通貨</a>　＞</li>';
$box1_1.='<li><a id="master_item"      href="master_item.php">取扱品目</a>　＞</li>';
$box1_1.='<li><a id="master_culc"      href="master_culc.php">手数料計算式</a>　＞</li>';
$box1_1.='<li><a id="master_cost"      href="master_cost.php">商品手数料</a>　＞</li>';
$box1_1.='<li><a id="master_payment"   href="master_payment.php">支払方法.</a></li>';
$box1_1.='</ul>';

$box1_2 ='<ul class="menu_ct1">';
$box1_2.='<li><a id="master_currency_setting" href="master_currency_setting.php">レートを登録.</a></li>';
$box1_2.='</ul>';


$box1_3 ='<ul class="menu_ct1">';
$box1_3.='<li><a id="master_plan_first"  href="master_plan_first.php">初回購入商品</a>　＞</li>';
$box1_3.='<li><a id="master_plan_add"    href="master_plan_add.php">追加購入商品</a>　＞</li>';
$box1_3.='<li><a id="master_plan_auto"   href="master_plan_auto.php">定期購入商品.</a></li>';
$box1_3.='</ul>';

$box1_4 ='<ul class="menu_ct1">';
$box1_4.='<li><a id="master_payment_restriction" href="master_payment_restriction.php">支払設定.</a></li>';
$box1_4.='</ul>';


$box1_5 ='<ul class="menu_ct1">';
$box1_5.='<li><a id="master_order_first" href="master_order_first.php">初回注文</a>　＞</li>';
$box1_5.='<li><a id="master_order_add"   href="master_order_add.php">追加注文</a>　＞</li>';
$box1_5.='<li><a id="master_order_auto"  href="master_order_auto.php">定期購入.</a></li>';
$box1_5.='</ul>';



//▼顧客管理
$tab_set2 = '<ul class="menu_ct2">';
$tab_set2.= '<li id="tab01" OnClick="location.href=\'user_list_master.php\';">■顧客管理　＞</li>';
$tab_set2.= '<li id="tab03" OnClick="location.href=\'user_active.php\';">アクティブにする.</li>';
$tab_set2.= '</ul>';

$box2_1 ='<ul class="menu_ct1">';
$box2_1.='<li><a id="user_list_master" href="user_list_master.php">顧客一覧.</a></li>';
$box2_1.='</ul>';

$box2_2 ='<ul class="menu_ct1">';
$box2_2.='<li><a id="user_new_up_list" href="user_new_up_list.php">顧客検索</a>　＞</li>';
$box2_2.='<li><span id="user_new_up_change" class="menu_nolink">紹介者を変更する.</span></li>';
$box2_2.='</ul>';

$box2_3 ='<ul class="menu_ct1">';
$box2_3.='<li><a id="user_active"       href="user_active.php">通常アクティブ.</a></li>';
$box2_3.='</ul>';

$box2_4 ='<ul class="menu_ct1">';
$box2_4.='<li><a id="user_tree" href="user_tree.php">組織図.</a></li>';
$box2_4.='</ul>';

$box2_5 ='<ul class="menu_ct1">';
$box2_5.='<li><a id="user_csv_dl" href="user_csv_dl.php">CSVダウンロード.</a></li>';
$box2_5.='</ul>';


//▼注文管理
$tab_set3 = '<ul class="menu_ct2">';
$tab_set3.= '<li id="tab01" OnClick="location.href=\'user_order_list.php\';">■入金処理</li>';
$tab_set3.= '<li id="tab02" OnClick="location.href=\'sales_order_list.php\';">■売上管理</li>';
$tab_set3.= '</ul>';

$box3_1 ='<ul class="menu_ct1">';
$box3_1.='<li><a id="user_order_list"            href="user_order_list.php">入金確認</a>　＞</li>';
$box3_1.='<li><a id="user_order_back_befor_ship" href="user_order_back_befor_ship.php">出荷前返品</a>　＞</li>';
$box3_1.='<li><a id="user_order_back_after_ship" href="user_order_back_after_ship.php">出荷後返品</a>　＞</li>';
$box3_1.='<li><a id="user_order_adjust_edit"     href="user_order_adjust_edit.php">登録した調整金を編集</a>　＞</li>';
$box3_1.='<li><a id="user_order_adjust_add"      href="user_order_adjust_add.php" >調整金を追加で登録</a>.</li>';
$box3_1.='</ul>';


$box3_2 ='<ul class="menu_ct1">';
$box3_2.='<li><a id="sales_order_list"  href="sales_order_list.php">売上管理.</a></li>';
$box3_2.='</ul>';


//▼ユーザー補助
$tab_set4 = '<ul class="menu_ct2">';
$tab_set4.= '<li id="tab01" OnClick="location.href=\'admin_doc.php\';">■顧客補助　＞</li>';
$tab_set4.= '<li id="tab02" OnClick="location.href=\'admin_qa.php\';">QA登録.</li>';
$tab_set4.= '</ul>';

$box4_1 ='<ul class="menu_ct1">';
$box4_1.='<li><a id="admin_doc"    href="admin_doc.php">顧客用資料登録</a>　＞</li>';
$box4_1.='<li><a id="admin_event"  href="admin_event.php">イベント登録</a>　＞</li>';
$box4_1.='<li><a id="admin_notice" href="admin_notice.php">お知らせ登録.</a></li>';
$box4_1.='</ul>';

$box4_2 ='<ul class="menu_ct1">';
$box4_2.='<li><a id="admin_qa_tag" href="admin_qa_tag.php">QA区分を追加</a>　＞</li>';
$box4_2.='<li><a id="admin_qa"     href="admin_qa.php">QAを登録.</a></li>';
$box4_2.='</ul>';


//▼報酬計算
$tab_set6 = '<ul class="menu_ct2">';
$tab_set6.= '<li id="tab01" OnClick="location.href=\'reward_af_set.php\';">■アフィリエイト報酬　＞</li>';
$tab_set6.= '<li id="tab02" OnClick="location.href=\'reward_af_list.php\';">計算結果を見る　＞</li>';
$tab_set6.= '<li id="tab03" OnClick="location.href=\'reward_rank_set.php\';">ランクアップ.</li>';
$tab_set6.= '<li id="tab04" OnClick="location.href=\'reward_point_set.php\';">■ポイント報酬.</li>';
$tab_set6.= '</ul>';

$box6_1 ='<ul class="menu_ct1">';
$box6_1.='<li><a id="reward_af_set"  href="reward_af_set.php" >期間を設定する</a>　＞</li>';
$box6_1.='<li><a id="reward_af_calc" href="reward_af_calc.php">報酬金額を計算する.</a></li>';
$box6_1.='</ul>';

$box6_2 ='<ul class="menu_ct1">';
$box6_2.='<li><a    id="reward_af_list"   href="reward_af_list.php">計算結果一覧</a>　＞</li>';
$box6_2.='<li><span id="reward_af_result" class="menu_nolink">計算詳細.</span></li>';
$box6_2.='</ul>';

$box6_3 ='<ul class="menu_ct1">';
$box6_3.='<li><a id="reward_rank_set"        href="reward_rank_set.php">ランクアップを登録する</a>　＞</li>';
$box6_3.='<li><a id="reward_rank_up_result"  href="reward_rank_up_result.php">結果を見る.</a></li>';
$box6_3.='</ul>';

$box6_4 ='<ul class="menu_ct1">';
$box6_4.='<li><a id="reward_point_set"    href="reward_point_set.php">期間を設定する</a>　＞</li>';
$box6_4.='<li><a id="reward_point_culc"   href="reward_point_culc.php">報酬金額を計算する</a>　＞</li>';
$box6_4.='<li><a id="reward_point_result" href="reward_point_result.php">計算結果を見る.</a></li>';
$box6_4.='</ul>';


//▼ユーザー補助
$tab_set7 = '<ul class="menu_ct2">';
$tab_set7.= '<li id="tab01" OnClick="location.href=\'admin_add_user.php\';">■オペレータを登録する</li>';
$tab_set7.= '</ul>';

$box7_1 ='<ul class="menu_ct1">';
$box7_1.='<li><a id="admin_add_user" href="admin_add_user.php">オペレータ登録</a></li>';
$box7_1.='</ul>';


/*----------表示内容----------*/
//▼ページを取得
$now_page = basename($_SERVER['PHP_SELF']);
$page_id  = basename($_SERVER['PHP_SELF'],".php");


/*=======================
システム設定
=======================*/
if(
	  ($now_page == "zsys_setting.php")
	OR($now_page == "zsys_master_bank.php")
	OR($now_page == "zsys_start_user.php")
){
	//▼表示タイトル
	$page_title = "システム設定";
	$menu_tab   = $tab_set0;
	
	
	//-----ボックス設定-----
	//▼tab01
	if(
		($now_page == "zsys_setting.php")
		OR($now_page == "zsys_master_bank.php")
	){$tab_id = "tab01"; $menu_box = $box0_1;}
	
	//▼tab02
	if(
		($now_page == "zsys_start_user.php")
	){$tab_id = "tab02"; $menu_box = $box0_2;}
}


/*=======================
商品設定
=======================*/
if(
	  ($now_page == "master_rank.php")
	OR($now_page == "master_point.php")
	OR($now_page == "master_currency.php")
	OR($now_page == "master_item.php")
	OR($now_page == "master_culc.php")
	OR($now_page == "master_cost.php")
	OR($now_page == "master_payment.php")
	OR($now_page == "master_detail.php")
	
	OR($now_page == "master_currency_setting.php")
	
	OR($now_page == "master_plan_first.php")
	OR($now_page == "master_plan_add.php")
	OR($now_page == "master_plan_auto.php")
	
	OR($now_page == "master_payment_restriction.php")
	
	OR($now_page == "master_order_first.php")
	OR($now_page == "master_order_add.php")
	OR($now_page == "master_order_auto.php")
){
	//▼表示タイトル
	$page_title = "マスター設定";
	$menu_tab   = $tab_set1;
	
	
	//-----ボックス設定-----
	//▼tab01
	if(
		  ($now_page == "master_rank.php")
		OR($now_page == "master_point.php")
		OR($now_page == "master_currency.php")
		OR($now_page == "master_item.php")
		OR($now_page == "master_culc.php")
		OR($now_page == "master_cost.php")
		OR($now_page == "master_payment.php")
	){$tab_id = "tab01"; $menu_box = $box1_1;}
	
	//▼tab02
	if(
		($now_page == "master_currency_setting.php")
	){$tab_id = "tab02"; $menu_box = $box1_2;}
	
	//▼tab03
	if(
		  ($now_page == "master_plan_first.php")
		OR($now_page == "master_plan_add.php")
		OR($now_page == "master_plan_auto.php")
	){$tab_id = "tab03"; $menu_box = $box1_3;}
	
	//▼tab04
	if(
		($now_page == "master_payment_restriction.php")
	){$tab_id = "tab04"; $menu_box = $box1_4;}
	
	//▼tab05
	if(
		  ($now_page == "master_order_first.php")
		OR($now_page == "master_order_auto.php")
		OR($now_page == "master_order_add.php")
	){$tab_id = "tab05"; $menu_box = $box1_5;}
}


/*=======================
顧客管理
=======================*/
if(
	  ($now_page == "user_list_master.php")
	OR($now_page == "user_reg_master.php")
	OR($now_page == 'user_active.php')
	OR($now_page == 'user_new_up_list.php')
	OR($now_page == 'user_new_up_change.php')
){

	//▼表示タイトル
	$page_title = "顧客管理";
	$menu_tab   = $tab_set2;
	
	
	//-----ボックス設定-----
	//▼tab01
	if(   
		  ($now_page == "user_list_master.php")
		OR($now_page == "user_reg_master.php")
	){$tab_id = "tab01"; $menu_box = $box2_1;}
	
	//▼tab02
	if(
		  ($now_page == 'user_new_up_change.php')
		OR($now_page == 'user_new_up_list.php')
	){$tab_id = "tab02"; $menu_box = $box2_2;}
	
	//▼tab03
	if(
		($now_page == 'user_active.php')
	){$tab_id = "tab03"; $menu_box = $box2_3;}
}


/*=======================
注文管理
=======================*/
if(
	  ($now_page == "user_order_list.php")
	OR($now_page == "sales_order_list.php")
	OR($now_page == "user_order_back_befor_ship.php")
	OR($now_page == "user_order_back_after_ship.php")
	OR($now_page == "user_order_adjust_add.php")
	OR($now_page == "user_order_adjust_edit.php")
){

	//▼表示タイトル
	$page_title = "注文管理";
	$menu_tab   = $tab_set3;
	
	
	//-----ボックス設定-----
	//▼tab01
	if(
		  ($now_page == "user_order_list.php")
		OR($now_page == "user_order_back_befor_ship.php")
		OR($now_page == "user_order_back_after_ship.php")
		OR($now_page == "user_order_adjust_add.php")
		OR($now_page == "user_order_adjust_edit.php")
	){$tab_id = "tab01"; $menu_box = $box3_1;}
	
	
	//▼tab02
	if(
		($now_page == "sales_order_list.php")
	){$tab_id = "tab02"; $menu_box = $box3_2;}
}


/*=======================
顧客補助
=======================*/
if(
	  ($now_page == 'admin_doc.php')
	OR($now_page == 'admin_event.php')
	OR($now_page == 'admin_notice.php')
	OR($now_page == 'admin_qa.php')
	OR($now_page == 'admin_qa_tag.php')
){

	//▼表示タイトル
	$page_title = "顧客補助";
	$menu_tab   = $tab_set4;
	
	
	//-----ボックス設定-----
	//▼tab01
	if(
		  ($now_page == 'admin_doc.php')
		OR($now_page == 'admin_event.php')
		OR($now_page == 'admin_notice.php')
	){$tab_id = "tab01"; $menu_box = $box4_1;}
	
	if(
		  ($now_page == 'admin_qa.php')
		OR($now_page == 'admin_qa_tag.php')
	){$tab_id = "tab02"; $menu_box = $box4_2;}
}



/*=======================
企業別ユーザー登録
=======================*/
if(
	($now_page == 'admin_add_user.php')
){

	//▼表示タイトル
	$page_title = "オペレータ登録";
	$menu_tab   = $tab_set7;
	
	
	//-----ボックス設定-----
	//▼tab01
	if(
		($now_page == 'admin_add_user.php')
	){$tab_id = "tab01"; $menu_box = $box7_1;}
}


/*----------フォーム成形----------*/
//▼メニューの整形
$menu_form = '<div class="menu_ct2_outer">';
$menu_form.= $menu_tab;
$menu_form.= '<div class="clear_float"></div>';
$menu_form.= '</div>';
$menu_form.= '<div class="menu_area_outer">';
$menu_form.= '<div class="menu_area">';
$menu_form.= $menu_box;
$menu_form.= '<div class="clear_float"></div>';
$menu_form.= '</div>';
$menu_form.= '</div>';
$menu_form.= '<script type="text/javascript">GetPageNow("'.$page_title.'","'.$tab_id.'","'.$page_id.'");</script>';

echo $menu_form;
?>