<?php

$rows		= array();
foreach( $files as $fileName ){
	$buttonImport	= UI_HTML_Tag::create( 'a', 'import', array(
		'href'	=> './info/manual/import/'.base64_encode( $fileName ),
		'class'	=> 'btn',
	) );
	$checkbox		= UI_HTML_Tag::create( 'input', NULL, array(
		'type'		=> 'checkbox',
		'name'		=> 'files[]',
		'value'		=> base64_encode( $fileName ),
	) );
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $checkbox ),
		UI_HTML_Tag::create( 'td', $fileName ),
		UI_HTML_Tag::create( 'td', $buttonImport ),
	) );
}
$colgroup		= UI_HTML_Elements::ColumnGroup( array( '', '' ) );
$thead			= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( '1', '2' ) ) );
$tbody			= UI_HTML_Tag::create( 'tbody', $rows );
$table			= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table' ) );

if( $env->php->version->has( 7 ) )																//  @todo remove in v1 using PGP 7
	$optCategory	= array_column( $categories, 'title', 'manualCategoryId' );
/*  @todo remove in v1 using PGP 7
*/else{
	$optCategory	= array();
	foreach( $categories as $category )
		$optCategory[$category->manualCategoryId]	= $category->title;
}/**/
$optCategory	= UI_HTML_Elements::Options( $optCategory, $categoryId );

$optFormat		= array_flip( Alg_Object_Constant::staticGetAll( 'Model_Manual_Page', 'FORMAT_' ) );
$optFormat		= UI_HTML_Elements::Options( array_reverse( $optFormat, TRUE ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', 'zurück', array( 'href' => './info/manual', 'class' => 'btn' ) );
$buttonSave		= UI_HTML_Tag::create( 'button', 'importieren', array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class' 	=> 'btn btn-primary',
) );

$preset		= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'label', 'Kategorie', array( 'for' => 'input_categoryId' ) ),
		UI_HTML_Tag::create( 'select', $optCategory, array(
			'name'		=> 'categoryId',
			'id'		=> 'input_categoryId',
			'class'		=> 'span12'
		) ),
	), array( 'class' => 'span4' ) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'label', 'Format', array( 'for' => 'input_format' ) ),
		UI_HTML_Tag::create( 'select', $optFormat, array(
			'name'		=> 'format',
			'id'		=> 'input_format',
			'class'		=> 'span12'
		) ),
	), array( 'class' => 'span4' ) ),
), array( 'class' => 'row-fluid' ) );

$panelList	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Import' ),
	UI_HTML_Tag::create( 'div', array(
		$table,
		UI_HTML_Tag::create( 'div', join( '&nbsp;', array(
			$preset,
			$buttonCancel,
			$buttonSave,
		) ), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );

return UI_HTML_Tag::create( 'form', $panelList, array(
	'action'	=> './info/manual/import',
	'method'	=> 'POST',
) );
