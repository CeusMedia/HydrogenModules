<?php
$panelAdd		= $this->loadTemplateFile( 'admin/mail/attachment/add.php' );
$panelUpload	= $this->loadTemplateFile( 'admin/mail/attachment/upload.php' );
//$panelFilter	= $this->loadTemplateFile( 'admin/mail/attachment/index.filter.php' );
$panelList		= $this->loadTemplateFile( 'admin/mail/attachment/index.list.php' );

$w			= (object) $words['index'];

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/admin/mail/attachment/' ) );

return $textTop.'
<div class="row-fluid">
	<div class="span12">
		'.$panelList.'
	</div>
</div>
<!--<hr/>-->
<div class="row-fluid">
	<div class="span6">
		'.$panelAdd.'
	</div>
	<div class="span5 offset1">
		'.$panelUpload.'
	</div>
</div>
'.$textBottom;
