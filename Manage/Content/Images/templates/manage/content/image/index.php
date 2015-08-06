<?php

$panelList		= $view->loadTemplateFile( 'manage/content/image/folders.php' );
$panelFolder	= $view->loadTemplateFile( 'manage/content/image/index.folder.php' );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/manage/content/image/' ) );

return $textTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelList.'
	</div>
	<div class="span9">
		'.$panelFolder.'
	</div>
</div>
'.$textBottom;
?>
