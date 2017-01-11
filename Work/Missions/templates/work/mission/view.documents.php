<?php

$w			= (object) @$words['view-documents'];

$phraser	= new View_Helper_TimePhraser( $env );


$table		= '<div class="alert alert-hint">'.$w->noEntries.'</div>';

if( $documents ){
	$rows	= array();
	foreach( $documents as $document ){

		$modifiedAt	= max( $document->createdAt, $document->modifiedAt );
		$modifiedAt	= 'vor '.$phraser->convert( $modifiedAt, TRUE );
/*		$buttonView		= UI_HTML_Tag::create( 'a', '<i class="fa fa-fw fa-eye"></i>', array(
			'href'	=> './work/mission/viewDocument/'.$mission->missionId.'/'.$document->missionDocumentId,
			'class'	=> 'btn btn-small not-btn-info',
			'title'	=> 'anzeigen',
		) );*/
		$buttonDownload	= UI_HTML_Tag::create( 'a', '<i class="fa fa-fw fa-download"></i>', array(
			'href'	=> './work/mission/downloadDocument/'.$mission->missionId.'/'.$document->missionDocumentId,
			'class'	=> 'btn btn-small btn-info',
			'title'	=> 'runterladen',
		) );
		$buttonRemove	= UI_HTML_Tag::create( 'a', '<i class="fa fa-fw fa-remove"></i>', array(
			'href'	=> './work/mission/removeDocument/'.$mission->missionId.'/'.$document->missionDocumentId,
			'class'	=> 'btn btn-small btn-inverse',
			'title'	=> 'entfernen',
		) );
		$buttons		= UI_HTML_Tag::create( 'div', array( /*$buttonView.*/$buttonDownload.$buttonRemove ), array( 'class' => 'btn-group' ) );
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

	$colgroup	= UI_HTML_Elements::ColumnGroup( '*', '140px', '100px' );
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( $w->headTitle, 'Speicherung', '' ) ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}

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
	<div class="span3">
		<div class="content-panel content-panel-form">
			<h3>Upload</h3>
			<div class="content-panel-inner">
				<form action="./work/mission/addDocument/'.$mission->missionId.'" method="post" enctype="multipart/form-data">
					<div class="row-fluid">
						<div class="span12">
							'.View_Helper_Input_File::render( 'document', 'Dokument', TRUE, '' ).'
						</div>
					</div>
					<div class="buttonbar">
						<button type="submit" name="save" value="document" class="btn btn-success btn-small"><i class="fa fa-upload"></i>&nbsp;hochladen</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
';
