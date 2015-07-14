<?php

$panelFilter	= $view->loadTemplateFile( 'manage/project/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/project/index.list.php' );

return '
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>';
?>
