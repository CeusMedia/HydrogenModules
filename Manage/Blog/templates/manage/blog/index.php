<?php

$panelFilter	= $view->loadTemplateFile( 'manage/blog/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/blog/index.list.php' );

return '
<div class="row-fluid">
	<div class="row-span4">
		'.$panelFilter.'
	</div>
	<div class="row-span8">
		'.$panelList.'
	</div>
</div>';
