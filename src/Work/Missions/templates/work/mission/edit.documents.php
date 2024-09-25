<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var array $words */
/** @var Entity_Mission $mission */
/** @var Entity_Mission_Document[] $documents */

$w			= (object) @$words['view-documents'];

$phraser	= new View_Helper_TimePhraser( $env );


$table		= '<div class="alert alert-hint">'.$w->noEntries.'</div>';

if( $documents ){
	$rows	= [];
	foreach( $documents as $document ){

		$modifiedAt	= max( $document->createdAt, $document->modifiedAt );
		$modifiedAt	= 'vor '.$phraser->convert( $modifiedAt, TRUE );
/*		$buttonView		= HtmlTag::create( 'a', '<i class="fa fa-fw fa-eye"></i>', [
			'href'	=> './work/mission/viewDocument/'.$mission->missionId.'/'.$document->missionDocumentId,
			'class'	=> 'btn btn-small not-btn-info',
			'title'	=> 'anzeigen',
		] );*/
		$buttonDownload	= HtmlTag::create( 'a', '<i class="fa fa-fw fa-download"></i>', [
			'href'	=> './work/mission/downloadDocument/'.$mission->missionId.'/'.$document->missionDocumentId,
			'class'	=> 'btn btn-small btn-info',
			'title'	=> 'runterladen',
		] );
		$buttonRemove	= HtmlTag::create( 'a', '<i class="fa fa-fw fa-remove"></i>', [
			'href'	=> './work/mission/removeDocument/'.$mission->missionId.'/'.$document->missionDocumentId,
			'class'	=> 'btn btn-small btn-inverse',
			'title'	=> 'entfernen',
		] );
		$buttons		= HtmlTag::create( 'div', [/*$buttonView.*/$buttonDownload.$buttonRemove], ['class' => 'btn-group'] );
		$label			= HtmlTag::create( 'a', $document->filename, [
			'href'		=> './work/mission/viewDocument/'.$mission->missionId.'/'.$document->missionDocumentId,
			'target'	=> '_blank',
			'class'		=> NULL,
		] );
		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $label, ['class' => 'cell-document-title'] ),
			HtmlTag::create( 'td', $modifiedAt, ['class' => 'cell-document-createdAt'] ),
			HtmlTag::create( 'td', $buttons, ['class' => 'cell-document-actions'] ),
		) );
	}

	$colgroup	= HtmlElements::ColumnGroup( '*', '140px', '100px' );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( [$w->headTitle, 'Speicherung', ''] ) );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );
}

$formUpload		= '';
if( $env->getAcl()->has( 'work/mission', 'addDocument' ) ){
	$iconFile		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-folder'] );
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
