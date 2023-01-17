<?php

use CeusMedia\Common\Alg\Obj\Constant as ObjectConstants;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$rows		= [];
foreach( $files as $fileName ){
	$buttonImport	= HtmlTag::create( 'a', 'import', array(
		'href'	=> './info/manual/import/'.base64_encode( $fileName ),
		'class'	=> 'btn',
	) );
	$checkbox		= HtmlTag::create( 'input', NULL, array(
		'type'		=> 'checkbox',
		'name'		=> 'files[]',
		'value'		=> base64_encode( $fileName ),
	) );
	$rows[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', $checkbox ),
		HtmlTag::create( 'td', $fileName ),
		HtmlTag::create( 'td', $buttonImport ),
	) );
}
$colgroup		= HtmlElements::ColumnGroup( ['', ''] );
$thead			= HtmlTag::create( 'thead', HtmlElements::TableHeads( ['1', '2'] ) );
$tbody			= HtmlTag::create( 'tbody', $rows );
$table			= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table'] );

if( $env->php->version->has( 7 ) )																//  @todo remove in v1 using PGP 7
	$optCategory	= array_column( $categories, 'title', 'manualCategoryId' );
/*  @todo remove in v1 using PGP 7
*/else{
	$optCategory	= [];
	foreach( $categories as $category )
		$optCategory[$category->manualCategoryId]	= $category->title;
}/**/
$optCategory	= HtmlElements::Options( $optCategory, $categoryId );

$optFormat		= array_flip( ObjectConstants::staticGetAll( 'Model_Manual_Page', 'FORMAT_' ) );
$optFormat		= HtmlElements::Options( array_reverse( $optFormat, TRUE ) );

$buttonCancel	= HtmlTag::create( 'a', 'zurück', ['href' => './info/manual', 'class' => 'btn'] );
$buttonSave		= HtmlTag::create( 'button', 'importieren', [
	'type'		=> 'submit',
	'name'		=> 'save',
	'class' 	=> 'btn btn-primary',
] );

$preset		= HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'label', 'Kategorie', ['for' => 'input_categoryId'] ),
		HtmlTag::create( 'select', $optCategory, [
			'name'		=> 'categoryId',
			'id'		=> 'input_categoryId',
			'class'		=> 'span12'
		] ),
	), ['class' => 'span4'] ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'label', 'Format', ['for' => 'input_format'] ),
		HtmlTag::create( 'select', $optFormat, [
			'name'		=> 'format',
			'id'		=> 'input_format',
			'class'		=> 'span12'
		] ),
	), ['class' => 'span4'] ),
), ['class' => 'row-fluid'] );

$panelList	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Import' ),
	HtmlTag::create( 'div', array(
		$table,
		HtmlTag::create( 'div', join( '&nbsp;', [
			$preset,
			$buttonCancel,
			$buttonSave,
		] ), ['class' => 'buttonbar'] ),
	), ['class' => 'content-panel-inner'] )
), ['class' => 'content-panel'] );

return HtmlTag::create( 'form', $panelList, [
	'action'	=> './info/manual/import',
	'method'	=> 'POST',
] );
