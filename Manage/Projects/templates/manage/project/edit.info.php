<?php

$facts	= array();
if( isset( $missions ) && count( $missions ) ){
	$url	= './work/mission/filter?projects[]='.$project->projectId;
	$label	= UI_HTML_Tag::create( 'a', count( $missions ), array( 'href' => $url ) );
	$facts[]	= UI_HTML_Tag::create( 'dt', 'Aufgaben' ).UI_HTML_Tag::create( 'dd', $label );
}

if( isset( $issues ) ){
	$url	= './work/issue/filter?projects[]='.$project->projectId;
	$button	= UI_HTML_Elements::LinkButton( $url, 'anzeigen', 'button filter' );
	$label	= count( $missions ).'&nbsp;'.$button;
	$facts[]	= UI_HTML_Tag::create( 'dt', 'Probleme' ).UI_HTML_Tag::create( 'dd', $label );
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