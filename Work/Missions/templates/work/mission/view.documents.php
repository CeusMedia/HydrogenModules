<?php

$w			= (object) @$words['view-documents'];
$phraser	= new View_Helper_TimePhraser( $env );
$table		= '<div class="alert alert-hint">'.$w->noEntries.'</div>';

if( !$documents )
	return;

$rows	= array();
foreach( $documents as $document ){
	$modifiedAt	= max( $document->createdAt, $document->modifiedAt );
	$modifiedAt	= 'vor '.$phraser->convert( $modifiedAt, TRUE );
/*	$buttonView		= UI_HTML_Tag::create( 'a', '<i class="fa fa-fw fa-eye"></i>', array(
		'href'	=> './work/mission/viewDocument/'.$mission->missionId.'/'.$document->missionDocumentId,
		'class'	=> 'btn btn-small not-btn-info',
		'title'	=> 'anzeigen',
	) );*/
	$buttonDownload	= UI_HTML_Tag::create( 'a', '<i class="fa fa-fw fa-download"></i>', array(
		'href'	=> './work/mission/downloadDocument/'.$mission->missionId.'/'.$document->missionDocumentId,
		'class'	=> 'btn btn-small btn-info',
		'title'	=> 'runterladen',
	) );
	$buttons		= UI_HTML_Tag::create( 'div', array( /*$buttonView.*/$buttonDownload ), array( 'class' => 'btn-group' ) );
	$label			= UI_HTML_Tag::create( 'a', $document->filename, array(
		'href'		=> './work/mission/viewDocument/'.$mission->missionId.'/'.$document->missionDocumentId,
		'target'	=> '_blank',
		'class'		=> NULL,
	) );
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $label, array( 'class' => 'cell-document-title' ) ),
		UI_HTML_Tag::create( 'td', $modifiedAt, array( 'class' => 'cell-document-createdAt' ) ),
		UI_HTML_Tag::create( 'td', $buttons, array( 'class' => 'cell-document-actions' ) ),
	) );
}

$colgroup	= UI_HTML_Elements::ColumnGroup( '*', '150px', '50px' );
$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( $w->headTitle, 'Speicherung', '' ) ) );
$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );

return '
<div class="row-fluid">
	<div class="span9">
		<div class="content-panel content-panel-list">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span12">
						'.$table.'
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
';
