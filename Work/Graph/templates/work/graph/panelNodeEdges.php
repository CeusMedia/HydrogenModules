<?php

$nodeIndex	= array();
foreach( $nodes as $node )
	$nodeIndex[$node->nodeId]	= $node->label ? $node->label : $node->ID;

$listEdgesTo	= '<small class="muted"><em>None.</em></small>';
if( $edgesTo ){
	foreach( $edgesTo as $edge ){
		$listEdgesTo	= array();
		$trans			= ' -> '.$nodeIndex[$edge->toNodeId];
		$label			= $edge->label.'<br/><small class="muted">'.$trans.'</small>';
		$link			= UI_HTML_Tag::create( 'a', $label, array(
			'href'	=> './work/graph/edge/'.$edge->edgeId.'/'.$nodeId
		) );
		$listEdgesTo[]	= UI_HTML_Tag::create( 'li', $link );
	}
	$listEdgesTo	= UI_HTML_Tag::create( 'ul', $listEdgesTo );
}

$listEdgesFrom	= '<small class="muted"><em>None.</em></small>';
if( $edgesFrom ){
	foreach( $edgesFrom as $edge ){
		$listEdgesFrom	= array();
		$trans			= $nodeIndex[$edge->fromNodeId].' -> ';
		$label			= $edge->label.'<br/><small class="muted">'.$trans.'</small>';
		$link			= UI_HTML_Tag::create( 'a', $label, array(
			'href'	=> './work/graph/edge/'.$edge->edgeId
		) );
		$listEdgesFrom[]	= UI_HTML_Tag::create( 'li', $link );
	}
	$listEdgesFrom	= UI_HTML_Tag::create( 'ul', $listEdgesFrom );
}

return '
<div class="content-panel">
	<h4>Edges on this node</h4>
	<div class="content-panel-inner">
		<h5>Outgoing edges</h5>
		'.$listEdgesTo.'
		<h5>Incoming edges</h5>
		'.$listEdgesFrom.'
	</div>
</div>
';
