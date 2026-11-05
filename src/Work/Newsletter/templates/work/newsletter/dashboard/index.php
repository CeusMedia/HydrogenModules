<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var bool $tabbedLinks */
/** @var array<int,int> $readers */

$tabsMain	= $tabbedLinks ? $view->renderMainTabs( 'work/newsletter' ) : '';

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/work/newsletter/dashboard/' ) );

$w	= (object) $env->getLanguage()->getSection( 'work/newsletter/dashboard', 'index' );

return '
'.$tabsMain.'
'.$textIndexTop.'
<h3>'.$w->heading.'</h3>
<div class="row-fluid">
	<div class="span3">
		<h4>'.$w->headingTasks.'</h4>
		<em><small class="muted">'.$w->placeholder.'</small></em>
	</div>
	<div class="span3">
		<h4>'.$w->headingReaders.'</h4>
		<ul>
			<li><a href="./work/newsletter/reader/filter/reset">'.sprintf( $w->readersTotal, $readers[-1] + $readers[0] + $readers[1] ).'</a></li>
			<li><a href="./work/newsletter/reader/filter/reset?filter&status=1">'.sprintf( $w->readersActive, $readers[1] ).'</a></li>
			<li><a href="./work/newsletter/reader/filter/reset?filter&status=0">'.sprintf( $w->readersNew, $readers[0] ).'</a></li>
			<li><a href="./work/newsletter/reader/filter/reset?filter&status=-1">'.sprintf( $w->readersUnregistered, $readers[-1] ).'</a></li>
		</ul>
	</div>
	<div class="span3">
		<h4>'.$w->headingGroups.'</h4>
		<em><small class="muted">'.$w->placeholder.'</small></em>
	</div>
	<div class="span3">
		<h4>'.$w->headingNewsletters.'</h4>
		<em><small class="muted">'.$w->placeholder.'</small></em>
	</div>
</div>'.$textIndexBottom;
