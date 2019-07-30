<?php
////
// Output the array[TEXT] from array[ID] of form pull down menu
	function tep_output_array_text($array, $id) {
		foreach($array as $rec){
			if ($rec['id'] == $id) $val = $rec['text'];
		}
		return $val;
	}

//// file upload
// Output a form enctype
	function tep_draw_form_enctype($name, $action, $parameters = '') {
		$form = '<form name="' . $name . '" action="';
		if ($parameters) {
			$form .= tep_href_link($action, $parameters);
		} else {
			$form .= tep_href_link($action);
		}
		$form .= '" method="post" enctype="multipart/form-data">';
		return $form;
	}

// Output a form filefield
	function tep_draw_file_field($name) {
		$field = tep_draw_input_field($name, $value = '', '', 'file');
		return $field;
	}

	function tep_get_uploaded_file($name) {
		if (isset($_FILES[$name])) {
			$uploaded_file = array(
				'name' => $_FILES[$name]['name'],
				'type' => $_FILES[$name]['type'],
				'size' => $_FILES[$name]['size'],
				'tmp_name' => $_FILES[$name]['tmp_name']
			);
		} 
		return $uploaded_file;
	}

	function tep_copy_uploaded_file($name, $target) {
		if (substr($target, -1) != '/') $target .= '/';
		$target .= $name['name'];
		move_uploaded_file($name['tmp_name'], $target);
	}

// multi extension file uploader
	function tep_uploader($name, $upfile, $updir) {
		tep_copy_uploaded_file($upfile, $updir);
		$extension = pathinfo($upfile['name'], PATHINFO_EXTENSION); //Extension distinction
		$name = $name. '.'. $extension;
		if(file_exists($updir. $name)) unlink($updir. $name);
		rename($updir. $upfile['name'], $updir. $name);
		return $name;
	}


// image uploader
	function tep_image_uploader($name, $upfile, $updir, $max_size = '') {
		$extension = pathinfo($upfile['name'], PATHINFO_EXTENSION); //extension distinction
		if(($max_size) && ($upfile['size'] >= $max_size)) {
			$error = true;
			$return = false;
		} elseif (($extension != 'jpg') && ($extension != 'jpeg') && ($extension != 'png') && ($extension != 'gif')) {
			$error = true;
			$return = false;
		}
		if($error == false) {
			tep_copy_uploaded_file($upfile, $updir);
			$name = $name. '.'. $extension;
			if(file_exists($updir. $name)) unlink($updir. $name);
			rename($updir. $upfile['name'], $updir. $name);
			return $name;
		} else {
			print $return;
		}
	}

// image resize uploader_width_fixed
	function tep_resize_uploader_width_fixed($name, $upfile, $updir, $img_width = '', $max_size = '') {
		$extension = pathinfo($upfile['name'], PATHINFO_EXTENSION); //extension distinction
		if(($max_size) && ($upfile['size'] >= $max_size)) {
			$error = true;
			$return = false;
		} elseif (($extension != 'jpg') && ($extension != 'jpeg') && ($extension != 'png') && ($extension != 'gif')) {
			$error = true;
			$return = false;
		}
		if($error == false) {
			tep_copy_uploaded_file($upfile, $updir);
			if (($extension == 'jpg') || ($extension == 'jpeg')) {
				$img_in = ImageCreateFromJPEG($updir. $upfile['name']); //JPEG image create
			} elseif ($extension == 'png'){
				$img_in = ImageCreateFromPNG($updir. $upfile['name']); //PNG image create
			} elseif ($extension == 'gif'){
				$img_in = ImageCreateFromGIF($updir. $upfile['name']); //GIF image create
			}
			if ($img_width) {
				$img_size = getImageSize($updir. $upfile['name']); //get size
				$img_high = round($img_size[1] * $img_width / $img_size[0]);
				$img_out = ImageCreateTruecolor($img_width, $img_high); // GD2.01 indispensability following
				ImageCopyResampled($img_out, $img_in, 0, 0, 0, 0, $img_width, $img_high, $img_size[0], $img_size[1]); // GD2.01 indispensability following
				if(file_exists($updir. $name)) unlink($updir. $name);
				$name = $name. '.'. $extension;
				ImageJPEG($img_out, $updir. $name); //writing new imege
				unlink($updir. $upfile['name']); //delete original image
				ImageDestroy($img_in); //memory usage
				ImageDestroy($img_out); //memory usage
				return $name;
			} else {
				$name = $name. '.'. $extension;
				if(file_exists($updir. $name)) unlink($updir. $name);
				rename($updir. $upfile['name'], $updir. $name);
				return $name;
			}
		} else {
			print $return;
		}
	}

