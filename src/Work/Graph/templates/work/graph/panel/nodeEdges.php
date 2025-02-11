<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$nodeIndex	= [];
foreach( $nodes as $node )
	$nodeIndex[$node->nodeId]	= $node->label ?: $node->ID;

$listEdgesIn	= '<small class="muted"><em>None.</em></small>';
if( $edgesIn ){
	$listEdgesIn	= [];
	foreach( $edgesIn as $edge ){
		$nodeFrom		= HtmlTag::create( 'small', $nodeIndex[$edge->fromNodeId].' -> ', ['class' => 'muted'] );
		$nodeTo			= HtmlTag::create( 'small', ' -> '.$nodeIndex[$edge->toNodeId], ['class' => 'muted'] );
		$label			= $nodeFrom.$edge->label.$nodeTo;
		$link			= HtmlTag::create( 'a', $label, [
			'href'	=> './work/graph/edge/'.$edge->edgeId.'/'.$nodeId,
		] );
		$key		= strtolower( $edge->label ).'_'.microtime( TRUE );
		$listEdgesIn[$key]	= HtmlTag::create( 'li', $link );
	}
	ksort( $listEdgesIn );
	$listEdgesIn	= HtmlTag::create( 'ul', $listEdgesIn, ['class' => 'not-unstyled nav nav-pills nav-stacked'] );
}

$listEdgesOut	= '<small class="muted"><em>None.</em></small>';
if( $edgesOut ){
	$listEdgesOut	= [];
	foreach( $edgesOut as $edge ){
		$nodeFrom		= HtmlTag::create( 'small', $nodeIndex[$edge->fromNodeId].' -> ', ['class' => 'muted'] );
		$nodeTo			= HtmlTag::create( 'small', ' -> '.$nodeIndex[$edge->toNodeId], ['class' => 'muted'] );
		$label			= $nodeFrom.$edge->label.$nodeTo;
		$link			= HtmlTag::create( 'a', $label, [
			'href'	=> './work/graph/edge/'.$edge->edgeId.'/'.$nodeId,
		] );
		$key		= strtolower( $edge->label ).'_'.microtime( TRUE );
		$listEdgesOut[$key]	= HtmlTag::create( 'li', $link );
	}
	ksort( $listEdgesOut );
	$listEdgesOut	= HtmlTag::create( 'ul', $listEdgesOut, ['class' => 'not-unstyled nav nav-pills nav-stacked'] );
}

return '
<div class="content-panel">
	<h3>Edges on this node</h3>
	<div class="content-panel-inner">
		<h4>Outgoing edges</h4>
		'.$listEdgesOut.'
		<h4>Incoming edges</h4>
		'.$listEdgesIn.'
	</div>
</div>
';
