<?php

$panelFacts		= $view->loadTemplateFile( 'manage/my/provision/license/view.facts.php' );
$panelKeys		= $view->loadTemplateFile( 'manage/my/provision/license/view.keys.php' );
$panelFilter	= $view->loadTemplateFile( 'manage/my/provision/license/index.filter.php' );

extract( $view->populateTexts( ['top', 'bottom'], 'html/manage/my/provision/license/' ) );

$tabs	= View_Manage_My_Provision_License::renderTabs( $env, '' );

$licenseLabel	= '';

return $tabs.$textTop.'
<div class="position-bar" style="font-size: 1.1em">
	<big>&nbsp;Position: </big>
	<a href="./manage/my/provision/license">Lizenzliste</a>
	<i class="fa fa-fw fa-chevron-right"></i>
	<span>
		<strong>'.$product->title.'</strong>
		<em>'.$userLicense->productLicense->title.'</em>
	</span>
	<hr/>
</div>
'.$panelFacts.'
'.$panelKeys.'
';

return $tabs.$textTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelFacts.'
		<br/>
		'.$panelKeys.'
	</div>
</div>';
