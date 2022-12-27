<?php
$panelCategories	= $view->loadTemplateFile( 'manage/catalog/gallery/index.categories.php' );
$panelCategory		= $view->loadTemplateFile( 'manage/catalog/gallery/editCategory.category.php' );
$panelImages		= $view->loadTemplateFile( 'manage/catalog/gallery/editCategory.images.php' );

if( $moduleConfig->get( 'layout' ) == 'matrix' ){
	return '
	<div class="row-fluid">
		<div class="span12">
			'.$panelCategory.'
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			'.$panelImages.'
		</div>
	</div>';
}

return '
	<div class="row-fluid">
		<div class="span4">
			'.$panelCategories.'
		</div>
		<div class="span8">
			'.$panelCategory.'
			'.$panelImages.'
		</div>
	</div>';
