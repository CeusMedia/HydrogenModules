<?php

$panelList		= $view->loadTemplateFile( 'manage/my/provision/license/key/index.list.php' );
$panelFilter	= $view->loadTemplateFile( 'manage/my/provision/license/key/index.filter.php' );

extract( $view->populateTexts( ['top', 'bottom'], 'html/manage/my/provision/license/key/' ) );

$tabs	= View_Manage_My_License::renderTabs( $env, 'key' );

return $tabs.$textTop.'
<div class="position-bar" style="font-size: 1.1em">
	<big>&nbsp;Position: </big>
	<span href="./manage/my/provision/license/key">Schlüsselliste</span>
	<hr/>
</div>
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>';
