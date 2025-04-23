<?php

use CeusMedia\HydrogenFramework\View;

/** @var View_Manage_Catalog_Bookstore_Author $view */

$tabs		= $view->renderMainTabs();

$panelList	= $view->loadTemplateFile( 'manage/catalog/bookstore/author/list.php' );

return '
'.$tabs.'
<div class="row-fluid">
	<div class="span4">
		'.$panelList.'
	</div>
</div>
';
