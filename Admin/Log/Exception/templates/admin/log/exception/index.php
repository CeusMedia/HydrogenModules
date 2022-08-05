<?php

$panelList		= $this->loadTemplateFile( 'admin/log/exception/index.list.php' );
$panelFilter	= $this->loadTemplateFile( 'admin/log/exception/index.filter.php' );

return '
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>';
