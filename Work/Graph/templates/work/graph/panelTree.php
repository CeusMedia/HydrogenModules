<?php

$listGraphs	= array();
foreach( $graphs as $graph ){
	$listNodes		= '';
	if( $graphId == $graph->graphId ){
		$listNodes		= array();
		foreach( $nodes as $node ){
			$label			= $node->label ? $node->label : $node->ID;
			$link			= UI_HTML_Tag::create( 'a', $label, array(
				'href'	=> './work/graph/node/'.$node->nodeId
			) );
			$listNodes[]	= UI_HTML_Tag::create( 'li', $link );
		}
		$listNodes		= UI_HTML_Tag::create( 'ul', $listNodes, array( 'class' => 'not-unstyled' ) );
	}
	$link			= UI_HTML_Tag::create( 'a', $graph->title, array(
		'href'	=> './work/graph/'.$graphId
	) );
	$listGraphs[]	= UI_HTML_Tag::create( 'li', $link.$listNodes );
}
$listGraphs	= UI_HTML_Tag::create( 'ul', $listGraphs, array( 'class' => 'not-unstyled' ) );

return '
<div class="content-panel">
	<h4>Graph tree</h4>
	<div class="content-panel-inner">
		'.$listGraphs.'
	</div>
</div>
';
