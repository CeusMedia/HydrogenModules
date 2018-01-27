<?php

$panelList		= $view->loadTemplateFile( 'manage/shop/bridge/index.list.php' );
$panelDiscover	= $view->loadTemplateFile( 'manage/shop/bridge/index.discover.php' );

//$tabs	= $this->renderMainTabs();
$tabs	= View_Manage_Shop::renderTabs( $env, 'bridge' );

return $tabs.'
<!--<h2 class="muted">Shop Bridges</h2>-->
<div class="row-fluid">
	<div class="span4">
		'.$panelList.'
		'.$panelDiscover.'
	</div>
	<div class="span8">
	</div>
</div>';
?>
