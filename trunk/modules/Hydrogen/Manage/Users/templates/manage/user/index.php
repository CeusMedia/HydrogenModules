<?php

$filter	= $view->loadTemplateFile( 'manage/user/index.filter.php' );
$list	= $view->loadTemplateFile( 'manage/user/index.list.php' );

$heading	= '';
if( !empty( $words['index']['heading'] ) )
	$heading	= UI_HTML_Tag::create( 'h2', $words['index']['heading'] );

return '
<div>
	'.$heading.'
	<div class="row-fluid">
		<div class="span2">
			'.$filter.'
		</div>
		<div class="span10">
			'.$list.'
		</div>
	</div>
</div>
';


?>