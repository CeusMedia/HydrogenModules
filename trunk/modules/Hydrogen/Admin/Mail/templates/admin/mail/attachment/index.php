<?php
$panelAdd		= $this->loadTemplateFile( 'admin/mail/attachment/add.php' );
$panelUpload	= $this->loadTemplateFile( 'admin/mail/attachment/upload.php' );
//$panelFilter	= $this->loadTemplateFile( 'admin/mail/attachment/index.filter.php' );
$panelList		= $this->loadTemplateFile( 'admin/mail/attachment/index.list.php' );

$w			= (object) $words['index'];
return '
<!--<h2>'.$w->heading.'</h2>-->
<div class="row-fluid">
<!--	<div class="span3">
		'./*$panelFilter.*/'
	</div>
	<div class="span9">
-->
	<div class="span12">
		<h3>Anhänge</h3>
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
';
