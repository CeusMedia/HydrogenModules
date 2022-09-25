<?php
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
$colgroup		= HtmlElements::ColumnGroup( array( '', '' ) );
$thead			= HtmlTag::create( 'thead', HtmlElements::TableHeads( array( '1', '2' ) ) );
$tbody			= HtmlTag::create( 'tbody', $rows );
$table			= HtmlTag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table' ) );

if( $env->php->version->has( 7 ) )																//  @todo remove in v1 using PGP 7
	$optCategory	= array_column( $categories, 'title', 'manualCategoryId' );
/*  @todo remove in v1 using PGP 7
*/else{
	$optCategory	= [];
	foreach( $categories as $category )
		$optCategory[$category->manualCategoryId]	= $category->title;
}/**/
$optCategory	= HtmlElements::Options( $optCategory, $categoryId );

$optFormat		= array_flip( Alg_Object_Constant::staticGetAll( 'Model_Manual_Page', 'FORMAT_' ) );
$optFormat		= HtmlElements::Options( array_reverse( $optFormat, TRUE ) );

$buttonCancel	= HtmlTag::create( 'a', 'zurück', array( 'href' => './info/manual', 'class' => 'btn' ) );
$buttonSave		= HtmlTag::create( 'button', 'importieren', array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class' 	=> 'btn btn-primary',
) );

$preset		= HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'label', 'Kategorie', array( 'for' => 'input_categoryId' ) ),
		HtmlTag::create( 'select', $optCategory, array(
			'name'		=> 'categoryId',
			'id'		=> 'input_categoryId',
			'class'		=> 'span12'
		) ),
	), array( 'class' => 'span4' ) ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'label', 'Format', array( 'for' => 'input_format' ) ),
		HtmlTag::create( 'select', $optFormat, array(
			'name'		=> 'format',
			'id'		=> 'input_format',
			'class'		=> 'span12'
		) ),
	), array( 'class' => 'span4' ) ),
), array( 'class' => 'row-fluid' ) );

$panelList	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Import' ),
	HtmlTag::create( 'div', array(
		$table,
		HtmlTag::create( 'div', join( '&nbsp;', array(
			$preset,
			$buttonCancel,
			$buttonSave,
		) ), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );

return HtmlTag::create( 'form', $panelList, array(
	'action'	=> './info/manual/import',
	'method'	=> 'POST',
) );
