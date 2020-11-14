<?php
$tabsMain		= $tabbedLinks ? $this->renderMainTabs() : '';

$panelFilter	= $view->loadTemplateFile( 'work/newsletter/template/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'work/newsletter/template/index.list.php' );
$panelThemes	= $view->loadTemplateFile( 'work/newsletter/template/index.themes.php' );

extract( $view->populateTexts( array( 'above', 'bottom', 'top' ), 'html/work/newsletter/template/index/', array( 'words' => $words ) ) );

return 	$textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
	'.$textAbove.'
	'.$panelList.'
<!--	<div class="row-fluid">
		<div class="span3">
			'.$panelFilter.'
		</div>
		<div class="span9">
			'.$panelList.'
		</div>
	</div>-->
</div>
'.$panelThemes.'
'.$textBottom;
?>
