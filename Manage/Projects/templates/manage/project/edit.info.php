<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$facts	= [];
if( isset( $missions ) && count( $missions ) ){
	$url	= './work/mission/filter?projects[]='.$project->projectId;
	$label	= HtmlTag::create( 'a', count( $missions ), ['href' => $url] );
	$facts[]	= HtmlTag::create( 'dt', 'Aufgaben' ).HtmlTag::create( 'dd', $label );
}

if( isset( $issues ) ){
	$url	= './work/issue/filter?projects[]='.$project->projectId;
	$button	= HtmlElements::LinkButton( $url, 'anzeigen', 'button filter' );
	$label	= count( $missions ).'&nbsp;'.$button;
	$facts[]	= HtmlTag::create( 'dt', 'Probleme' ).HtmlTag::create( 'dd', $label );
}

if( !$facts )
	return '';
	
return '
<div class="content-panel content-panel-info">
	<h3>Informationen</h3>
	<div class="content-panel-inner">
		<dl>
			'.join( $facts ).'
		</dl>
	</div>
</div>';
?>