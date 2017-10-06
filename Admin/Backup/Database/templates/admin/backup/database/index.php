<?php

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$list	= UI_HTML_Tag::create( 'div', 'Keine vorhanden.', array( 'class' => 'alert alert-info' ) );
if( $dumps ){
	$list	= array();
	foreach( $dumps as $dump ){
		$link	= UI_HTML_Tag::create( 'a', $dump->filename, array( 'href' => './admin/backup/database/view/'.$dump->id ) );
		$date	= UI_HTML_Tag::create( 'span', date( 'd.m.Y', $dump->timestamp ), array( 'class' => '' ) );
		$time	= UI_HTML_Tag::create( 'small', date( 'H:i:s', $dump->timestamp ), array( 'class' => 'muted' ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', $dump->comment, array( 'class' => 'muted' ) ) ),
			UI_HTML_Tag::create( 'td', Alg_UnitFormater::formatBytes( $dump->filesize ) ),
			UI_HTML_Tag::create( 'td', $date.' '.$time ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array( '33%', '', '100px', '150px' ) );
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Datei', 'Kommentar', 'Größe', 'Erstellungsdatum' ) ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $list );
	$list		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed' ) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.' neue Sicherung anlegen', array(
	'href'	=> './admin/backup/database/backup',
	'class'	=> 'btn btn-success',
) );

return '
<div class="content-panel">
	<h3>Gesicherte Datenbestände</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