// image resize uploader
	//max_min $width or $height 最大値を最小値セットで縮小 or 最小値を最大値セットで縮小
	//$is_small 元画像が小さい場合リサイズしない
	function tep_resize_uploader($name, $upfile, $updir, $width = '', $height = '', $trim_width = '', $trim_height = '', $max_size = '', $max_min = false, $is_small = false) {
		$extension = pathinfo($upfile['name'], PATHINFO_EXTENSION); //extension distinction
		if(($max_size) && ($upfile['size'] >= $max_size)) {
			$error = true;
			$return = false;
		} elseif (($extension != 'jpg') && ($extension != 'jpeg') && ($extension != 'png') && ($extension != 'gif')) {
			$error = true;
			$return = false;
		}
		if($error == false) {
			tep_copy_uploaded_file($upfile, $updir);
			if (($extension == 'jpg') || ($extension == 'jpeg')) {
				$imagein = ImageCreateFromJPEG($updir. $upfile['name']); //JPEG image create
			} elseif ($extension == 'png') {
				$imagein = ImageCreateFromPNG($updir. $upfile['name']); //PNG image create
			} elseif ($extension == 'gif') {
				$imagein = ImageCreateFromGIF($updir. $upfile['name']); //GIF image create
			}
			$imagesize = getimagesize($updir. $upfile['name']);
			$is_small = (($is_small == true) && (($width && ($width > $imagesize[0])) || ($height && ($height > $imagesize[0])))) ? true : false;
			if (($width || $height) && ($is_small == false)) {
				if ($max_min != true) {
					if ($imagesize[0] > $imagesize[1]) { //画像が横長の場合、横幅を$width値とする
						$ratio = $width / $imagesize[0];
						$height = round($imagesize[1] * $ratio);
					} else { //画像が縦長の場合、縦幅を$height値とする
						$ratio = $height / $imagesize[1];
						$width = round($imagesize[0] * $ratio);
					}
				} else {
					if ($imagesize[0] < $imagesize[1]) { //画像が縦長の場合、横幅を$width値とする
						$ratio = $width / $imagesize[0];
						$height = round($imagesize[1] * $ratio);
					} else { //画像が横長の場合、縦幅を$height値とする
						$ratio = $height / $imagesize[1];
						$width = round($imagesize[0] * $ratio);
					}
				}
				if (!$trim_width) $trim_width = $width;
				if (!$trim_height) $trim_height = $height;
				$imageout = ImageCreateTruecolor($trim_width, $trim_height); // GD2.01 indispensability following

				ImageCopyResampled($imageout, $imagein, 0, 0, 0, 0, $width, $height, $imagesize[0], $imagesize[1]); // GD2.01 indispensability following
				if (file_exists($updir. $name)) unlink($updir. $name);
				$name = $name. '.jpg';
				ImageJPEG($imageout, $updir. $name); //writing new imege
				unlink($updir. $upfile['name']); //delete original image
				ImageDestroy($imagein); //memory usage
				ImageDestroy($imageout); //memory usage
				return $name;
			} else {
				$name = $name. '.'. $extension;
				if (file_exists($updir. $name)) unlink($updir. $name);
				rename($updir. $upfile['name'], $updir. $name);
				return $name;
			}
		} else {
			print $return;
		}
	}

