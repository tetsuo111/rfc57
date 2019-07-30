<?php 
require('includes/application_top.php');

if(($_COOKIE['master_id']) && ($_COOKIE['master_permission']) && (empty($_COOKIE['admin_id']))){
	$master_id = $_COOKIE['master_id'];
	$head_master_name = $_COOKIE['master_name'].'様';
}else{
	//$head_master_name = 'ゲスト様';
	tep_redirect('logout.php', '', 'SSL');
}


/*============================
　全体設定
============================*/
//飛び先設定
$form_action_to  = basename($_SERVER['PHP_SELF']);

$data_num = 16;
$post_cis = $_POST['cisid'];

//子要素作成関数
function zzGetObj($data){
	
	$ch = '';
	$res_ar = '';
	foreach($data AS $k => $v){
		if(is_array($v)){
			$aa = zzGetObj($v);
			$ch[] = (object)array('name'=>(string)$k,'children'=>$aa);
		}else{
			$ch[] = (object)array('name'=>$v);
		}
	}
	return $ch;
}

/*-------- 系列表示 --------*/
if($_POST['act'] == 'process'){
	
	//▼エラーチェック　＞　キャンセル対応
	$err = false;
	
	//ロケールを設定する
	setlocale(LC_ALL, 'ja_JP.UTF-8');
	$file = 'testtttt.csv';

	//ファイルの内容を取得する
	$data = file_get_contents($file);
	if($data == false){
		$err = true;
		$err_text = '<span class="input_alert">ファイルが読み込めません</span>';
	}

	if($err == false){
		
		//▼データ取得
		$data = mb_convert_encoding($data, 'UTF-8', 'SJIS-win');	//内容をUTF-8に変換
		$temp = tmpfile();											//tempファイルを作成する

		//▼ファイル操作
		fwrite($temp, $data);										//エンコードした内容を一時ファイルに書き込む
		rewind($temp);

		//▼初期化
		$i_c    = 0;	//データエラーの数
		$i_d    = 0;	//フォーマットエラーの数
		$i_fget = 0;	//現在のデータ行数
		
		while (($d2 = fgetcsv($temp)) !== FALSE) {
			
			//▼最初の一行を無視する
			if($i_fget != 0){
				
				$err_c = false;
				
				//データの数を数える
				if(count($d2) != $data_num){
					$err_c = true;
					$err_text = '<span class="input_alert">ファイルのフォーマットが正しくありません</span>';
					$i_d++;
					
				}else{
					
					/*----- DB登録 -----*/
					if($err_c == false){
						
						/*
							0	CIS自身ID
							1	CIS紹介者ID
						*/
						$syoukai_ar[$d2[1]][$d2[0]] = $d2[0];
						
					}else{
						
						//▼エラー表示用
						$err_ar[$i_fget]   =  $d2;
						$i_c++;
					}
				}
			}
			
			$i_fget++;
		}
		
		ksort($syoukai_ar);
		/*----- DB登録 -----*/
		//エラーがなくデータがある場合
		if(($i_c == 0)AND($i_d == 0)AND($i_fget >1)){
			
			$err_text = '<span class="input_alert">顧客データを登録しました</span>';
			
			/*----- 系列作成 -----*/
			foreach($syoukai_ar AS $ksyo => $syo_ar){
				
				//▼紹介者ID
				$cis_id = $ksyo;
				
				//▼紹介者の紹介者の配列
				$inv_ar = '';
				$inv_ar = array_filter ($syoukai_ar,function($value) use ($cis_id){
					return (in_array($cis_id,$value) !== false);
				});
				
				//▼配列をまとめる
				if($inv_ar){
					//▼紹介者の紹介ID
					$invid = key($inv_ar);
					
					//登録がなければ新規追加
					$jjj_ar[$invid][$ksyo] = $syo_ar;
				}
			}
			
			ksort($jjj_ar);
			
			$aa_id = '24000';
			$inv_ar = array_filter ($syoukai_ar,function($value) use ($aa_id){
				return (in_array($aa_id,$value) !== false);
			});
			
			
			//▼表示データの編集
			$a       = ($post_cis)? $post_cis : '99999999000';
			$bbb     = (object)array('name'=>$a,'children'=>zzGetObj($jjj_ar[$a]));
			$jsonAAA = json_encode($bbb);
			

		}else if($i_c > 0){
			
			$err_text = '<span class="input_alert">エラーがあります</span>';
		}
	}
}


