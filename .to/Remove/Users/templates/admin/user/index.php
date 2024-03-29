<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$filter	= $view->loadTemplateFile( 'admin/user/index.filter.php' );
$list	= $view->loadTemplateFile( 'admin/user/index.list.php' );

$heading	= '';
if( !empty( $words['index']['heading'] ) )
	$heading	= HtmlTag::create( 'h2', $words['index']['heading'] );

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