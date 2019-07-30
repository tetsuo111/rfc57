<?php 
	//<a href="user_tree.php"><li>組織管理</li></a>
	$ppa = $_COOKIE['master_permission'];
	
	$menu_admin = '<div class="spc100">';
	$menu_admin.= '<h3>管理者</h3>';
	$menu_admin.= '<ul>';
	$menu_admin.= '<a href="admin_add_user.php"><li>オペレータ登録</li></a>';
	$menu_admin.= '</ul>';
	$menu_admin.= '</div>';

	$menu_master = '<div class="spc50">';
	$menu_master.= '<h3>マスター</h3>';
	$menu_master.= '<ul>';
	$menu_master.= '<a href="master_rank.php"><li>マスター登録</li></a>';
	$menu_master.= '<a href="zsys_setting.php"><li>システム設定</li></a>';
	$menu_master.= '</ul>';
	$menu_master.= '</div>';
	
	if($ppa == 's'){
		$inleft = $menu_admin;
		$inleft.= $menu_master;
		
	}elseif($ppa == 'a'){
		$inleft = $menu_admin;
	}else{
		$inleft = '';
	}
?>

<div id="admin_menu_list" class="text_link">
	<h3>業務</h3>
	<ul>
		<a href="index.php"><li>管理画面TOP</li></a>
		<a href="user_order_list.php"><li>注文管理</li></a>
		<a href="user_inquiry_list.php"><li>問合管理</li></a>
		<a href="user_list_master.php"><li>顧客管理</li></a>
		<a href="admin_doc.php"><li>顧客補助</li></a>
		<a href=""><li>コミッション</li></a>
	</ul>
	
	<?php echo $inleft;?>
</div>