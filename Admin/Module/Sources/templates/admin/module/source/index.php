<?php

$iconEnabled	= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/accept.png', $words['states'][1] );
$iconDisabled	= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/delete.png', $words['states'][0] );
$iconRefresh	= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/mini/borderless/png/action_refresh_blue.png', '' );

$rows	= array();
foreach( $sources as $source ){
	$state	= $source->active == "yes" ? $iconEnabled : $iconDisabled;
	if( $source->active == "yes" ){
		$urlRefresh	= './admin/module/source/refresh/'.$source->id;
		$state .= '&nbsp;'.UI_HTML_Elements::LinkButton( $urlRefresh, $iconRefresh, 'button tiny' );
	}
	$label	= $source->title;
	$link	= UI_HTML_Elements::Link( './admin/module/source/edit/'.$source->id, $source->id );
	$type	= $words['types'][$source->type].'  <em><small class="counter-modules"></small></em>';
	$cellId		= UI_HTML_Tag::create( 'td', $link );
	$cellLabel	= UI_HTML_Tag::create( 'td', $label );
	$cellType	= UI_HTML_Tag::create( 'td', $type );
	$cellActive	= UI_HTML_Tag::create( 'td', $state );
	$rows[]		= UI_HTML_Tag::create( 'tr',
		$cellId.$cellLabel.$cellType.$cellActive,
		array( 'class' => 'source', 'data-id' => $source->id )
	);
}

$w			= (object) $words['index'];

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
	'.UI_HTML_Elements::LinkButton( './admin/module/source/add', $w->buttonAdd, 'button add' ).'
</fieldset>
';

return '
<div class="column-left-75">
	'.$panelList.'
</div>
';

?>