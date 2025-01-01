<?php

/** @var array<string,array<string,string>> $words */
/** @var View_Manage_Relocation $view */

$w		= (object) $words['index'];

$panelFilter	= $view->loadTemplateFile( 'manage/relocation/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/relocation/index.list.php' );

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/manage/relocation/' ) );

return $textIndexTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>
'.$textIndexBottom;
