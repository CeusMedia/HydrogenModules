<?php

$language	= $env->getLanguage()->getLanguage();
$helper		= new View_Helper_Catalog( $env );

//$columns	= 2;
//$columns	= max( 1, min( 3, $columns ) );
//$total		= count( $categories );
//$edge		= ceil( $total / $columns );
$position	= $helper->renderPositionFromCategory();

/*
$lists	= [];
for( $i=0; $i<$columns; $i++ ){
	$column	= $helper->renderCategoryList( array_slice( $categories, $i*$edge, ($i+1)*$edge ), $language );
	$column	= '<div class="column column-'.$columns.'">'.$column.'</div>';
	$lists[]	= $column;
}
$lists	= join( $lists );
*/

$total	= count( $categories );
$edge	= ceil( $total / 2 );
$lists	= [];
for( $i=0; $i<2; $i++ ){
	$column	= $helper->renderCategoryList( array_slice( $categories, $i*$edge, ($i+1)*$edge ), $language );
	$column	= '<div class="column span6">'.$column.'</div>';
	$lists[]	= $column;
}
$lists	= UI_HTML_Tag::create( 'div', $lists, array( 'class' => 'row-fluid' ) );

return '
	<script>
$(document).ready(function(){
	ModuleCatalog.setupCategoryIndex("#categoryList");
});
	</script>
	<div id="categoryList">
		<h2>'.$words['index']['heading'].'</h2>
		'.$position.'<br/>
<!--		<h3>Fachbereiche</h3>-->
		<small>Bitte wählen Sie einen Fachbereich aus, um dessen Serien und Veröffentlichungen zu sehen.</small><br/>
		<br/>
		'.$lists.'
	</div>';

?>
