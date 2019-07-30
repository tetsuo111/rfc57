<?php
//require('includes/application_top.php');
//require_once('PEAR.php');
//phpinfo();

$dbh = new PDO("mysql:dbname=".DB_DATABASE.";host=".DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
$dbh->query("SET NAMES utf8;");



//------------------------------------------------------------------------
// 暫定
//------------------------------------------------------------------------
//error_reporting(E_ERROR | E_WARNING | E_PARSE);

//header("Access-Control-Allow-Origin:*");
/*
function api_response($status=200, $message='success', $data=array()) {
//	http_response_code($status);
//	header($message, true, $status);
	$data['status']  = $status;
	$data['message'] = $message;
	echo json_encode($data);
}
*/


//------------------------------------------------------------------------
// データベース関連
//------------------------------------------------------------------------
function execute () {
	global $dbh;

	// 可変引数の処理
	$args  = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();
	$sql   = array_shift($args);
	$param = array();
	foreach( $args as $buf ) {
		if (is_array($buf)) $param = array_merge($param, $buf);
	}

	$sql = preg_replace('/;\s*$/', '', $sql);
	$sth = $dbh->prepare($sql);

	// 名前付きプレースホルダに対応するフォームデータをバインド
//	preg_match_all('/:(\w+)(?=[, );\n]|$)/',  $sql, $matches);
	preg_match_all('/[^:]:(\w+)/',  $sql, $matches);
	for ($i=0; $i< count($matches[1]); $i++) {
		$key = $matches[1][$i];
		if (!array_key_exists($key, $param) || $param[$key] === '') {
			$sth->bindValue(":$key", '', PDO::PARAM_NULL);
		} else {
			$sth->bindValue(":$key", $param[$key]);
		}
	}
	$sth->execute();


	if ($sth->errorCode() != '0000') trigger_error('<' . $sth->errorCode() . '> SQL:"' .$sql. '"' , E_USER_WARNING);
	return $sth;
}

// 複数行を連想配列で取得
function recordset () {
	$sth = execute(func_get_args());
	$sth->setFetchMode(PDO::FETCH_ASSOC);
	return $sth->fetchAll();
}

// 単一行を連想配列で取得
function query () {
	$sth = execute(func_get_args());
	$sth->setFetchMode(PDO::FETCH_ASSOC);
	return $sth->fetch();
}

// 単一値をスカラ値で取得
function queryValue () {
	$sth = execute(func_get_args());
	$sth->setFetchMode(PDO::FETCH_NUM);
	$row = $sth->fetch();
	return $row[0];
}

// 単一列複数行を配列で取得
function queryArray () {
	$sth = execute(func_get_args());
	$sth->setFetchMode(PDO::FETCH_NUM);
	
	$ret=array();
	while ($row = $sth->fetch()){
		$ret[] = $row[0];
	}
	return $ret;
}
// 単純なINSERT文を実行
//   insert('tablename', 'field1 field2 field3', $in, $acc);
function insert() {
	$args  = func_get_args();
	$table = array_shift($args);
	$field = arrange4sql(array_shift($args));
	$sth   = execute(array_merge((array)"INSERT INTO $table($field[field]) VALUES($field[value])", $args));
//	return queryValue("SELECT LAST_INSERT_ID()"); // MySQLでのみ有効
}

// 単純なUPDATE文を実行
//   update('tablename', 'field1 field2 field3', 'key1 key2', $in, $acc);
function update() {
	$args  = func_get_args();
	$table = array_shift($args);
	$field = arrange4sql(array_shift($args));
	$where = arrange4sql(array_shift($args));
	$sth   = execute(array_merge((array)"UPDATE $table SET $field[equal] WHERE $where[where]", $args));
	return $sth;
}

// 単純なDELETE文を実行
//   delete('tablename', 'key1 key2', $in, $acc);
function delete() {
	$args  = func_get_args();
	$table = array_shift($args);
	$where = arrange4sql(array_shift($args));
	$sth   = execute(array_merge((array)"DELETE FROM $table WHERE $where[where]", $args));
	return $sth;
}


// 上記３関数の下請け
function arrange4sql ($list) {
	$equal = preg_replace('/(\w+)/', '$1 = :$1,', $list);
	$equal = preg_replace('/,\s*$/',  '', $equal);
	$value = preg_replace('/\w+ = /', '', $equal);
	$field = preg_replace('/:/',      '', $value);
	$where = preg_replace('/(\w+)/', '$1 = :$1 AND', $list);
	$where = preg_replace('/AND\s*$/',  '', $where);
	return array(
		'equal' => $equal,
		'value' => $value,
		'field' => $field,
		'where' => $where
	);
}

?>