$input_form = '<form action="'.$form_action_to.'" name="input_form" method="POST" enctype="multipart/form-data">';
$input_form.= '<input type="hidden" name="act"    value="process">';
$input_form.= 'CISID：<input type="text"   name="cisid" size="10" value="'.$post_cis.'" pattern="[0-9]+$">';
$input_form.= '<div style="margin-top:20px;"><input type="submit" name="act_send" value="データを登録する"></div>';
$input_form.= '</form>';

?>
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
	<link rel="stylesheet" type="text/css" href="../css/common.css" media="all">
	<link rel="stylesheet" type="text/css" href="../css/master.css" media="all">
	
	<style>
		.text_remarks{width:400px; height:180px; resize:none;overflow:auto;}
		.tx_long{width:100%;}
		.in_tel{width:80px;}
		
		.list_table{width:100%;}
		.list_table td{vertical-align:top;}
		
		.ng{background:#FAA;}
		.dt_ng{color:#FF0;font-weight:800;}
		input[type="submit"] {cursor:pointer;}
		.overlay{background-color:#EEE;}
		
		.node {	cursor: pointer;}
		.node circle {
			fill: #fff;
			stroke: steelblue;
			stroke-width: 1.5px;
		}
		
		.node text {
			font-size:12px; 
			font-family:Meiryo;
		}
		
		.link {
			fill: none;
			stroke: #ccc;
			stroke-width: 1.5px;
		}

		.templink {
			fill: none;
			stroke: red;
			stroke-width: 3px;
		}

		.ghostCircle.show{display:block;}
		.ghostCircle, .activeDrag .ghostCircle{display: none;}
	</style>
	<script src="../js/jquery-3.2.1.min.js" charset="UTF-8"></script>
	<script src="../js/d3/d3.v3.min.js"     charset="UTF-8"></script>
</head>
<body>
<div id="wrapper">
	
	<div id="header">
		<?php require('inc_master_header.php');?>
	</div>
	<div id="head_line">
		<?php require('inc_master_head_line.php');?>
	</div>

	<div id="content">
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
				
				<h2>CSVツリー表示</h2>
				<div class="part">
					<div class="spc20">
						<?php echo $input_form;?>
						<?php echo $err_text;?>
					</div>
					<div class="spc20">
						<a href="">クリア</a>
						<?php echo $err_table;?>
					</div>
					<div style="margin-bottom:100px;">
						<div id="tree-container"></div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="clear_float"></div>
	</div>
	
	<div id="footer">
		<?php require('inc_master_footer.php'); ?>
	</div>
<script>

/*----- 変数定義 -----*/
//▼元データ
var AAA = '{"name":"flare"}';
var BBB = '<?php echo $jsonAAA;?>';
var ttjson = (BBB)? BBB:AAA;

ttjson = ttjson.replace(/\s+/g, "");
ttjson = ttjson.replace(/\r?\n/g, "");
var treeData = JSON.parse(ttjson);

//▼接点の合計と最大ラベル長さを計算する
// Calculate total nodes, max label length
var totalNodes     = 0;
var maxLabelLength = 0;


//▼ドラッグドロップ変数
// variables for drag/drop
var selectedNode = null;
var draggingNode = null;

//▼pan変数
// panning variables
var panSpeed    = 200;
var panBoundary = 20; // Within 20px from edges will pan when dragging. ▼ドラッグ時に端から20pxの領域がpanされる

//▼その他変数
// Misc. variables
var i = 0;
var duration = 750;
var root;

//▼描画領域の設定
// size of the diagram
var viewerWidth  = $(document).width();
var viewerHeight = $(document).height();

//▼表示するツリーを設定
var tree = d3.layout.tree().size([viewerHeight, viewerWidth]);


//▼後でnode pathが利用するための d3 対角投影を定義する
// define a d3 diagonal projection for use by the node paths later on.
var diagonal = d3.svg.diagonal().projection(function(d) {return [d.y, d.x];});


/*----- visit関数 -----*/
//▼すべてのnodeを通り抜けることによっていくつかの設定を行う再帰的支援関数
// A recursive helper function for performing some setup by walking through all nodes.
function visit(parent, visitFn, childrenFn) {
	if (!parent) return;
	
	//▼親要素を変数にして表示関数を指定　＞関数の内容は後で定義
	visitFn(parent);
	
	//▼親要素を変数にして子要素してい関数を指定　＞関数の内容は後で定義
	var children = childrenFn(parent);
	if (children) {
		var count = children.length;
		
		//▼子要素の数だけ繰り返し自分を呼び出す
		for (var i = 0; i < count; i++) {
			visit(children[i], visitFn, childrenFn);
		}
	}
}


//▼「maxLabelLength」を取得するためにvisit関数を呼び出す
// Call visit function to establish maxLabelLength
visit(treeData,
	function(d) {
		totalNodes++;
		maxLabelLength = Math.max(d.name.length, maxLabelLength);
	},
	function(d) {
		return d.children && d.children.length > 0 ? d.children : null;
	}
);


/*----- sort tree関数 -----*/
//▼node名に従ってツリーを並び替える関数
//各node要素のオブジェクトを変数に指定する
//各要素の中の"name"キーで並び変える
// sort the tree according to the node names
function sortTree() {
	tree.sort(function(a, b) {
		return b.name.toLowerCase() < a.name.toLowerCase() ? 1 : -1;
	});
}
	
//▼JSONが並び替え順になっていない場合に備えて　最初にツリーを並び替える
// Sort the tree initially in case the JSON isn't in a sorted order.
sortTree();



/*----- pan（平行移動）関数 -----*/
//▼Pan（平行移動）アクション、よりよく実装できる
// TODO: Pan function, can be better implemented.
function pan(domNode, direction) {
	var speed = panSpeed;
	
	if (panTimer) {
		clearTimeout(panTimer);
		translateCoords = d3.transform(svgGroup.attr("transform"));

		if (direction == 'left' || direction == 'right') {
			translateX = direction == 'left' ? translateCoords.translate[0] + speed : translateCoords.translate[0] - speed;
			translateY = translateCoords.translate[1];
		} else if (direction == 'up' || direction == 'down') {
			translateX = translateCoords.translate[0];
			translateY = direction == 'up' ? translateCoords.translate[1] + speed : translateCoords.translate[1] - speed;
		}
		
		//▼rootからみたマウスマウスの相対位置
		scaleX = translateCoords.scale[0];
		scaleY = translateCoords.scale[1];
		scale  = zoomListener.scale();
		
		//▼属性を追加
		svgGroup.transition().attr("transform", "translate(" + translateX + "," + translateY + ")scale(" + scale + ")");
		d3.select(domNode).select('g.node').attr("transform", "translate(" + translateX + "," + translateY + ")");
		
		zoomListener.scale(zoomListener.scale());
		zoomListener.translate([translateX, translateY]);
		
		panTimer = setTimeout(function() {pan(domNode, speed, direction);}, 50);
	}
}


/*----- zoom関数 -----*/
//▼拡大可能なツリー用に拡大機能を定義する
// Define the zoom function for the zoomable tree
function zoom() {
	svgGroup.attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")");
}


//▼scaleExtentsの中で制御されている"zoom"イベント
// define the zoomListener which calls the zoom function on the "zoom" event constrained within the scaleExtents
var zoomListener = d3.behavior.zoom().scaleExtent([0.1, 3]).on("zoom", zoom);



/*----- ドラッグの初期化 -----*/
function initiateDrag(d, domNode) {
	draggingNode = d;
	d3.select(domNode).select('.ghostCircle').attr('pointer-events', 'none');
	d3.selectAll('.ghostCircle').attr('class', 'ghostCircle show');
	d3.select(domNode).attr('class', 'node activeDrag');

	svgGroup.selectAll("g.node").sort(function(a, b) {		// select the parent and sort the path's　▼親を選択しパスを並び変える
		if (a.id != draggingNode.id) return 1;				// a is not the hovered element, send "a" to the back　▼aがは選択中の要素ではない場合、"a"を後ろに送る
		else return -1;									// a is the hovered element, bring "a" to the front　　▼aが選択中の要素の場合、aを前に持っていく
	});
	
	//▼もしノードに子要素がある場合にはリンクとノードを削除する
	// if nodes has children, remove the links and nodes
	if (nodes.length > 1) {
		// remove link paths
		links = tree.links(nodes);
		nodePaths = svgGroup.selectAll("path.link")
			.data(links, function(d) {
				return d.target.id;
			}).remove();
		// remove child nodes
		nodesExit = svgGroup.selectAll("g.node")
			.data(nodes, function(d) {
				return d.id;
			}).filter(function(d, i) {
				if (d.id == draggingNode.id) {
					return false;
				}
				return true;
			}).remove();
	}
	
	//▼親要素のリンクを削除
	// remove parent link
	parentLink = tree.links(tree.nodes(draggingNode.parent));
	svgGroup.selectAll('path.link').filter(function(d, i) {
		if (d.target.id == draggingNode.id) {
			return true;
		}
		return false;
	}).remove();

	dragStarted = null;
}


/*----- 描画開始 -----*/
//▼基になるbaseSvg画像を定義、書式設定用のクラスとzoomListnerを設定する
// define the baseSvg, attaching a class for styling and the zoomListener
var baseSvg = d3.select("#tree-container").append("svg")
	.attr("width"  ,viewerWidth)
	.attr("height" ,viewerHeight)
	.attr("class"  ,"overlay")
	.call(zoomListener);



/*----- ドラッグ時の挙動定義 -----*/
//▼nodeのdrag/drop挙動用のdrag listenerを定義する
// Define the drag listeners for drag/drop behaviour of nodes.
dragListener = d3.behavior.drag()
	.on("dragstart", function(d) {
		if (d == root) {
			return;
		}
		
		//▼ドラッグ開始処理
		dragStarted = true;
		nodes = tree.nodes(d);
		d3.event.sourceEvent.stopPropagation();
		//▼nodeドラッグ中のmouseoverイベントを抑制することはとても重要である。
		// it's important that we suppress the mouseover event on the node being dragged.
		
		//▼さもなければmouseoverイベントを吸収し基底にあるnodeを検出しなくなる。
		//Otherwise it will absorb the mouseover event and the underlying node will not detect it d3.
		
		//▼この属性を設定する
		//select(this).attr('pointer-events', 'none');
	})
	.on("drag", function(d) {
		//▼ドラッグ中の処理
		if (d == root) {
			return;
		}
		if (dragStarted) {
			domNode = this;
			initiateDrag(d, domNode);
		}
		
		//▼panを許可するために、マウスイベントのsvg containerに対する相対座標を取得する。
		// get coords of mouseEvent relative to svg container to allow for panning
		relCoords = d3.mouse($('svg').get(0));
		if (relCoords[0] < panBoundary) {
			panTimer = true;
			pan(this, 'left');
			
		} else if (relCoords[0] > ($('svg').width() - panBoundary)) {
			panTimer = true;
			pan(this, 'right');
			
		} else if (relCoords[1] < panBoundary) {
			panTimer = true;
			pan(this, 'up');
			
		} else if (relCoords[1] > ($('svg').height() - panBoundary)) {
			panTimer = true;
			pan(this, 'down');
			
		} else {
			try {
				clearTimeout(panTimer);
			} catch (e) {

			}
		}

		d.x0 += d3.event.dy;
		d.y0 += d3.event.dx;
		var node = d3.select(this);
		node.attr("transform", "translate(" + d.y0 + "," + d.x0 + ")");
		updateTempConnector();
	})
	.on("dragend", function(d) {
		//▼ドラッグ終了時の処理
		if (d == root) {
			return;
		}
		domNode = this;
		
		if (selectedNode) {
			
			//▼ここで親から要素を削除し、それを新しい子要素の中に追加する
			// now remove the element from the parent, and insert it into the new elements children
			var index = draggingNode.parent.children.indexOf(draggingNode);
			if (index > -1) {
				draggingNode.parent.children.splice(index, 1);
			}
			if (typeof selectedNode.children !== 'undefined' || typeof selectedNode._children !== 'undefined') {
				if (typeof selectedNode.children !== 'undefined') {
					selectedNode.children.push(draggingNode);
				} else {
					selectedNode._children.push(draggingNode);
				}
			} else {
				selectedNode.children = [];
				selectedNode.children.push(draggingNode);
			}
			
			//▼追加されたノードが展開されていることを明確にする。それによりユーザーは追加されたnodeが正しく動いていることがわかる。
			// Make sure that the node being added to is expanded so user can see added node is correctly moved
			expand(selectedNode);
			sortTree();
			endDrag();
		} else {
			endDrag();
		}
	});


//▼ドラッグ終了時関数
function endDrag() {
	selectedNode = null;
	d3.selectAll('.ghostCircle').attr('class', 'ghostCircle');
	d3.select(domNode).attr('class', 'node');
	
	//▼ここでmouseoverイベントを復元する　さもなければ、2回目のドラッグができなくなる
	// now restore the mouseover event or we won't be able to drag a 2nd time
	d3.select(domNode).select('.ghostCircle').attr('pointer-events', '');
	updateTempConnector();
	if (draggingNode !== null) {
		update(root);
		centerNode(draggingNode);
		draggingNode = null;
	}
}



/*----- node開閉時の挙動 -----*/
//▼node開閉用の支援関数
// Helper functions for collapsing and expanding nodes.

//▼nodeを閉じる
function collapse(d) {
	
	if (d.children) {
		d._children = d.children;
		d._children.forEach(collapse);
		d.children = null;
	}
}

//▼nodeを開く
function expand(d) {
	if (d._children) {
		d.children = d._children;
		d.children.forEach(expand);
		d._children = null;
	}
}

//▼円上にある場合
var overCircle = function(d) {
	selectedNode = d;
	updateTempConnector();
};

//▼円を外れる場合
var outCircle = function(d) {
	selectedNode = null;
	updateTempConnector();
};



//▼drag状態を示すために一時的な接続を更新する関数
// Function to update the temporary connector indicating dragging affiliation
var updateTempConnector = function() {
	var data = [];
	if (draggingNode !== null && selectedNode !== null) {
		// have to flip the source coordinates since we did this for the existing connectors on the original tree
		data = [{
			source: {
				x: selectedNode.y0,
				y: selectedNode.x0
			},
			target: {
				x: draggingNode.y0,
				y: draggingNode.x0
			}
		}];
	}
	var link = svgGroup.selectAll(".templink").data(data);

	link.enter().append("path")
		.attr("class", "templink")
		.attr("d", d3.svg.diagonal())
		.attr('pointer-events', 'none');

	link.attr("d", d3.svg.diagonal());

	link.exit().remove();
};

//▼clicked/dropped時にnodeを中心に移動するための関数です。
//そのため大量の子要素を閉じたり移動するときにもnodeが消えることはありません
// Function to center node when clicked/dropped so node doesn't get lost 
//when collapsing/moving with large amount of children.
function centerNode(source) {
	scale = zoomListener.scale();
	x = -source.y0;
	y = -source.x0;
	x = x * scale + viewerWidth / 2;
	y = y * scale + viewerHeight / 2;
	d3.select('g').transition()
		.duration(duration)
		.attr("transform", "translate(" + x + "," + y + ")scale(" + scale + ")");
	zoomListener.scale(scale);
	zoomListener.translate([x, y]);
}

//▼子要素切り替え関数
// Toggle children function
function toggleChildren(d) {
	if (d.children) {
		d._children = d.children;
		d.children = null;
	} else if (d._children) {
		d.children = d._children;
		d._children = null;
	}
	return d;
}

//▼クリックで子要素を切り替える
// Toggle children on click.
function click(d) {
	if (d3.event.defaultPrevented) return; // click suppressed　クリックを抑制
	d = toggleChildren(d);
	update(d);
	centerNode(d);
}


//▼表示を更新
function update(source) {
	//▼新しい高さを計算する。起源nodeの中の全ての子要素の数を数え、対応するツリーの高さを設定する。
	// Compute the new height, function counts total children of root node and sets tree height accordingly.
	
	//▼これにより、新しいnodeが可視化された時に見た目がつぶれる、またはnodeが削除されたときにレイアウトの見た目が希薄になるのを防ぐことができる。
	// This prevents the layout looking squashed when new nodes are made visible or looking sparse when nodes are removed.
	
	//▼こによりレイアウトがより一致した状態になる
	// This makes the layout more consistent.
	var levelWidth = [1];
	var childCount = function(level, n) {

		if (n.children && n.children.length > 0) {
			if (levelWidth.length <= level + 1) levelWidth.push(0);

			levelWidth[level + 1] += n.children.length;
			n.children.forEach(function(d) {
				childCount(level + 1, d);
			});
		}
	};
	
	childCount(0, root);
	var newHeight = d3.max(levelWidth) * 25; // 25 pixels per line  
	tree = tree.size([newHeight, viewerWidth]);
	
	//▼新しいtreeのレイアウトを計算する
	// Compute the new tree layout.
	var nodes = tree.nodes(root).reverse(),
		links = tree.links(nodes);
	
	//▼maxLabelLengthを元にlevel間の幅を設定する
	// Set widths between levels based on maxLabelLength.
	nodes.forEach(function(d) {
		d.y = (d.depth * (maxLabelLength * 10)); //maxLabelLength * 10px
		// alternatively to keep a fixed scale one can set a fixed depth per level
		// Normalize for fixed-depth by commenting out below line
		// d.y = (d.depth * 500); //500px per level.
	});
	
	//▼nodeを更新する
	// Update the nodes…
	node = svgGroup.selectAll("g.node")
		.data(nodes, function(d) {
			return d.id || (d.id = ++i);
		});
	
	//▼親の前の位置にあらゆる新しいノードをいれる
	// Enter any new nodes at the parent's previous position.
	var nodeEnter = node.enter().append("g")
		.call(dragListener)
		.attr("class", "node")
		.attr("transform", function(d) {
			return "translate(" + source.y0 + "," + source.x0 + ")";
		})
		.on('click', click);

	nodeEnter.append("circle")
		.attr('class', 'nodeCircle')
		.attr("r", 0)
		.style("fill", function(d) {
			return d._children ? "lightsteelblue" : "#fff";
		});

	nodeEnter.append("text")
		.attr("x", function(d) {
			return d.children || d._children ? -10 : 10;
		})
		.attr("dy", ".35em")
		.attr('class', 'nodeText')
		.attr("text-anchor", function(d) {
			return d.children || d._children ? "end" : "start";
		})
		.text(function(d) {
			return d.name;
		})
		.style("fill-opacity", 0);

	//▼周囲の半径にmouseoverを与えるための幻のnode
	// phantom node to give us mouseover in a radius around it
	nodeEnter.append("circle")
		.attr('class', 'ghostCircle')
		.attr("r", 30)
		.attr("opacity", 0.2) // change this to zero to hide the target area　目標の範囲を隠すためにはこの値を0にする
	.style("fill", "red")
		.attr('pointer-events', 'mouseover')
		.on("mouseover", function(node) {
			overCircle(node);
		})
		.on("mouseout", function(node) {
			outCircle(node);
		});

	//▼nodeが子要素を持っているかどうかを反映するように文字列を更新する
	// Update the text to reflect whether node has children or not.
	node.select('text')
		.attr("x", function(d) {
			return d.children || d._children ? -10 : 10;
		})
		.attr("text-anchor", function(d) {
			return d.children || d._children ? "end" : "start";
		})
		.text(function(d) {
			return d.name;
		});
	
	//▼子要素を持っていて、かつたたまれているかによって円の塗りを変更する
	// Change the circle fill depending on whether it has children and is collapsed
	node.select("circle.nodeCircle")
		.attr("r", 4.5)
		.style("fill", function(d) {
			return d._children ? "lightsteelblue" : "#fff";
		});
	
	//▼nodeを新しい位置に遷移する
	// Transition nodes to their new position.
	var nodeUpdate = node.transition()
		.duration(duration)
		.attr("transform", function(d) {
			return "translate(" + d.y + "," + d.x + ")";
		});
	
	//▼文字を表示する
	// Fade the text in
	nodeUpdate.select("text").style("fill-opacity", 1);
	
	//▼既存のnodeを親の新しい位置に遷移する
	// Transition exiting nodes to the parent's new position.
	var nodeExit = node.exit().transition()
		.duration(duration)
		.attr("transform", function(d) {
			return "translate(" + source.y + "," + source.x + ")";
		})
		.remove();

	nodeExit.select("circle").attr("r", 0);
	nodeExit.select("text").style("fill-opacity", 0);
	
	//▼つながりを更新する
	// Update the links…
	var link = svgGroup.selectAll("path.link")
		.data(links, function(d) {
			return d.target.id;
		});

	//▼あらゆるリンクを親の前の位置に格納する
	// Enter any new links at the parent's previous position.
	link.enter().insert("path", "g")
		.attr("class", "link")
		.attr("d", function(d) {
			var o = {
				x: source.x0,
				y: source.y0
			};
			return diagonal({
				source: o,
				target: o
			});
		});
	
	//▼遷移は新しい位置とつながる
	// Transition links to their new position.
	link.transition()
		.duration(duration)
		.attr("d", diagonal);
	
	//▼既存のノードを親の新しい位置に移動する
	// Transition exiting nodes to the parent's new position.
	link.exit().transition()
		.duration(duration)
		.attr("d", function(d) {
			var o = {
				x: source.x,
				y: source.y
			};
			return diagonal({
				source: o,
				target: o
			});
		})
		.remove();
	
	//▼状態遷移のために古い位置を隠す
	// Stash the old positions for transition.
	nodes.forEach(function(d) {
		d.x0 = d.x;
		d.y0 = d.y;
	});
}

//▼すべてのnodeを保持し、zoom Listenerが動作するグループを追加する
// Append a group which holds all nodes and which the zoom Listener can act upon.
var svgGroup = baseSvg.append("g");

//▼ルートを定義
// Define the root
root = treeData;
root.x0 = viewerHeight / 2;
root.y0 = 0;

//▼最初にツリーを配置し、root nodeを中心におく。
// Layout the tree initially and center on the root node.

update(root);
centerNode(root);
</script>
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>