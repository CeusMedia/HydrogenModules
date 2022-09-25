<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconEnabled	= HtmlElements::Image( 'https://cdn.ceusmedia.de/img/famfamfam/silk/accept.png', $words['states'][1] );
$iconDisabled	= HtmlElements::Image( 'https://cdn.ceusmedia.de/img/famfamfam/silk/delete.png', $words['states'][0] );
$iconRefresh	= HtmlElements::Image( 'https://cdn.ceusmedia.de/img/famfamfam/mini/borderless/png/action_refresh_blue.png', '' );

$rows	= [];
foreach( $sources as $sourceId => $source ){
	$state	= $iconDisabled;
	if( $source->active ){
		$state		= $iconEnabled;
		$urlRefresh	= './admin/module/source/refresh/'.$sourceId."?from=admin/module/source";
		$button		= HtmlElements::LinkButton( $urlRefresh, $iconRefresh, 'button tiny' );
		$button		= HtmlTag::create( 'a', $iconRefresh, array(
			'href'		=> $urlRefresh,
			'class'		=> 'button tiny locklayer-auto',
			'title'		=> 'Quelle neu einlesen',
			'data-locklayer-label'	=> 'Lese Quelle neu ein ...',
		) );
		$state		.= '&nbsp;'.$button;
	}
	$label	= $source->title;
	$link	= HtmlElements::Link( './admin/module/source/edit/'.$sourceId, $sourceId );
	$type	= $words['types'][$source->type].'  <em><small class="counter-modules"></small></em>';
	$cellId		= HtmlTag::create( 'td', $link );
	$cellLabel	= HtmlTag::create( 'td', $label );
	$cellType	= HtmlTag::create( 'td', $type );
	$cellActive	= HtmlTag::create( 'td', $state );
	$rows[$sourceId]		= HtmlTag::create( 'tr',
		$cellId.$cellLabel.$cellType.$cellActive,
		array( 'class' => 'source', 'data-id' => $sourceId )
	);
}
ksort( $rows );

$w			= (object) $words['index'];

$buttonAdd	= HtmlTag::create( 'button', '<span>'.$w->buttonAdd.'</span>', array(
	'type'					=> 'button',
	'class'					=> 'button add locklayer-auto',
	'onclick'				=> 'document.location.href = \'./admin/module/source/add\';',
//	'data-locklayer-label'	=> '
) );



$heads		= array( $w->headId, $w->headTitle, $w->headType, $w->headActive );
$heads		= HtmlElements::TableHeads( $heads );
$colgroup	= HtmlElements::ColumnGroup( '20%,55%,15%,10%' );
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
