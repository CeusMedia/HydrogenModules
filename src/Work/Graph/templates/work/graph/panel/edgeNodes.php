<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<string,array<string,string>> $words */
$wp	= (object) $words['panel.edgeNodes'];

$nodeIndex	= [];
foreach( $nodes as $node )
	$nodeIndex[$node->nodeId]	= $node->label ?: $node->ID;

$linkSource	= HtmlTag::create( 'a', $nodeIndex[$edge->fromNodeId], [
	'href'	=> './work/graph/node/'.$edge->fromNodeId
] );
$linkTarget	= HtmlTag::create( 'a', $nodeIndex[$edge->toNodeId], [
	'href'	=> './work/graph/node/'.$edge->toNodeId
] );

return '
<div class="content-panel">
	<h3>'.$wp->heading.'</h3>
	<div class="content-panel-inner">
		<h4 class="not-muted">'.$wp->headingSource.'</h4>
		'.$linkSource.'
		<h4 class="not-muted">'.$wp->headingTarget.'</h4>
		'.$linkTarget.'
	</div>
</div>
';
