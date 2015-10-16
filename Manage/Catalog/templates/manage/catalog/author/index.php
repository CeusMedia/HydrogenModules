<?php
$tabs		= $this->renderMainTabs();

$panelList	= $view->loadTemplateFile( 'manage/catalog/author/list.php' );

return '
'.$tabs.'
<div class="row-fluid">
	<div class="span4">
		'.$panelList.'
	</div>
</div>
';
?>
