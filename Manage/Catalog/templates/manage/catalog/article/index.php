<?php
$w			= (object) $words['index'];

$tabs		= $this->renderMainTabs();

extract( $view->populateTexts( array( 'top', 'bottom', 'index' ), 'html/manage/catalog/article/' ) );

$panelFilter	= $view->loadTemplateFile( 'manage/catalog/article/filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/catalog/article/list.php' );

return $textTop.'
'.$tabs.'
<div class="row-fluid">
	<div class="span2">
		'.$panelFilter.'
	</div>
	<div class="span3">
		'.$panelList.'
	</div>
	<div class="span7">
		'.$textIndex.'
	</div>
</div>
'.$textBottom;
?>
