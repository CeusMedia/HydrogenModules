<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var View_Work_Newsletter_Template $this */
/** @var object $words */
/** @var bool $tabbedLinks */

$tabsMain		= $tabbedLinks ? $this->renderMainTabs() : '';

$panelFilter	= $view->loadTemplateFile( 'work/newsletter/template/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'work/newsletter/template/index.list.php' );
$panelThemes	= $view->loadTemplateFile( 'work/newsletter/template/index.themes.php' );

extract( $view->populateTexts( ['above', 'bottom', 'top'], 'html/work/newsletter/template/index/', ['words' => $words] ) );

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
