<?php

/** @var \CeusMedia\HydrogenFramework\Environment $env */
/** @var \CeusMedia\HydrogenFramework\View $view */

$panelExport	= $view->loadTemplateFile( 'manage/ip/lock/transport/index.export.php' );
$panelImport	= $view->loadTemplateFile( 'manage/ip/lock/transport/index.import.php' );

$tabs	= View_Manage_IP_Lock::renderTabs( $env, 'transport' );

return $tabs.'
<div class="row-fluid">
	<div class="span6">
		'.$panelExport.'
	</div>
	<div class="span6">
		'.$panelImport.'
	</div>
</div>';