////
// The HTML image wrapper function
	//max_min $width or $height 最大値を最小値セットで縮小 or 最小値を最大値セットで縮小
	function tep_image2($src, $alt = '', $width, $height, $parameters = '', $pixel_none = false, $max_min = false) {
		if ( (empty($src) || ($src == DIR_WS_IMAGES)) && (IMAGE_REQUIRED == 'false') ) {
			return false;
		}
		if ($image_size = @getimagesize($src)) {
			if ($max_min != true) {
				if ($image_size[0] > $image_size[1]) { //画像が横長の場合、横幅を$width値とする
					$ratio = $width / $image_size[0];
					$oheight = $height;
					$height = $image_size[1] * $ratio;
					if ($oheight > $height) {
						$pixel_height = ($oheight - $height)/2;
						$pixel = '<div style="clear: both; width: '. $width. '; height: '. intval($pixel_height). 'px; overflow: hidden;"></div>';
					}
				} else { //画像が縦長の場合、横幅を$width値とする
					$ratio = $height / $image_size[1];
					$owidth = $width;
					$width = $image_size[0] * $ratio;
					if ($owidth > $width) {
						$pixel_width = ($owidth - $width)/2;
						$pixel = '<img src="'. DIR_WS_IMAGES. '1x1.png" border="0" width="' . intval($pixel_width) . '" height="' . tep_output_string($height) . '" />';
					}
				}
			} else {
				if ($image_size[0] < $image_size[1]) { //画像が縦長の場合、横幅を$width値とする
					$ratio = $width / $image_size[0];
					$oheight = $height;
					$height = $image_size[1] * $ratio;
					if ($oheight > $height) {
						$pixel_height = ($oheight - $height)/2;
						$pixel = '<div style="clear: both; width: '. $width. '; height: '. intval($pixel_height). 'px; overflow: hidden;"></div>';
					}
				} else { //画像が横長の場合、縦幅を$height値とする
					$ratio = $height / $image_size[1];
					$owidth = $width;
					$width = $image_size[0] * $ratio;

					if ($owidth -1 > $width) { //109x110等、縦伸び対応
						$pixel_width = ($owidth - $width)/2;
						$pixel = '<img src="'. DIR_WS_IMAGES. '1x1.png" border="0" width="' . intval($pixel_width) . '" height="' . tep_output_string($height) . '" />';
					}
				}
			}

		} elseif (IMAGE_REQUIRED == 'false') {
			return false;
		}

		if ($pixel_none == 'true') $pixel = '';

		$image = $pixel;
		$image .= '<img src="' . tep_output_string($src) . '" border="0" alt="' . tep_output_string($alt) . '"';

		if (tep_not_null($alt)) {
			$image .= ' title=" ' . tep_output_string($alt) . ' "';
		}

		//if (tep_not_null($width) && tep_not_null($height)) {
		$image .= ' width="' . intval($width) . '" height="' . intval($height) . '"';
		//}

		if (tep_not_null($parameters)) $image .= ' ' . $parameters;

		$image .= ' />';
		$image .= $pixel;

		return $image;
	}

//// customer dob
// dob pull-down list
	function tep_get_dob_list($name, $raw_date, $selected = '', $parameters = '') {
		$y_selected = substr($raw_date, 0, 4);
		$y_array = array(); 
		$y = date(Y)-100;
		while ($y <= date(Y)-20) { //20years old or more
			$y_array[] = array('id' => $y, 'text' => $y);
			$y++;
		}
		$m_selected = (int)substr($raw_date, 5, 2);
		$m_array = array();
		$m = 1;
		while ($m <= 12) {
			$m_array[] = array('id' => sprintf("%02d",$m), 'text' => $m);
			$m++;
		}
		$d_selected = (int)substr($raw_date, 8, 2);
		$d_array = array();
		$d = 1;
		while ($d <= 31) {
			$d_array[] = array('id' => sprintf("%02d",$d), 'text' => $d);
			$d++;
		}
		$return = tep_draw_pull_down_menu('y_'. $name, $y_array, $y_selected, $parameters);
		$return .= tep_draw_pull_down_menu('m_'. $name, $m_array, $m_selected, $parameters);
		$return .= tep_draw_pull_down_menu('d_'. $name, $d_array, $d_selected, $parameters);
		return $return;
	}

////
// age(dob) pull-down list
	function tep_get_age_list($name, $raw_date, $selected = '', $parameters = '') {
		$selected = substr($raw_date, 0, 4). '/01/01';
		$a_array = array(array('id' => '', 'text' => PULL_DOWN_DEFAULT));
		$a = 18;
		while ($a <= 99) {
			$a_array[] = array('id' => date(Y)-$a. '/01/01', 'text' =>	$a);
			$a++;
		}
		return tep_draw_pull_down_menu($name, $a_array, $selected, $parameters);
	}

