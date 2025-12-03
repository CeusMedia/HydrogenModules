<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var View_Work_Newsletter_Template $view */
/** @var object $words */
/** @var array $themes */
/** @var string $pathNewsletterThemes */

if( [] === $themes )
	return '';

if( !$this->env->getAcl()->has( 'work/newsletter/template', 'installTheme' ) )
	return '';

$helper	= new View_Helper_Work_Newsletter_ThemeList( $this->env );
$helper->setThemes( $themes );
$helper->setThemePath( $pathNewsletterThemes );
$list	= $helper->render();

$w	= (object) $words->index_themes;

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
