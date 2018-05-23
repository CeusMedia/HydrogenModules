<?php
$modelForm	= new Model_Form( $env );
$modelFill	= new Model_Form_Fill( $env );

$iconView	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconFilter	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search' ) );
$iconReset	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-minus' ) );

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
$colgroup	= UI_HTML_Elements::ColumnGroup( '40px', '', '', '100px', '130px', '80px' );
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
$panelList	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Einträge' ),
	UI_HTML_Tag::create( 'div', array(
		$table,
		$buttonbar,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );


$optForm		= array( '' => '- alle -' );
foreach( $forms as $item )
	$optForm[$item->formId]	= $item->title;
$optForm		= UI_HTML_Elements::Options( $optForm, $filterFormId );

$optStatus		= array( '' => '- alle -', '0' => 'unbestätigt', '1' => 'gültig' );
$optStatus		= UI_HTML_Elements::Options( $optStatus, $filterStatus );

$panelFilter	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Filter' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'form', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'E-Mail', array( 'for' => 'input_email' ) ),
					UI_HTML_Tag::create( 'input', NULL, array( 'type' => 'text', 'name' => 'email', 'id' => 'input_email', 'value' => htmlentities( $filterEmail, ENT_QUOTES, 'UTF-8' ) ) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'E-Mail', array( 'for' => 'input_email' ) ),
					UI_HTML_Tag::create( 'select', $optForm, array( 'name' => 'formId', 'id' => 'input_formId' ) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Zustand', array( 'for' => 'input_status' ) ),
					UI_HTML_Tag::create( 'select', $optStatus, array( 'name' => 'status', 'id' => 'input_status' ) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'button', $iconFilter.'&nbsp;filtern', array(
						'type'	=> 'submit',
						'name'	=> 'filter',
						'class'	=> 'btn btn-small btn-info',
					) ),
					UI_HTML_Tag::create( 'a', $iconReset.'&nbsp;leeren', array(
						'href'	=> './manage/form/fill/filter/reset',
						'class'	=> 'btn btn-small btn-inverse',
					) ),
				), array( 'class' => 'btn-group' ) ),
			), array( 'class' => 'buttonbar' ) ),
		), array( 'action' => './manage/form/fill/filter', 'method' => 'post' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

//$heading	= UI_HTML_Tag::create( 'h2', 'Einträge' );
//return $heading.$filter.$table.$buttonbar;

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', $panelFilter, array( 'class' => 'span3' ) ),
	UI_HTML_Tag::create( 'div', $panelList, array( 'class' => 'span9' ) ),
), array( 'class' => 'row-fluid' ) );
