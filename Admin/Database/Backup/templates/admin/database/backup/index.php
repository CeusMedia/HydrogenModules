<?php

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );


$list	= UI_HTML_Tag::create( 'div', 'Keine vorhanden.', array( 'class' => 'alert alert-info' ) );
if( $backups ){
	$list	= array();
	foreach( $backups as $backup ){
		if( is_string( $backup->comment ) ){
			$backup->comment	= array(
				'comment'		=> $backup->comment,
			);
		}
		else if( is_null( $backup->comment ) ){
			$backup->comment	= array( 'comment' => '' );
		}
		$rowClass	= '';
		$status		= '';
		if( !empty( $backup->comment['copyPrefix'] ) ){
			$rowClass	= 'info';
			$status		= 'Kopie installiert';
			if( $backup->comment['copyPrefix'] === $currentCopyPrefix ){
				$rowClass	= 'success';
				$status		= 'Kopie aktiviert';
			}
		}

		$link	= UI_HTML_Tag::create( 'a', $backup->filename, array( 'href' => './admin/database/backup/view/'.$backup->id ) );
		if( class_exists ( 'View_Helper_TimePhraser' ) ){
			$helper			= new View_Helper_TimePhraser( $env );
			$creationDate	= $helper->convert( $backup->timestamp, TRUE, 'vor ' );
		}
		else {
			$date			= UI_HTML_Tag::create( 'span', date( 'd.m.Y', $backup->timestamp ), array( 'class' => '' ) );
			$time			= UI_HTML_Tag::create( 'small', date( 'H:i:s', $backup->timestamp ), array( 'class' => 'muted' ) );
			$creationDate	= $date.' '.$time;
		}
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $status ),
			UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', $backup->comment['comment'], array( 'class' => 'muted' ) ) ),
			UI_HTML_Tag::create( 'td', Alg_UnitFormater::formatBytes( $backup->filesize ) ),
			UI_HTML_Tag::create( 'td', $creationDate ),
		), array( 'class' => $rowClass ) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array( '33%', '15%', '', '100px', '150px' ) );
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Datei', 'Status', 'Kommentar', 'Größe', 'Erstellungsdatum' ) ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $list );
	$list		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed' ) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.' neue Sicherung anlegen', array(
	'href'	=> './admin/database/backup/backup',
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
