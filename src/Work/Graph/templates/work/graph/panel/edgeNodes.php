<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$nodeIndex	= [];
foreach( $nodes as $node )
	$nodeIndex[$node->nodeId]	= $node->label ? $node->label : $node->ID;

$linkSource	= HtmlTag::create( 'a', $nodeIndex[$edge->fromNodeId], [
	'href'	=> './work/graph/node/'.$edge->fromNodeId
] );
$linkTarget	= HtmlTag::create( 'a', $nodeIndex[$edge->toNodeId], [
	'href'	=> './work/graph/node/'.$edge->toNodeId
] );

return '
<div class="content-panel">
	<h3>Node of this edge</h3>
	<div class="content-panel-inner">
		<h4 class="not-muted">Source</h4>
		'.$linkSource.'
		<h4 class="not-muted">Target</h4>
		'.$linkTarget.'
	</div>
</div>
';
