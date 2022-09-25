<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) @$words['view-documents'];

$phraser	= new View_Helper_TimePhraser( $env );


$table		= '<div class="alert alert-hint">'.$w->noEntries.'</div>';

if( $documents ){
	$rows	= [];
	foreach( $documents as $document ){

		$modifiedAt	= max( $document->createdAt, $document->modifiedAt );
		$modifiedAt	= 'vor '.$phraser->convert( $modifiedAt, TRUE );
/*		$buttonView		= HtmlTag::create( 'a', '<i class="fa fa-fw fa-eye"></i>', array(
			'href'	=> './work/mission/viewDocument/'.$mission->missionId.'/'.$document->missionDocumentId,
			'class'	=> 'btn btn-small not-btn-info',
			'title'	=> 'anzeigen',
		) );*/
		$buttonDownload	= HtmlTag::create( 'a', '<i class="fa fa-fw fa-download"></i>', array(
			'href'	=> './work/mission/downloadDocument/'.$mission->missionId.'/'.$document->missionDocumentId,
			'class'	=> 'btn btn-small btn-info',
			'title'	=> 'runterladen',
		) );
		$buttonRemove	= HtmlTag::create( 'a', '<i class="fa fa-fw fa-remove"></i>', array(
			'href'	=> './work/mission/removeDocument/'.$mission->missionId.'/'.$document->missionDocumentId,
			'class'	=> 'btn btn-small btn-inverse',
			'title'	=> 'entfernen',
		) );
		$buttons		= HtmlTag::create( 'div', array( /*$buttonView.*/$buttonDownload.$buttonRemove ), array( 'class' => 'btn-group' ) );
		$label			= HtmlTag::create( 'a', $document->filename, array(
			'href'		=> './work/mission/viewDocument/'.$mission->missionId.'/'.$document->missionDocumentId,
			'target'	=> '_blank',
			'class'		=> NULL,
		) );
		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $label, array( 'class' => 'cell-document-title' ) ),
			HtmlTag::create( 'td', $modifiedAt, array( 'class' => 'cell-document-createdAt' ) ),
			HtmlTag::create( 'td', $buttons, array( 'class' => 'cell-document-actions' ) ),
		) );
	}

	$colgroup	= UI_HTML_Elements::ColumnGroup( '*', '140px', '100px' );
	$thead		= HtmlTag::create( 'thead', UI_HTML_Elements::TableHeads( array( $w->headTitle, 'Speicherung', '' ) ) );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}

$formUpload		= '';
if( $env->getAcl()->has( 'work/mission', 'addDocument' ) ){
	$iconFile		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-folder' ) );
	$helperUpload	= new View_Helper_Input_File( $env );
	$helperUpload->setName( 'document' );
	$helperUpload->setLabel( $iconFile );
	$helperUpload->setRequired( TRUE );
	$formUpload	= '
	<form action="./work/mission/addDocument/'.$mission->missionId.'" method="post" enctype="multipart/form-data">
		<div class="buttonbar">
			<div class="row-fluid">
				<div class="span6">
					'.$helperUpload->render().'
				</div>
				<div class="span6">
					<button type="submit" name="save" value="document" class="btn btn-success not-btn-small"><i class="fa fa-upload"></i>&nbsp;hochladen</button>
				</div>
			</div>
		</div>
	</form>';
}

return '
<div class="content-panel content-panel-list" id="documents">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				'.$table.'
			</div>
			'.$formUpload.'
		</div>
	</div>
</div>';
