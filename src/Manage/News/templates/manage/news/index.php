<?php

/** @var View_Manage_News $view */

$panelFilter	= $view->loadTemplateFile( 'manage/news/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/news/index.list.php' );

return '
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>
';
