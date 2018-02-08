<?php

$language	= $env->getLanguage()->getLanguage();
$helper		= new View_Helper_Catalog_Bookstore( $env );

$total		= count( $categories );
$edge		= ceil( $total / 2 );
$position	= $helper->renderPositionFromCategory();
$list1		= $helper->renderCategoryList( array_slice( $categories, 0, $edge ), $language );
$list2		= $helper->renderCategoryList( array_slice( $categories, $edge ), $language );

return '
	<script>
$(document).ready(function(){
	ModuleCatalogBookstore.setupCategoryIndex("#categoryList");
});
	</script>
	<div id="categoryList">
		<h2>'.$words['index']['heading'].'</h2>
		'.$position.'<br/>
<!--		<h3>Fachbereiche</h3>-->
		<small>Bitte wählen Sie einen Fachbereich aus, um dessen Serien und Veröffentlichungen zu sehen.</small><br/>
		<br/>
		<div class="column">
			'.$list1.'
		</div>
		<div class="column">
			'.$list2.'
		</div>
	</div>';

?>
