<?php

$w	= (object) $words['index'];

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/admin/config/index/' ) );

$panelFilter	= $view->loadTemplateFile( 'admin/config/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'admin/config/index.list.php' );

return $textTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>'.$textBottom;
