<?php

$panelList		= $view->loadTemplateFile( 'manage/my/provision/license/index.list.php' );
$panelKeys		= $view->loadTemplateFile( 'manage/my/provision/license/index.keys.php' );
$panelFilter	= $view->loadTemplateFile( 'manage/my/provision/license/index.filter.php' );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/manage/my/provision/license/' ) );

$tabs	= View_Manage_My_License::renderTabs( $env, '' );

return $tabs.$textTop.'
<div class="position-bar" style="font-size: 1.1em">
	<big>&nbsp;Position: </big>
	<span href="./manage/my/provision/license">Lizenzliste</span>
	<hr/>
</div>
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
		'.$panelKeys.'
	</div>
</div>';
