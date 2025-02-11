<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var object $newsletter */
/** @var bool $tabbedLinks */

$tabsMain		= $tabbedLinks ? $view->renderMainTabs() : '';

$panelAdd		= $view->loadTemplateFile( 'work/newsletter/index.add.php' );
$panelFilter	= $view->loadTemplateFile( 'work/newsletter/index.filter.php', ['inlineFilter' => FALSE] );
$panelList		= $view->loadTemplateFile( 'work/newsletter/index.list.php' );

extract( $view->populateTexts( ['above', 'bottom', 'top'], 'html/work/newsletter', ['heading' => $words->index->heading] ) );

return $textTop.'
<div class="newsletter-content">
	'.$tabsMain.'
	'.$textAbove.'
	'.$panelList.'
</div>
'.$textBottom.$panelAdd;

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
