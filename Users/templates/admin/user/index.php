<?php

$filter	= require_once 'templates/admin/user/index.filter.php';
$list	= require_once 'templates/admin/user/index.list.php';

$heading	= '';
if( !empty( $words['index']['heading'] ) )
	$heading	= UI_HTML_Tag::create( 'h2', $words['index']['heading'] );

return '
<div>
	'.$heading.'
	<div class="column-control">
		'.$filter.'
	</div>
	<div class="column-main">
		'.$list.'
	</div>
	<div style="clear: both"></div>
</div>
';


?>