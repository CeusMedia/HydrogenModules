<?php
$w		= (object) $words['index'];

$panelFilter	= $view->loadTemplateFile( 'admin/mail/queue/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'admin/mail/queue/index.list.php' );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/admin/mail/queue/' ) );

return $textTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>
<style>
.list-item-mail {}
</style>'.$textBottom;
?>
