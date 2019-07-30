<?php
//common --------------------------------------------------

function own_common($str='') {
	//common
	$str = str_replace('[/own]', '</own>', $str);
	$str = preg_replace('#\[own_(.*?)\]#i', '<own_$1>', $str);
	$str = preg_replace('#\[\/own_(.*?)\]#i', '</own_$1>', $str);

	//common_p_delete
	$str = str_replace('<P>', '<p>', $str);
	$str = str_replace('</P>', '</p>', $str);
	$str = preg_replace('#\<p\>\<own_(.*?)\>\</own\>\<\/p\>#i', '<own_$1></own>', $str);

	//text
	$str = str_replace('<own_text_pt>', '<div class="point">', $str);
	$str = str_replace('</own_text_pt>', '</div>', $str);
	$str = str_replace('<own_text_em>', '<div class="emphasis">', $str);
	$str = str_replace('</own_text_em>', '</div>', $str);

/*
	$str = preg_replace('#\<own_text_color=(.*?)\>#i', '<div style="color: $1;">', $str);
	$str = str_replace('</own_text_color>', '</div>', $str);
	$str = preg_replace('#\<own_text_size=(.*?)\>#i', '<div style="font-size: $1;">', $str);
	$str = str_replace('</own_text_size>', '</div>', $str);
*/

	//additional

	//p_delete
	$str = str_replace('<p></p>', '', $str);
	$str = str_replace('<p>&nbsp;</p>', '', $str);

	return $str;
}

function own_all_delete($str='') {
	//common
	$str = str_replace('[/own]', '', $str);
	$str = preg_replace('#\[own_(.*?)\]#i', '', $str);
	$str = preg_replace('#\[\/own_(.*?)\]#i', '', $str);

	//p_delete
	$str = str_replace('<P>', '<p>', $str);
	$str = str_replace('</P>', '</p>', $str);
	$str = str_replace('<p></p>', '', $str);
	$str = str_replace('<p>&nbsp;</p>', '', $str);

	return $str;
}

// /admin_2048 /admin_2090で使用　アップデート後削除 ---------->
function own_p_delete($str='') {
	$str = str_replace('<p><own_', '<own_', $str);
	$str = str_replace('<P><own_', '<own_', $str);
	$str = str_replace('</own></p>', '</own>', $str);
	$str = str_replace('</own></P>', '</own>', $str);
	return $str;
}
// <---------- /admin_2048 /admin_2090で使用　アップデート後削除


//own2048 --------------------------------------------------
function own_2048_movie($str='', $img='') { //[own_2048_movie=http://www.g-stream.net/vl/0/plan10.html][/own]
	$str = preg_replace('#\<own_2048_movie=(.*?)\>\</own\>#i', '<div class="own_2048_movie"><a rel="external" href="$1"><img src="'. $img. '" border="0" alt="ムービー再生"></img></a></div>', $str);
	return $str;
}

function own_2048_movie2($str='') { //[own_2048_movie=http://www.g-stream.net/vl/0/plan10.html&own_2048_movie_img=http://test.japanfaa.com/vl_btn_s.jpg][/own]
	$str = preg_replace('#\<own_2048_movie=(.*?)\>\</own\>#i', '<div class="own_2048_movie"><a rel="external" href="$1" border="0" alt="ムービー再生"></img></a></div>', $str);
	$str = str_replace('&own_2048_movie_img=', '"><img src="', $str);
	$str = str_replace('&amp;own_2048_movie_img=', '"><img src="', $str);
	return $str;
}

function own_2048_movie2ne($str='') { //not external Gmap onload被り
	$str = preg_replace('#\<own_2048_movie=(.*?)\>\</own\>#i', '<div class="own_2048_movie"><a target="_blank" href="$1" border="0" alt="ムービー再生"></img></a></div>', $str);
	$str = str_replace('&own_2048_movie_img=', '"><img src="', $str);
	$str = str_replace('&amp;own_2048_movie_img=', '"><img src="', $str);
	return $str;
}

function own_test_101129($str='') { // http://www.liveface.jp/blog/playChoko1122_1.js [own_test_101129=playChoko1122_1.js][/own]
	$str = preg_replace('#\<own_test_101129=(.*?)\>\</own\>#i', '<script src="http://www.liveface.jp/blog/$1" type="text/javascript"></script>', $str);
	return $str;
}

?>
