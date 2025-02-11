<?php

/** @var View_Manage_Page $view */

$panelTree	= $view->loadTemplateFile( 'manage/page/tree.php' );

extract( $view->populateTexts( ['index'], 'html/manage/page/' ) );

//  --  LAYOUT  --  //
return '
<div class="row-fluid">
	<div id="manage-page-tree" class="span3">
		'.$panelTree.'
	</div>
	<div id="manage-page-main" class="span9">
		<div style="float: left; width: 100%">
			'.$textIndex.'
		</div>
	</div>
</div>';
