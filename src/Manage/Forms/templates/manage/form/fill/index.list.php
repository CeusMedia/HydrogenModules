<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array<Entity_Form_Fill> $fills */
/** @var array<Entity_Form_Transfer_Target> $transferTargets */
/** @var ?string $filterStatus */
/** @var int $page */
/** @var int $pages */

$modelForm	= new Model_Form( $env );
$modelFill	= new Model_Form_Fill( $env );

$iconView		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconFilter		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search'] );
$iconReset		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search-minus'] );
$iconDownload	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-download'] );
$iconTransfer   = HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-upload'] );

$statusLabels	= [
	Model_Form_Fill::STATUS_NEW			=> HtmlTag::create( 'label', 'unbestätigt', ['class' => 'label'] ),
	Model_Form_Fill::STATUS_CONFIRMED	=> HtmlTag::create( 'label', 'gültig', ['class' => 'label label-info'] ),
	Model_Form_Fill::STATUS_HANDLED		=> HtmlTag::create( 'label', 'behandelt', ['class' => 'label label-info'] ),
	Model_Form_Fill::STATUS_CUSTOMER	=> HtmlTag::create( 'label', 'Kunde', ['class' => 'label label-success'] ),
	Model_Form_Fill::STATUS_BOOKING		=> HtmlTag::create( 'label', 'Buchung', ['class' => 'label label-success'] ),
	Model_Form_Fill::STATUS_ORDER		=> HtmlTag::create( 'label', 'Kauf', ['class' => 'label label-success'] ),
	Model_Form_Fill::STATUS_NEWSLETTER	=> HtmlTag::create( 'label', 'Newsletter', ['class' => 'label label-success'] ),
];

$helperTime		= new View_Helper_TimePhraser( $env );
$helperTime->setTemplate( '%s' );
$helperTime->setMode( View_Helper_TimePhraser::MODE_BREAK );

$rows		= [];
foreach( $fills as $fill ){
	$fill->data	= json_decode( $fill->data );
	$linkView	= HtmlTag::create( 'a', $iconView, [
		'href'	=> './manage/form/fill/view/'.$fill->fillId.'?page='.$page,
		'class'	=> 'btn btn-mini btn-info',
		'title'	=> 'anzeigen',
	] );
	$linkRemove	= HtmlTag::create( 'a', $iconRemove, [
		'href'		=> './manage/form/fill/remove/'.$fill->fillId.'?page='.$page,
		'class'		=> 'btn btn-mini btn-danger',
		'title'		=> 'entfernen',
		'onclick'	=> "if(!confirm('Wirklich ?'))return false;"
	] );
	$buttons	= HtmlTag::create( 'div', [$linkView, $linkRemove], ['class' => 'btn-group'] );
	$date		= $helperTime->setTimestamp( $fill->createdAt );
	$email		= HtmlTag::create( 'small', $fill->email );
	$name		= '';
	if( isset( $fill->data->firstname ) )
		$name		= $fill->data->firstname->value.' '.$fill->data->surname->value.'<br/>';
	$linkForm	= './manage/form/edit/'.$fill->formId.( $page ? '?page='.$page : '' );
	$linkView	= './manage/form/fill/view/'.$fill->fillId.( $page ? '?page='.$page : '' );
	$title		= HtmlTag::create( 'a', $name.$email, ['href' => $linkView] );
	$form		= HtmlTag::create( 'a', $fill->form->title, ['href' => $linkForm] );

	$transfers	= '';
	if( count( $fill->transfers ) ){
		$list	= [];
		$success	= TRUE;
		foreach( $fill->transfers as $transfer ){
			if( $success && in_array( (int) $transfer->status, [
				Model_Form_Fill_Transfer::STATUS_ERROR,
				Model_Form_Fill_Transfer::STATUS_EXCEPTION,
			], TRUE ) )
				$success	= FALSE;
			$list[]	= $transferTargets[$transfer->formTransferTargetId]->title;
		}
		$list		= 'Transfers:'.PHP_EOL.' - '.implode( PHP_EOL.' - ', $list );
		$label		= count( $fill->transfers );
		$transfers	= HtmlTag::create( 'span', $iconTransfer.'&nbsp;'.$label, [
			'class'	=> 'label '.( $success ? 'label-success' : 'label-important' ),
			'title' => $list
		] );
	}

	$statuses	= [];
	$statusBits	= new \CeusMedia\Common\ADT\Bitmask( $fill->status );
	foreach( $statusLabels as $status => $statusLabel )
		if( $statusBits->has( $status ) )
			$statuses[]	= $statusLabel;

	$rows[]		= HtmlTag::create( 'tr', [
		HtmlTag::create( 'td', HtmlTag::create( 'small', $fill->fillId ) ),
		HtmlTag::create( 'td', $title ),
		HtmlTag::create( 'td', HtmlTag::create( 'div', $form.'<br/><small>'.join( ' ', $statuses ).'</small>' , ['class' => 'autocut', 'style' => 'font-size: 0.9em'] ) ),
//		HtmlTag::create( 'td', $statuses[(int) $fill->status].'&nbsp;'.$transfers ),
		HtmlTag::create( 'td', $date ),
		HtmlTag::create( 'td', $buttons ),
	] );
}
$colgroup	= HtmlElements::ColumnGroup( '50px', '', ''/*, '95px'*/, '120px', '75px' );
$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( ['ID', 'Name / E-Mail', 'Formular'/*, 'Zustand'*/, 'Alter / Datum', ''] ) );
$tbody		= HtmlTag::create( 'tbody', $rows );
$table		= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-fixed table-striped not-table-condensed'] );

$buttonExport	= HtmlTag::create( 'button', $iconDownload.'&nbsp;exportieren', [
	'type'		=> 'button',
	'disabled'	=> 'disabled',
	'class'		=> 'btn',
] );
if( !empty( $filterFormId ) && 0 !== count( array_filter( $filterFormId ) ) )
	$buttonExport	= HtmlTag::create( 'a', $iconDownload.'&nbsp;exportieren', [
		'href'		=> './manage/form/fill/export/csv/form/'.join( ',', $filterFormId ).'/'.$filterStatus,
		'class'		=> 'btn',
	] );

$pagination	= '';
if( $pages > 1 ){
	Icon::$defaultSet	= 'fontawesome';
	$pagination	= new PageControl( './manage/form/fill/', $page, $pages );
	$pagination->patternUrl	= '%s';
}
$buttonbar	= HtmlTag::create( 'div', join( '&nbsp;', [$buttonExport, $pagination] ), ['class' => 'buttonbar'] );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Einträge' ),
	HtmlTag::create( 'div', [
		$table,
		$buttonbar,
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );
