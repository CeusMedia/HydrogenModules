<?php
use CeusMedia\HydrogenFramework\View;

/** @var View $view */
/** @var array $words */

$w			= (object) $words['index'];

$tabs		= $view->renderMainTabs();

extract( $view->populateTexts( ['top', 'bottom', 'index'], 'html/manage/catalog/bookstore/article/' ) );

$panelFilter	= $view->loadTemplateFile( 'manage/catalog/bookstore/article/filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/catalog/bookstore/article/list.php' );

return $textTop.'
'.$tabs.'
<div class="row-fluid">
	<div class="span2">
		'.$panelFilter.'
	</div>
	<div class="span3">
		'.$panelList.'
	</div>
	<div class="span7">
		'.$textIndex.'
	</div>
</div>
'.$textBottom;
