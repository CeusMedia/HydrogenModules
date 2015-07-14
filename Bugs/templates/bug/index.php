<?php
$filter	= require_once 'templates/bug/index.filter.php';
$list	= require_once 'templates/bug/index.list.php';

return '
<div>
		'.$filter.'
		'.$list.'
<!--	<div class="column-control">
		'.$filter.'
	</div>
	<div class="column-main">
		'.$list.'
	</div>-->
	<div style="clear: both"></div>
</div>
';


?>
