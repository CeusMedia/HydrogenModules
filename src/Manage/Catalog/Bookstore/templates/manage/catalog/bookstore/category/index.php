<?php

use CeusMedia\HydrogenFramework\View;

/** @var View $view */
/** @var array $words */

$w	= (object) $words['index'];

$tabs		= $view->renderMainTabs();

$panelList	= $view->loadTemplateFile( 'manage/catalog/bookstore/category/list.php' );

return '
'.$tabs.'
<div class="row-fluid">
	<div class="span6">
		'.$panelList.'
	</div>
</div>
';
