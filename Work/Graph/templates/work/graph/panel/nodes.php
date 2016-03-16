<?php

if( empty( $nodeId ) )
	$nodeId	= NULL;
$listNodes		= '<em class="muted">Keine vorhanden.</em>';
if( $nodes ){
	$listNodes		= array();
	foreach( $nodes as $node ){
		$class		= $nodeId == $node->nodeId ? 'active' : NULL;
		$label			= $node->label ? $node->label : $node->ID;
		$link			= UI_HTML_Tag::create( 'a', $label, array(
			'href'	=> './work/graph/node/'.$node->nodeId
		) );
		$key		= strtolower( $label ).'_'.microtime( TRUE );
		$listNodes[$key]	= UI_HTML_Tag::create( 'li', $link, array(
			'class'	=> $class,
		) );
	}
	ksort( $listNodes );
	$listNodes		= UI_HTML_Tag::create( 'ul', $listNodes, array( 'class' => 'not-unstyled nav nav-pills nav-stacked' ) );
}

return '
<div class="content-panel">
	<h3>Nodes</h3>
	<div class="content-panel-inner">
		'.$listNodes.'
	</div>
</div>
';
