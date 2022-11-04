<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$listGraphs	= [];
foreach( $graphs as $graph ){
	$class		= $graphId == $graph->graphId ? 'active' : '';
	$link		= HtmlTag::create( 'a', $graph->title, array(
		'href'	=> './work/graph/'.$graph->graphId,
	) );
	$key		= strtolower( $graph->title ).'_'.microtime( TRUE );
	$listGraphs[$key]	= HtmlTag::create( 'li', $link, array(
		'class'	=> $class,
	) );
}
ksort( $listGraphs );
$listGraphs	= HtmlTag::create( 'ul', $listGraphs, ['class' => 'not-unstyled nav nav-pills nav-stacked'] );

return '
<div class="content-panel">
	<h3>Graphs</h3>
	<div class="content-panel-inner">
		'.$listGraphs.'
	</div>
</div>
';
