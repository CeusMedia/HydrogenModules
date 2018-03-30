<?php

$panelFilter	= $view->loadTemplateFile( 'manage/catalog/provision/license/index.filter.php' );
$panelEdit		= '
<div class="content-panel">
	<h3><span class="muted">Lizenz: </span>'.$license->title.'</h3>
	<div class="content-panel-inner">
		...
	</div>
</div>';

return '
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>';
