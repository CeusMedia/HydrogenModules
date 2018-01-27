<?php


if( $moduleConfig->get( 'layout' ) == 'matrix' ){
	return '
	<div class="content-panel">
		<h3>'.$words['index']['heading'].'</h3>
		<div class="content-panel-inner">
			<div class="row-fluid">
				<div class="span12">
					'.$view->renderCategoryMatrix( $categories, './manage/catalog/gallery/addCategory' ).'
				</div>
			</div>
		</div>
	</div>';
}

$panelCategories	= $view->loadTemplateFile( 'manage/catalog/gallery/index.categories.php' );

return '
<div class="row-fluid">
	<div class="span4">
		'.$panelCategories.'
	</div>
	<div class="span8">
		<div class="muted"><em><small>Bitte wähle eine Galerie!</small></em></div>
	</div>

</div>';
?>
