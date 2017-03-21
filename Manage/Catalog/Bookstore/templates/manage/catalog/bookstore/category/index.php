<?php
$w	= (object) $words['index'];

$tabs		= $this->renderMainTabs();

$panelList	= $view->loadTemplateFile( 'manage/catalog/bookstore/category/list.php' );

return '
'.$tabs.'
<div class="row-fluid">
	<div class="span6">
		'.$panelList.'
	</div>
</div>
';
?>
