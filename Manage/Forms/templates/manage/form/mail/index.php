<?php

$panelFilter	= $view->loadTemplateFile( 'manage/form/mail/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/form/mail/index.list.php' );
$heading		= UI_HTML_Tag::create( 'h2', 'Formular-E-Mails' );

return $heading.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>';
