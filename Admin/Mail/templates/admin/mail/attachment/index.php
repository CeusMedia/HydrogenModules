<?php
//$panelAdd		= $this->loadTemplateFile( 'admin/mail/attachment/add.php' );
//$panelUpload	= $this->loadTemplateFile( 'admin/mail/attachment/upload.php' );
$panelFilter	= $this->loadTemplateFile( 'admin/mail/attachment/index.filter.php' );
$panelList		= $this->loadTemplateFile( 'admin/mail/attachment/index.list.php' );

$w			= (object) $words['index'];

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/admin/mail/attachment/' ) );

$tabs	= View_Admin_Mail_Attachment::renderTabs( $env, '' );

return $tabs.$textTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>
'.$textBottom;
