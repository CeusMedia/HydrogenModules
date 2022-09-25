<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$modelForm	= new Model_Form( $env );
$modelFill	= new Model_Form_Fill( $env );

$iconView		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconFilter		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search' ) );
$iconReset		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-minus' ) );
$iconDownload	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) );
$iconTransfer   = HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-upload' ) );

$statuses	= array(
	Model_Form_Fill::STATUS_NEW			=> HtmlTag::create( 'label', 'unbestätigt', array( 'class' => 'label' ) ),
	Model_Form_Fill::STATUS_CONFIRMED	=> HtmlTag::create( 'label', 'gültig', array( 'class' => 'label label-success' ) ),
	Model_Form_Fill::STATUS_HANDLED		=> HtmlTag::create( 'label', 'behandelt', array( 'class' => 'label label-info' ) ),
);

$rows		= [];
foreach( $fills as $fill ){
	$fill->data	= json_decode( $fill->data );
	$linkView	= HtmlTag::create( 'a', $iconView, array(
		'href'	=> './manage/form/fill/view/'.$fill->fillId.'?page='.$page,
		'class'	=> 'btn btn-mini btn-info',
		'title'	=> 'anzeigen',
	) );
	$linkRemove	= HtmlTag::create( 'a', $iconRemove, array(
		'href'		=> './manage/form/fill/remove/'.$fill->fillId.'?page='.$page,
		'class'		=> 'btn btn-mini btn-danger',
		'title'		=> 'entfernen',
		'onclick'	=> "if(!confirm('Wirklich ?'))return false;"
		) );
	$buttons	= HtmlTag::create( 'div', array( $linkView, $linkRemove ), array( 'class' => 'btn-group' ) );
	$date		= HtmlTag::create( 'small', date( 'Y-m-d H:i:s', $fill->createdAt ) );
	$email		= HtmlTag::create( 'small', $fill->email );
	$name		= '';
	if( isset( $fill->data->firstname ) )
		$name		= $fill->data->firstname->value.' '.$fill->data->surname->value.'<br/>';
	$linkForm	= './manage/form/edit/'.$fill->formId.( $page ? '?page='.$page : '' );
	$linkView	= './manage/form/fill/view/'.$fill->fillId.( $page ? '?page='.$page : '' );
	$title		= HtmlTag::create( 'a', $name.$email, array( 'href' => $linkView ) );
	$form		= $modelForm->get( $fill->formId );
	$form		= HtmlTag::create( 'a', $form->title, array( 'href' => $linkForm ) );

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

	$rows[]		= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', HtmlTag::create( 'small', $fill->fillId ) ),
		HtmlTag::create( 'td', $title ),
		HtmlTag::create( 'td', HtmlTag::create( 'small', $form ) ),
		HtmlTag::create( 'td', $statuses[(int) $fill->status].'&nbsp;'.$transfers ),
		HtmlTag::create( 'td', $date ),
		HtmlTag::create( 'td', $buttons ),
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( '50px', '', '', '100px', '130px', '80px' );
$thead		= HtmlTag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'ID', 'Name / E-Mail', 'Formular', 'Zustand', 'Datum / Zeit', '' ) ) );
$tbody		= HtmlTag::create( 'tbody', $rows );
$table		= HtmlTag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-fixed table-striped not-table-condensed' ) );

$buttonExport	= HtmlTag::create( 'button', $iconDownload.'&nbsp;exportieren', array(
	'type'		=> 'button',
	'disabled'	=> 'disabled',
	'class'		=> 'btn',
) );
if( !empty( $filterFormId ) && 0 !== count( array_filter( $filterFormId ) ) )
	$buttonExport	= HtmlTag::create( 'a', $iconDownload.'&nbsp;exportieren', array(
		'href'		=> './manage/form/fill/export/csv/form/'.join( ',', $filterFormId ).'/'.$filterStatus,
		'class'		=> 'btn',
	) );

$pagination	= '';
if( $pages > 1 ){
	\CeusMedia\Bootstrap\Icon::$defaultSet	= 'fontawesome';
	$pagination	= new \CeusMedia\Bootstrap\Nav\PageControl( './manage/form/fill/', $page, $pages );
	$pagination->patternUrl	= '%s';
}
$buttonbar	= HtmlTag::create( 'div', join( '&nbsp;', array( $buttonExport, $pagination ) ), array( 'class' => 'buttonbar' ) );

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Einträge' ),
	HtmlTag::create( 'div', array(
		$table,
		$buttonbar,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
