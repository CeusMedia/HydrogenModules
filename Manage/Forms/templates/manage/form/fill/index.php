<?php
$modelForm	= new Model_Form( $env );
$modelFill	= new Model_Form_Fill( $env );

$iconView	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$statuses	= array(
	Model_Form_Fill::STATUS_NEW			=> UI_HTML_Tag::create( 'label', 'unbestätigt', array( 'class' => 'label' ) ),
	Model_Form_Fill::STATUS_CONFIRMED	=> UI_HTML_Tag::create( 'label', 'gültig', array( 'class' => 'label label-success' ) ),
);

$rows		= array();
foreach( $fills as $fill ){
	$fill->data	= json_decode( $fill->data );
	$linkView	= UI_HTML_Tag::create( 'a', $iconView, array(
		'href'	=> './manage/form/fill/view/'.$fill->fillId.'?page='.$page,
		'class'	=> 'btn btn-mini btn-info',
		'title'	=> 'anzeigen',
	) );
	$linkRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
		'href'	=> './manage/form/fill/remove/'.$fill->fillId.'?page='.$page,
		'class'	=> 'btn btn-mini btn-danger',
		'title'	=> 'entfernen',
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
	$rows[]		= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', $fill->fillId ) ),
		UI_HTML_Tag::create( 'td', $title ),
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'small', $form ) ),
		UI_HTML_Tag::create( 'td', $statuses[(int) $fill->status] ),
		UI_HTML_Tag::create( 'td', $date ),
		UI_HTML_Tag::create( 'td', $buttons ),
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( '40px', '', '', '120px', '140px', '120px' );
$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'ID', 'Name / E-Mail', 'Formular', 'Zustand', 'Datum / Zeit', '' ) ) );
$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
$table		= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-fixed table-striped not-table-condensed' ) );

$buttonbar	= '';
if( $pages > 1 ){
	\CeusMedia\Bootstrap\Icon::$iconSet	= 'fontawesome';
	$pagination	= new \CeusMedia\Bootstrap\PageControl( './manage/form/fill/', $page, $pages );
	$pagination->patternUrl	= '%s';
	$buttonbar	= UI_HTML_Tag::create( 'div', $pagination->render(), array( 'class' => 'buttonbar' ) );
}

$heading	= UI_HTML_Tag::create( 'h2', 'Einträge' );
return $heading.$table.$buttonbar;
