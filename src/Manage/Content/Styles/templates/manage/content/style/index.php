<?php

/** @var View_Manage_Content_Style $view */

$panelFilter	= $view->loadTemplateFile( 'manage/content/style/index.filter.php' );
$panelEditor	= $view->loadTemplateFile( 'manage/content/style/index.edit.php' );

return '<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelEditor.'
	</div>
</div>';
