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

/*
//子要素作成関数
function zzGetObj($data,$array){
	
	$ch = '';
	$res_ar = '';
	foreach($data AS $k => $v){
		if(is_array($v)){
			$aa = zzGetObj($v,$array);
			$ch[] = (object)array('name'=>(string)$array[$k],'pid'=>(string)$k,'children'=>$aa);
		}else{
			$ch[] = (object)array('name'=>(string)$array[$v],'pid'=>(string)$v);
		}
	}
	return $ch;
}
*/

/*----- データ取得 -----*/
//▼ポジションデータ
$query = tep_db_query("
	SELECT
		`p`.`fs_id`,
		`p`.`position_id`      AS `p_id`,
		`p`.`position_inviter` AS `inv`,
		CONCAT(`ui`.`user_name`,`ui`.`user_name2`) AS `name`
	FROM      `".TABLE_POSITION."`  AS `p`
	LEFT JOIN `".TABLE_USER_INFO."` AS `ui` ON `ui`.`user_id` = `p`.`user_id`
	WHERE `p`.`state` = '1'
	AND  ((`ui`.`state` = '1')OR(`ui`.`state` IS NULL))
	ORDER BY `p`.`position_id` ASC
");

if(tep_db_num_rows($query)){
	
	$jtree   = pMakeMapTreeArray();
	$jsonAAA = json_encode($jtree);
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
		input[type="submit"] {cursor:pointer;}
		.overlay{background-color:#EEE;}
		
		.node {	cursor: pointer;}
		.node circle {
			fill: #fff;
			stroke: steelblue;
			stroke-width: 3px;
		}
		
		.node text {
			font-size:12px; 
			font-family:Meiryo;
		}
		
		.link {
			fill: none;
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
				
				<h2>顧客組織図</h2>
				<div class="part">
					<div class="spc20">
						<a href=""><button type="button">再表示</button></a>
					</div>
					<div style="margin-bottom:100px;">
						<div class="spc20" id="tree-container"></div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="clear_float"></div>
	</div>
	
	<div id="footer">
		<?php require('inc_master_footer.php'); ?>
	</div>
</div>
<script>

/*----- 変数定義 -----*/
//▼元データ
var AAA = '{"name":"orner"}';
var BBB = '<?php echo $jsonAAA;?>';
var ttjson = (BBB)? BBB:AAA;

ttjson = ttjson.replace(/\s+/g, "");
ttjson = ttjson.replace(/\r?\n/g, "");
var treeData = JSON.parse(ttjson);

console.log(treeData);

//▼接点の合計と最大ラベル長さを計算する
// Calculate total nodes, max label length
var totalNodes     = 0;
var maxLabelLength = 0;


//▼pan変数
// panning variables
// Within 20px from edges will pan when dragging. ▼ドラッグ時に端から20pxの領域がpanされる
var panSpeed    = 200;
var panBoundary = 20; 

//▼その他変数
// Misc. variables
var i = 0;
var duration = 750;
var root;

var chNum = [];


//▼描画領域の設定
// size of the diagram
var viewerWidth  = $(document).width()  - 220;
var viewerHeight = $(document).height() - 340;

//▼表示するツリーを設定
var tree = d3.layout.tree()
		.size([viewerHeight, viewerWidth]);


//▼後でnode pathが利用するための d3 対角投影を定義する
// define a d3 diagonal projection for use by the node paths later on.
var diagonal = d3.svg.diagonal().projection(function(d) {return [d.y, d.x];});


/*----- visit関数 -----*/
//▼すべてのnodeを通り抜けることによっていくつかの設定を行う再帰的支援関数
// A recursive helper function for performing some setup by walking through all nodes.
function visit(parent, visitFn, childrenFn) {
	if (!parent) return;
	
	//▼親要素を変数にして表示関数を指定
	visitFn(parent);
	
	//▼親要素を変数にして子要素指定して関数を指定
	var children = childrenFn(parent);
	var numall= 0;
	if (children) {
		var count = children.length;
		numall+= count;
		
		//▼子要素の数だけ繰り返し自分を呼び出す
		for (var i = 0; i < count; i++) {
			numall+= visit(children[i], visitFn, childrenFn);
		}
	}
	
	parent.cnum = numall;
	return numall;
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
function zoom() {
	svgGroup.attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")");
}


//▼scaleExtentsの中で制御されている"zoom"イベント
var zoomListener = d3.behavior.zoom().scaleExtent([0.1, 3]).on("zoom", zoom);



/*----- 描画開始 -----*/
//▼基になるbaseSvg画像を定義、書式設定用のクラスとzoomListnerを設定する
// define the baseSvg, attaching a class for styling and the zoomListener
var baseSvg = d3.select("#tree-container").append("svg")
	.attr("width"  ,viewerWidth)
	.attr("height" ,viewerHeight)
	.attr("class"  ,"overlay")
	.call(zoomListener);


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
	
	// 25 pixels per line  
	var newHeight = d3.max(levelWidth) * 80;
	tree = tree.size([newHeight, viewerWidth]);
	
	// Compute the new tree layout.
	var nodes = tree.nodes(root).reverse(),
		links = tree.links(nodes);
	
	
	// Set widths between levels based on maxLabelLength.
	nodes.forEach(function(d) {
		d.y = (d.depth * (maxLabelLength * 10)); //maxLabelLength * 10px
	});
	
	// Update the nodes…
	node = svgGroup.selectAll("g.node")
		.data(nodes, function(d) {
			return d.id || (d.id = ++i);
		});
	
	//▼親の前の位置にあらゆる新しいノードをいれる
	// Enter any new nodes at the parent's previous position.
	//ドラッグイベントを削除
	//.call(dragListener)
	var nodeEnter = node.enter().append("g")
		.attr("class", "node")
		.attr("transform", function(d) {
			return "translate(" + source.y0 + "," + source.x0 + ")";
		})
		.on('click', click);
	
	
	//▼円の表示
	nodeEnter.append("circle")
		.attr('class', 'nodeCircle')
		.attr("r", 0)
		.style("fill", function(d) {
			return d._children ? "lightsteelblue" : "#fff";
		});
	
	//▼文字の表示
	nodeEnter.append("text")
		.attr("x", function(d) {
			return d.children || d._children ? -50 : -50;
		})
		.attr("dy", "-12px")
		.attr("transform", "rotate(15)")   
		.attr('class', 'nodeText')
		.text(function(d) {
			return d.name;
		})
		.style("fill-opacity", 0)
		.on("mouseover", function(d){d3.select(this).attr("fill","#666").style("font-size","12.5px");})
		.on("mouseout" , function(d){d3.select(this).attr("fill","#000").style("font-size","12px");});
	
	//▼子要素を持っていて、かつたたまれているかによって円の塗りを変更する
	// Change the circle fill depending on whether it has children and is collapsed
	node.select("circle.nodeCircle")
		.attr("r",function(d){return ((d.cnum * 1.3) > 20 )? 20:(d.cnum * 1.3) + 5;})
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
	
	// Fade the text in
	nodeUpdate.select("text")
	.style("fill-opacity", 1);

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
		})
		.style("stroke",      function(d){return (d.target.cnum > 0)? '#CCC':'#DEDEDE';})
		.style("stroke-width",function(d){return ((d.target.cnum * 1.3) > 20 )? 20 : (d.target.cnum * 1.3)+ 3;});
	
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


//▼ツリーの初期表示位置
function firstNode(source){
	scale = zoomListener.scale();
	
	//x = x * scale + viewerWidth / 2;
	x = 100;
	y = -source.x0;
	y = y * scale + viewerHeight / 2;
	d3.select('g').transition()
		.duration(duration)
		.attr("transform", "translate(" + x + "," + y + ")scale(" + scale + ")");
	zoomListener.scale(scale);
	zoomListener.translate([x, y]);
}


//▼ルートを定義
// Define the root
root = treeData;


root.x0 = viewerHeight / 2;
root.y0 = 0;


//▼最初にツリーを配置し、root nodeを開始位置におく。
// Layout the tree initially and put root node on start position.
update(root);
firstNode(root);

</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>