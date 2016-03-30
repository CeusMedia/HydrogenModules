<?php

$panelFilter	= $view->loadTemplateFile( 'manage/blog/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/blog/index.list.php' );

return '
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>';
