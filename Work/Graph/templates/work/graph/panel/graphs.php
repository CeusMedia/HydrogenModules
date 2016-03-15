<?php

$listGraphs	= array();
foreach( $graphs as $graph ){
	$class		= $graphId == $graph->graphId ? 'active' : '';
	$link		= UI_HTML_Tag::create( 'a', $graph->title, array(
		'href'	=> './work/graph/'.$graph->graphId,
	) );
	$key		= strtolower( $graph->title ).'_'.microtime( TRUE );
	$listGraphs[$key]	= UI_HTML_Tag::create( 'li', $link, array(
		'class'	=> $class,
	) );
}
ksort( $listGraphs );
$listGraphs	= UI_HTML_Tag::create( 'ul', $listGraphs, array( 'class' => 'not-unstyled nav nav-pills nav-stacked' ) );

return '
<div class="content-panel">
	<h3>Graphs</h3>
	<div class="content-panel-inner">
		'.$listGraphs.'
	</div>
</div>
';
