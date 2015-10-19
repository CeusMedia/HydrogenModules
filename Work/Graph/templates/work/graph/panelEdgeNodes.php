<?php

$nodeIndex	= array();
foreach( $nodes as $node )
	$nodeIndex[$node->nodeId]	= $node->label ? $node->label : $node->ID;

$linkSource	= UI_HTML_Tag::create( 'a', $nodeIndex[$edge->fromNodeId], array(
	'href'	=> './work/graph/node/'.$edge->fromNodeId
) );
$linkTarget	= UI_HTML_Tag::create( 'a', $nodeIndex[$edge->toNodeId], array(
	'href'	=> './work/graph/node/'.$edge->toNodeId
) );

return '
<div class="content-panel">
	<h4>Node of this edge</h4>
	<div class="content-panel-inner">
		<h5 class="not-muted">Source</h4>
		'.$linkSource.'
		<h5 class="not-muted">Target</h4>
		'.$linkTarget.'
	</div>
</div>
';
