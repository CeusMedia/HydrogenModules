<?php
$w	= (object) $words['index'];

$panelActive	= $view->loadTemplateFile( 'work/time/index.active.php' );
$panelUnrelated	= $view->loadTemplateFile( 'work/time/index.unrelated.php' );
$panelNew		= $view->loadTemplateFile( 'work/time/index.new.php' );
$panelPaused	= $view->loadTemplateFile( 'work/time/index.paused.php' );
$panelDone		= $view->loadTemplateFile( 'work/time/index.done.php' );

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/work/time/' ) );

$tabs	= View_Work_Time::renderTabs( $env );

return $tabs.$textIndexTop.'
<div class="row-fluid">
	<div class="span6">
		'.$panelActive.'
		'.$panelNew.'
		'.$panelUnrelated.'
	</div>
	<div class="span6">
		'.$panelPaused.'
		'.$panelDone.'
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
	</div>
	<div class="span6">
	</div>
</div>'.$textIndexBottom;