// paging_array-parameters
function tep_pager($page, $pager_rows, $limit, $pager_parameters_array){
	$maxPage = ceil($pager_rows / $limit);
	if(($maxPage == 1) || ($maxPage < $page)) return false;

	$parameters = '';
	foreach ($pager_parameters_array as $varname) {
		if($_GET[$varname]) ${$varname} = '&'. $varname. '='. $_GET[$varname];
		$parameters .= ${$varname};
	}

	if ($page > 11) {
		$startPage = ($page - 10);
	} else {
		$startPage = 1;
	}
	if ($page > 1) {
		$startMore = '<a href="'. $_SERVER['PHP_SELF']. '?page='. ($page -1). '&limit='. $limit. $parameters. '">&lsaquo;&lsaquo;前へ</a>';
	}

	if (($page + 10) <= $maxPage) {
		$endPage = ($page + 10);
	} else {
		$endPage = $maxPage;
	}
	if ($page < ceil($pager_rows / $limit)){ 
		$endMore = '<a href="'. $_SERVER['PHP_SELF']. '?page='. ($page +1). '&limit='. $limit. $parameters. '">&nbsp;次へ&rsaquo;&rsaquo;</a>';
	}

	$page_link = '';
	for($i = $startPage; $i <= $endPage; $i++){
		if($page == $i){
			$page_link .= '&nbsp;<span style="font-Size:120%;">'. $i. '</span>';
		}else{
			$page_link .= '&nbsp;<a href="'. $_SERVER['PHP_SELF']. '?page='. $i. '&limit='. $limit. $parameters. '">'. $i. '</a>';
		}
	}
	$page_link = $startMore. $page_link. $endMore;
	return $page_link;
}

function tep_pager1($page, $rows, $limit, $p_array, $num){
	$maxPage = ceil($rows / $limit);
	if(($maxPage == 1) || ($maxPage < $page)) return false;
	$parameters = '';
	foreach ($p_array as $varname) {
		if($_GET[$varname]) ${$varname} = '&'. $varname. '='. $_GET[$varname];
		$parameters .= ${$varname};
	}
	$num1 = $num -1;
	if ($page > $num) {
		$startPage = ($page -$num1);
	} else {
		$startPage = 1;
	}
	if ($page > 1) {
		$startMore = '<a href="'. $_SERVER['PHP_SELF']. '?page='. ($page -1). '&limit='. $limit. $parameters. '">&lsaquo;&lsaquo;前</a>';
	}
	if (($page +$num1) <= $maxPage) {
		$endPage = ($page +$num1);
	} else {
		$endPage = $maxPage;
	}
	if ($page < ceil($rows / $limit)){ 
		$endMore = '&nbsp;<a href="'. $_SERVER['PHP_SELF']. '?page='. ($page +1). '&limit='. $limit. $parameters. '">次&rsaquo;&rsaquo;</a>';
	}
	for($i = $startPage; $i <= $endPage; $i++){
		if($page == $i){
			$page_link .= '&nbsp;<span style="font-Size:120%;">'. $i. '</span>';
		}else{
			$page_link .= '&nbsp;<a href="'. $_SERVER['PHP_SELF']. '?page='. $i. '&limit='. $limit. $parameters. '">'. $i. '</a>';
		}
	}
	$page_link = $startMore. $page_link. $endMore;
	return $page_link;
}

