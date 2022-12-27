<?php

$panelList	= $view->loadTemplateFile( 'manage/catalog/provision/product/index.list.php' );

return '
<div class="row-fluid">
	<div class="span3">
		'.$panelList.'
	</div>
</div>';
