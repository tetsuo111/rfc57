<?php

function tep_cookie_set($name, $value, $directory='/', $domain=DOMAIN){
	return setcookie($name, $value, time() + (60*60*24*90), $directory, $domain);//90days
}

function tep_cookie_del($name, $value='', $directory='/', $domain=DOMAIN){
	return setcookie($name, $value, time() - (60*60*24*90), $directory, $domain);
}

?>