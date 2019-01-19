<?php

$panelFacts		= $view->loadTemplateFile( 'admin/database/backup/view.facts.php' );
$panelCopy		= $view->loadTemplateFile( 'admin/database/backup/view.copy.php' );
$panelDownload	= $view->loadTemplateFile( 'admin/database/backup/view.download.php' );
$panelRecover	= $view->loadTemplateFile( 'admin/database/backup/view.recover.php' );

return '
<div class="row-fluid">
	<div class="span8">
		'.$panelFacts.'
		'.$panelCopy.'
	</div>
	<div class="span4">
		'.$panelDownload.'
		'.$panelRecover.'
	</div>
</div>';
