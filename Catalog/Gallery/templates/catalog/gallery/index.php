<?php

extract( $this->populateTexts( array( 'top', 'content', 'bottom' ), 'html/catalog/gallery/index/' ) );

$categoryList	= $view->renderCategoryList( $categories, 0, FALSE );

$categoryMatrix	= $view->renderCategoryMatrix( $categories );

$w		= (object) $words['index'];

return '
'.$textTop.'
<div class="row-fluid">
	<div class="span3 not-pull-right">
		<h3>'.$words['categories']['heading'].'</h3>
		'.$categoryList.'
	</div>
	<div class="span9">
		'.$categoryMatrix.'
	</div>
</div>

<style>

</style>
'.$textBottom.'
';

?>
