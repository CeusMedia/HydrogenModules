<?php
$w	= (object) $words['index'];

$tabs		= $this->renderMainTabs();

$panelList	= $view->loadTemplateFile( 'manage/catalog/category/list.php' );

return '
'.$tabs.'
<div class="row-fluid">
	<div class="span6">
		'.$panelList.'
	</div>
</div>
';
?>
