<?php
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */

$w	= (object) $words['index'];

$panelFilter	= $view->loadTemplateFile( 'work/time/analysis/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'work/time/analysis/index.list.php' );

$tabs	= View_Work_Time::renderTabs( $env, 'analysis' );

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/work/time/analysis/' ) );

return $tabs.$textIndexTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>'.$textIndexBottom;
