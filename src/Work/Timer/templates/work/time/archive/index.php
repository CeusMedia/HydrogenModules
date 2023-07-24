<?php
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */

$w	= (object) $words['index'];

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/work/time/archive/' ) );

$panelFilter	= $view->loadTemplateFile( 'work/time/archive/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'work/time/archive/index.list.php' );

$tabs	= View_Work_Time::renderTabs( $env, 'archive' );

//$helperShortList	= new View_Helper_Work_Time_ShortList( $env );
//$helperShortList->setStatus( [0, 2, 3] );

return $tabs.$textIndexTop.'
<div class="row-fluid">
	<div class="span2">
		'.$panelFilter.'
	</div>
	<div class="span10">
		'.$panelList.'
	</div>
</div>'.$textIndexBottom;
