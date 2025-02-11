<?php
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var bool $tabbedLinks */

$tabsMain		= $tabbedLinks ? $view->renderMainTabs() : '';

extract( $view->populateTexts( ['above', 'bottom', 'top'], 'html/work/newsletter/group/index/', ['words' => $words] ) );

$panelFilter	= $view->loadTemplateFile( 'work/newsletter/group/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'work/newsletter/group/index.list.php' );

return $textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
	'.$textAbove.'
	'.$panelList.'
</div>
'.$textBottom;

/*
return $textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
	'.$textAbove.'
	<div class="row-fluid">
		<div class="span3">
			'.$panelFilter.'
		</div>
		<div class="span9">
			'.$panelList.'
		</div>
	</div>
</div>
'.$textBottom;
*/
