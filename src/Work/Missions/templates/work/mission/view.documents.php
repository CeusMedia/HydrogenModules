<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array $words */
/** @var object $mission */
/** @var object[] $documents */

$w			= (object) @$words['view-documents'];
$phraser	= new View_Helper_TimePhraser( $env );
$table		= '<div class="alert alert-hint">'.$w->noEntries.'</div>';

if( !$documents )
	return;

$rows	= [];
foreach( $documents as $document ){
	$modifiedAt	= max( $document->createdAt, $document->modifiedAt );
	$modifiedAt	= 'vor '.$phraser->convert( $modifiedAt, TRUE );
/*	$buttonView		= HtmlTag::create( 'a', '<i class="fa fa-fw fa-eye"></i>', array(
		'href'	=> './work/mission/viewDocument/'.$mission->missionId.'/'.$document->missionDocumentId,
		'class'	=> 'btn btn-small not-btn-info',
		'title'	=> 'anzeigen',
	) );*/
	$buttonDownload	= HtmlTag::create( 'a', '<i class="fa fa-fw fa-download"></i>', [
		'href'	=> './work/mission/downloadDocument/'.$mission->missionId.'/'.$document->missionDocumentId,
		'class'	=> 'btn btn-small btn-info',
		'title'	=> 'runterladen',
	] );
	$buttons		= HtmlTag::create( 'div', [/*$buttonView.*/$buttonDownload], ['class' => 'btn-group'] );
	$label			= HtmlTag::create( 'a', $document->filename, [
		'href'		=> './work/mission/viewDocument/'.$mission->missionId.'/'.$document->missionDocumentId,
		'target'	=> '_blank',
		'class'		=> NULL,
	] );
	$rows[]	= HtmlTag::create( 'tr', [
		HtmlTag::create( 'td', $label, ['class' => 'cell-document-title'] ),
		HtmlTag::create( 'td', $modifiedAt, ['class' => 'cell-document-createdAt'] ),
		HtmlTag::create( 'td', $buttons, ['class' => 'cell-document-actions'] ),
	] );
}

$colgroup	= HtmlElements::ColumnGroup( '*', '150px', '50px' );
$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( [$w->headTitle, 'Speicherung', ''] ) );
$tbody		= HtmlTag::create( 'tbody', $rows );
$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );

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