/*
function tep_pager2($page, $rows, $limit) {
	$page_max = ceil($rows / $limit);
	if ($page_max == 1) return false;
	if ($page > 1) $lsaquo = '<a href="'. $_SERVER['PHP_SELF']. '?page='. ($page -1). '">&lsaquo;&lsaquo;&nbsp;</a>';
	if ($page < $page_max) $rsaquo ='<a href="'. $_SERVER['PHP_SELF']. '?page='. ($page +1). '">&nbsp;&rsaquo;&rsaquo;</a>';
	for ($i = 1; $i <= $page_max; $i++) {
		$selected = ($i == $page) ? ' selected' : '';
		$pager.= '<option value="'. $i. '"'. $selected.'>'. $i. '</option>';
	}
$URL = $_SERVER['PHP_SELF'];
$pager = <<< EOF
<form name="pages" action="$URL" method="get">
$lsaquo &nbsp;&nbsp;
<select name="page" onChange="this.form.submit();"> $pager </select>
&nbsp;/&nbsp; $page_max ページ&nbsp;&nbsp; $rsaquo
</form>
EOF;
	return $pager;
}
*/
function tep_pager2($page, $rows, $limit, $pager_array ='') {
	if ($pager_array) {
		foreach ($pager_array as $varname) {
			if($_GET[$varname]) ${$varname} = '&'. $varname. '='. $_GET[$varname];
			$parameters .= ${$varname};
			$parameters1 .= tep_draw_hidden_field($varname, $_GET[$varname]);
		}
	}
	$page_max = ceil($rows / $limit);
	if ($page_max == 1) return false;
	if ($page > 1) $lsaquo = '<a href="'. $_SERVER['PHP_SELF']. '?page='. ($page -1). $parameters. '">&lsaquo;&lsaquo;&nbsp;</a>';
	if ($page < $page_max) $rsaquo ='<a href="'. $_SERVER['PHP_SELF']. '?page='. ($page +1). $parameters. '">&nbsp;&rsaquo;&rsaquo;</a>';
	for ($i = 1; $i <= $page_max; $i++) {
		$selected = ($i == $page) ? ' selected' : '';
		$pager.= '<option value="'. $i. '"'. $selected.'>'. $i. '</option>';
	}
	$URL = $_SERVER['PHP_SELF'];
$pager = <<< EOF
<form name="pages" action="$URL" method="get">
$lsaquo &nbsp;&nbsp;
<select name="page" onChange="this.form.submit();"> $pager </select>
$parameters1
&nbsp;/&nbsp; $page_max ページ&nbsp;&nbsp; $rsaquo
</form>
EOF;
	return $pager;
}

// paging_array-parameters_pull_down
function tep_pager_pull_down($pager_parameters_array, $pager_limit_array) {
	$return = tep_draw_form('pager_limit', $_SERVER['PHP_SELF'], 'get');
	$return .= tep_draw_pull_down_menu('limit', $pager_limit_array, $_GET['limit'], 'onChange="this.form.submit();"');
	$return .= tep_draw_hidden_field('page', '1');
	$parameters = '';
	foreach ($pager_parameters_array as $varname) {
		if($_GET[$varname]) {
			$return .= tep_draw_hidden_field($varname, $_GET[$varname]);
		}
	}
	$return .= '</form>';
	return $return;
}

// paging_array-parameters_link
function tep_pager_link($name, $value='', $parameter_name, $pager_parameters_array) {
	$parameters = '';
	if ($_GET['limit']) $parameters .= '&limit='. $_GET['limit'];
	if ($value) $parameters .= '&'. $name. '='. $value;
	foreach ($pager_parameters_array as $varname) {
		if(($_GET[$varname]) && ($varname != $name)) ${$varname} = '&'. $varname. '='. $_GET[$varname];
		$parameters .= ${$varname};
	}
	//$page_link .= '<a href="'. $_SERVER['PHP_SELF']. '?'. $parameter_name. '='. $parameter_value. '&limit='. $limit. $parameters. '">'. $parameter_name. '</a>';
	$page_link .= '<a href="'. $_SERVER['PHP_SELF']. '?page=1'. $parameters. '">'. $parameter_name. '</a>';
	return $page_link;
}

// paging_array-parameters_orderby
function tep_pager_orderby($name, $value='', $parameter_name, $pager_parameters_array) {
	$return = ($_GET[$name] != $value)? tep_pager_link($name, $value, $parameter_name, $pager_parameters_array) : $parameter_name;
	if ($_GET[$name] == $value) {
		$return = ($_GET['by'] == 'asc')? tep_pager_link('by', 'desc', $parameter_name. '▽', $pager_parameters_array) : tep_pager_link('by', 'asc', $parameter_name. '△', $pager_parameters_array);
	}
	return $return;
}

function tep_pager_orderby1($name, $value='', $pager_array) {
	$return = ($_GET[$name] != $value)? tep_pager_link($name, $value, '▼', $pager_array) : '';
	if ($_GET[$name] == $value) {
		$return .= ($_GET['by'] == 'asc')? tep_pager_link('by', 'desc', '▽', $pager_array) : tep_pager_link('by', 'asc', '△', $pager_array);
	} 
	return $return;
}

