<?php

use CeusMedia\HydrogenFramework\View;

/** @var View_Manage_Catalog_Bookstore_Category $view */
/** @var array<string,array<string,int|string>> $words */

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
