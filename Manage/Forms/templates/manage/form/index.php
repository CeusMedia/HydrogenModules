<?php

$panelFilter	= $view->loadTemplateFile( 'manage/form/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/form/index.list.php' );
$heading		= UI_HTML_Tag::create( 'h2', 'Formulare' );

return $heading.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>';
