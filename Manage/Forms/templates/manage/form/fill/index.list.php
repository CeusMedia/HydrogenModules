<?php
$modelForm	= new Model_Form( $env );
$modelFill	= new Model_Form_Fill( $env );

$iconView		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconFilter		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search' ) );
$iconReset		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-minus' ) );
$iconDownload	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) );
$iconTransfer   = UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-upload' ) );

$statuses	= array(
	Model_Form_Fill::STATUS_NEW			=> UI_HTML_Tag::create( 'label', 'unbestätigt', array( 'class' => 'label' ) ),
	Model_Form_Fill::STATUS_CONFIRMED	=> UI_HTML_Tag::create( 'label', 'gültig', array( 'class' => 'label label-success' ) ),
	Model_Form_Fill::STATUS_HANDLED		=> UI_HTML_Tag::create( 'label', 'behandelt', array( 'class' => 'label label-info' ) ),
);

$rows		= [];
foreach( $fills as $fill ){
	$fill->data	= json_decode( $fill->data );
	$linkView	= UI_HTML_Tag::create( 'a', $iconView, array(
		'href'	=> './manage/form/fill/view/'.$fill->fillId.'?page='.$page,
		'class'	=> 'btn btn-mini btn-info',
		'title'	=> 'anzeigen',
	) );
	$linkRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
		'href'		=> './manage/form/fill/remove/'.$fill->fillId.'?page='.$page,
		'class'		=> 'btn btn-mini btn-danger',
		'title'		=> 'entfernen',
		'onclick'	=> "if(!confirm('Wirklich ?'))return false;"
		) );
	$buttons	= UI_HTML_Tag::create( 'div', array( $linkView, $linkRemove ), array( 'class' => 'btn-group' ) );
	$date		= UI_HTML_Tag::create( 'small', date( 'Y-m-d H:i:s', $fill->createdAt ) );
	$email		= UI_HTML_Tag::create( 'small', $fill->email );
	$name		= '';
	if( isset( $fill->data->firstname ) )
		$name		= $fill->data->firstname->value.' '.$fill->data->surname->value.'<br/>';
	$linkForm	= './manage/form/edit/'.$fill->formId.( $page ? '?page='.$page : '' );
	$linkView	= './manage/form/fill/view/'.$fill->fillId.( $page ? '?page='.$page : '' );
	$title		= UI_HTML_Tag::create( 'a', $name.$email, array( 'href' => $linkView ) );
	$form		= $modelForm->get( $fill->formId );
	$form		= UI_HTML_Tag::create( 'a', $form->title, array( 'href' => $linkForm ) );

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
		$transfers	= UI_HTML_Tag::create( 'span', $iconTransfer.'&nbsp;'.$label, [
			'class'	=> 'label '.( $success ? 'label-success' : 'label-important' ),
			'title' => $list
		] );
	}

	$rows[]		= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', $fill->fillId ) ),
		UI_HTML_Tag::create( 'td', $title ),
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', $form ) ),
		UI_HTML_Tag::create( 'td', $statuses[(int) $fill->status].'&nbsp;'.$transfers ),
		UI_HTML_Tag::create( 'td', $date ),
		UI_HTML_Tag::create( 'td', $buttons ),
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( '50px', '', '', '100px', '130px', '80px' );
$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'ID', 'Name / E-Mail', 'Formular', 'Zustand', 'Datum / Zeit', '' ) ) );
$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
$table		= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-fixed table-striped not-table-condensed' ) );

$buttonExport	= UI_HTML_Tag::create( 'button', $iconDownload.'&nbsp;exportieren', array(
	'type'		=> 'button',
	'disabled'	=> 'disabled',
	'class'		=> 'btn',
) );
if( !empty( $filterFormId ) && 0 !== count( array_filter( $filterFormId ) ) )
	$buttonExport	= UI_HTML_Tag::create( 'a', $iconDownload.'&nbsp;exportieren', array(
		'href'		=> './manage/form/fill/export/csv/form/'.join( ',', $filterFormId ).'/'.$filterStatus,
		'class'		=> 'btn',
	) );

$pagination	= '';
if( $pages > 1 ){
	\CeusMedia\Bootstrap\Icon::$defaultSet	= 'fontawesome';
	$pagination	= new \CeusMedia\Bootstrap\Nav\PageControl( './manage/form/fill/', $page, $pages );
	$pagination->patternUrl	= '%s';
}
$buttonbar	= UI_HTML_Tag::create( 'div', join( '&nbsp;', array( $buttonExport, $pagination ) ), array( 'class' => 'buttonbar' ) );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Einträge' ),
	UI_HTML_Tag::create( 'div', array(
		$table,
		$buttonbar,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