// paging_array-parameters_form
function tep_pager_form($name, $pager_parameters_array) {
	$return = tep_draw_form($name, $_SERVER['PHP_SELF'], 'get');
	$return .= tep_draw_hidden_field('page', '1');
	$return .= tep_draw_hidden_field('limit', $_GET['limit']);
/*
	foreach ($pager_parameters_array as $varname) {
		if($_GET[$varname]) {
			$return .= tep_draw_hidden_field($varname, $_GET[$varname]);
		}
	}
*/
	//$return .= '</form>';
	return $return;
}

function tep_pager_num($page, $limit, $pager_rows, $rows) {
	$page = ($page + 1);
	$limit = ((($limit * $_GET['page']) - $limit) + $rows);
	return "全". $pager_rows. "件中". $page. "～". $limit. "件を表示"; 
}

function tep_parameters_href($pager_parameters_array){
	$parameters = '';
	foreach ($pager_parameters_array as $varname) {
		if($_GET[$varname]) ${$varname} = '&'. $varname. '='. $_GET[$varname];
		$parameters .= ${$varname};
	}
	if ($_GET['page']) $page = 'page='. $_GET['page'];
	if ($_GET['limit']) $limit = '&limit='. $_GET['limit'];
	return $page. $limit. $parameters;
}

function tep_parameters_href1($pager_array){
	foreach ($pager_array as $varname) {
		if($_GET[$varname]) ${$varname} = '&'. $varname. '='. $_GET[$varname];
		$parameters .= ${$varname};
	}
	return $parameters;
}

////
// The CSS background wrapper function
	function tep_bg_div($image, $width, $height, $parameters = '') {
		if ( (empty($src) || ($src == DIR_WS_IMAGES)) && (IMAGE_REQUIRED == 'false') ) {
			return false;
		}
		$background = '<div style=" background: url(' . tep_output_string($image) . ') no-repeat left top; width:'. $width. 'px; height:'. $height. 'px; ';

		if (tep_not_null($parameters)) $background .= ' ' . $parameters;

		$background .= '">';

		return $background;
	}

	function tep_bg_x_div($image, $width, $height, $parameters = '') {
		if ( (empty($src) || ($src == DIR_WS_IMAGES)) && (IMAGE_REQUIRED == 'false') ) {
			return false;
		}
		$background = '<div style=" background: url(' . tep_output_string($image) . ') repeat-x left bottom; width:'. $width. 'px; height:'. $height. 'px; ';

		if (tep_not_null($parameters)) $background .= ' ' . $parameters;

		$background .= '">';

		return $background;
	}

////
// Output a separator either through whitespace, or with an image define (Separator Clear: Both)
	function tep_space($height = '1', $width = '100%') {
		$return =	'<div style="clear: both; width: '. $width. '; height:'. $height. 'px; overflow: hidden;"><p style="display: none;">tep_space</p></div>';
		return $return;
	}

////
// 全角単位で文字数を取得
	function tep_zen_len($str){
		// 内部エンコーディングが utf-8 の場合
		return mb_strwidth($str, 'utf-8') / 2;
	}

	function tep_get_unixtime($raw_date) {
		$year = substr($raw_date, 0, 4);
		$month = (int)substr($raw_date, 5, 2);
		$day = (int)substr($raw_date, 8, 2);
		$hour = (int)substr($raw_date, 11, 2);
		$minute = (int)substr($raw_date, 14, 2);
		$second = (int)substr($raw_date, 17, 2);
		return mktime($hour, $minute, $second, $month, $day, $year);
	}

// including japanese?
function tep_including_japanese($str = '') {
	$japanese = false;
	if (mb_ereg('[ぁ-ん]', $str)) $japanese = true; //ひらがなが含まれる
	if (mb_ereg('[ァ-ヶ]', $str)) $japanese = true; //カタカナが含まれる
	if (!$japanese) { //ひらがなもカタカナも含まれない
		return false;
	} else {
		return true;
	}
}

function tep_validate_tel($tel) {
	$tel = mb_convert_kana($tel, "n", "UTF-8");
	$tel = preg_replace('/[^\d]+/', '', $tel);
	if ('0' === $tel{0} && (10 == strlen($tel) || 11 == strlen($tel))) {
		$valid_tel = true;
	} else {
		$valid_tel = false;
	}
	return $valid_tel;
}

