<?php

$iconEnabled	= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/accept.png', $words['states'][1] );
$iconDisabled	= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/delete.png', $words['states'][0] );
$iconRefresh	= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/mini/borderless/png/action_refresh_blue.png', '' );

$rows	= array();
foreach( $sources as $sourceId => $source ){
	$state	= $iconDisabled;
	if( $source->active ){
		$state		= $iconEnabled;
		$urlRefresh	= './admin/module/source/refresh/'.$sourceId."?from=admin/module/source";
		$button		= UI_HTML_Elements::LinkButton( $urlRefresh, $iconRefresh, 'button tiny' );
		$button		= UI_HTML_Tag::create( 'a', $iconRefresh, array(
			'href'		=> $urlRefresh,
			'class'		=> 'button tiny locklayer-auto',
			'title'		=> 'Quelle neu einlesen',
			'data-locklayer-label'	=> 'Lese Quelle neu ein ...',
		) );
		$state		.= '&nbsp;'.$button;
	}
	$label	= $source->title;
	$link	= UI_HTML_Elements::Link( './admin/module/source/edit/'.$sourceId, $sourceId );
	$type	= $words['types'][$source->type].'  <em><small class="counter-modules"></small></em>';
	$cellId		= UI_HTML_Tag::create( 'td', $link );
	$cellLabel	= UI_HTML_Tag::create( 'td', $label );
	$cellType	= UI_HTML_Tag::create( 'td', $type );
	$cellActive	= UI_HTML_Tag::create( 'td', $state );
	$rows[$sourceId]		= UI_HTML_Tag::create( 'tr',
		$cellId.$cellLabel.$cellType.$cellActive,
		array( 'class' => 'source', 'data-id' => $sourceId )
	);
}
ksort( $rows );

$w			= (object) $words['index'];

$buttonAdd	= UI_HTML_Tag::create( 'button', '<span>'.$w->buttonAdd.'</span>', array(
	'type'					=> 'button',
	'class'					=> 'button add locklayer-auto',
	'onclick'				=> 'document.location.href = \'./admin/module/source/add\';',
//	'data-locklayer-label'	=> '
) );



$heads		= array( $w->headId, $w->headTitle, $w->headType, $w->headActive );
$heads		= UI_HTML_Elements::TableHeads( $heads );
$colgroup	= UI_HTML_Elements::ColumnGroup( '20%,55%,15%,10%' );
$panelList	= '
<fieldset>
	<legend class="library">'.$w->legend.'</legend>
	<table>
		'.$colgroup.'
		'.$heads.'
		'.join( $rows ).'
	</table>
	'.$buttonAdd.'
</fieldset>
';

return '
<div class="column-left-75">
	'.$panelList.'
</div>
';
?>
