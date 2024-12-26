<?php
$tabs		= $view->renderMainTabs();

$panelList	= $view->loadTemplateFile( 'manage/catalog/author/list.php' );

return '
'.$tabs.'
<div class="row-fluid">
	<div class="span4">
		'.$panelList.'
	</div>
</div>
';