function tep_ymjw() {
	$week_array = array('日', '月', '火', '水', '木', '金', '土');
	$ymjw = date("Y年m月j日"). '（'. $week_array[date("w")]. '）';
	return $ymjw;
}

function tep_ymjwhis() {
	$week_array = array('日', '月', '火', '水', '木', '金', '土');
	$ymjwhis = date("Y年m月j日"). '（'. $week_array[date("w")]. '）'. date("H時i分s秒");
	return $ymjwhis;
}

function tep_remove_javascript($str='') {
	$str = strip_tags($str);
	$str = preg_replace('/javascript/i', '', preg_replace('/[\x00-\x20\x22\x27]/', '', $str)); 
	return $str;
}

function tep_remove_specific_elements($str='') {
	$str = preg_replace('!<style.*?>.*?</style.*?>!is', '', $str) ;
	$str = preg_replace('!<script.*?>.*?</script.*?>!is', '', $str) ;
	return $html ;
}

function htmlpurifier($html = '') {
	require_once(DIR_WS_INCLUDES. '/htmlpurifier/library/HTMLPurifier.auto.php'); //「library」フォルダがある場所を指定する
	$config = HTMLPurifier_Config::createDefault();
	$config->set('Core.Encoding', 'UTF-8');
	$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
	$purifier = new HTMLPurifier();
	$clean_html = $purifier->purify($html);
	return $clean_html;
}

function tep_setcookie($val) {
	$timeout = time() +30 *86400;
	$host = parse_url(HTTP_SERVER);
	//setcookie("jfaa", $val, $timeout, '/');
	setcookie("jfaa", $val, $timeout, '/', $host['host']);
}
function tep_setcookie_renew($cookie, $val) {
	$cookie[0] = $val;
	$val = join(",", $cookie);
	tep_setcookie($val);
}
function tep_cookie() {
	$cookie = explode(",", $_COOKIE["jfaa"]);
	return $cookie;
}

function tep_setcookie_thirdparty($val, $host) {
	$timeout = time() +30 *86400;
	setcookie("jfaa", $val, $timeout, '/', $host);
}


/*
 * simplexml形式を連想配列にする。
 * $sxml = simplexml_load_string($xml);
 * $retArray = xml2array($sxml);
 */
function xml2array(&$sxml, $isRoot = true){
	if ($isRoot)
		return array($sxml->getName() => array(xml2array($sxml, false)));
	$r = array();
	foreach($sxml->children() as $cld){
		$a = &$r[(string)$cld->getName()];
		$a = &$a[count($a)];
		if (count($cld->children()) == 0)
			$a['_value'] = (string)$cld;
		else
			$a = xml2array($cld, false);
		foreach($cld->attributes() as $at)
			$a['_attr'][(string)$at->getName()] = (string)$at;
	}
	return $r;
}

function tep_trim($str){
	$str = preg_replace('/^[ 　]*(.*?)[ 　]*$/u', '$1', $str);
	return $str;
}

function tep_get_date_list($name, $date, $parameters = '') {
	if (!$date) $date = date('Y-m-d');
	$selected = substr($date, 0, 4);
	$y = date(Y) -1;
	while ($y <= date(Y)) {
		$array[] = array('id' => $y, 'text' => $y);
		$y++;
	}
	$selected1 = (int)substr($date, 5, 2);
	$m = 1;
	while ($m <= 12) {
		$array1[] = array('id' => sprintf("%02d",$m), 'text' => $m);
		$m++;
	}
	$selected2 = (int)substr($date, 8, 2);
	$d = 1;
	while ($d <= 31) {
		$array2[] = array('id' => sprintf("%02d",$d), 'text' => $d);
		$d++;
	}
	$return = tep_draw_pull_down_menu($name. '_y', $array, $selected, $parameters);
	$return.= tep_draw_pull_down_menu($name. '_m', $array1, $selected1, $parameters);
	$return.= tep_draw_pull_down_menu($name. '_d', $array2, $selected2, $parameters);
	return $return;
}

function tep_hash($str, $algo = 'ripemd320'){
	return hash($algo, $str);
}

?>
