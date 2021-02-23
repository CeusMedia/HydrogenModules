<?php
$w	= (object) $words['index'];

$panelFilter	= $view->loadTemplateFile( 'work/time/analysis/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'work/time/analysis/index.list.php' );

$tabs	= View_Work_Time::renderTabs( $env, 'analysis' );

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/work/time/analysis/' ) );

return $tabs.$textIndexTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>'.$textIndexBottom;
