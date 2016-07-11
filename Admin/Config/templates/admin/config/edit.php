<?php

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/admin/config/' ) );

$panelList	= $view->loadTemplateFile( 'admin/config/list.php' );
$panelEdit	= $view->loadTemplateFile( 'admin/config/edit.php' );

return $textTop.'
<div class="row-fluid">
	<div class="span4">
		'.$panelList.'
	</div>
	<div class="span8">
		'.$panelEdit.'
	</div>
</div>
'.$textBottom;
