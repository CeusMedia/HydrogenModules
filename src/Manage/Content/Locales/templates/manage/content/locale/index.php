<?php

$panelFilter	= $view->loadTemplateFile( 'manage/content/locale/filter.php' );

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/manage/content/locale' ) );

return $textIndexTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
	</div>
</div>'.$textIndexBottom;
