<?php

$panelList	= $view->loadTemplateFile( 'admin/mail/attachment/folder/index.list.php' );

$tabs		= View_Admin_Mail_Attachment::renderTabs( $env, 'folder' );

extract( $view->populateTexts( ['top', 'bottom'], 'html/admin/mail/attachment/folder' ) );

return $tabs.$textTop.'
<div class="row-fluid">
	<div class="span9">
		'.$panelList.'
	</div>
	<div class="span3">
	</div>
</div>
'.$textBottom;
